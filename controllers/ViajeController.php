<?php
// controllers/ViajeController.php — Lógica de viajes

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Viaje.php';
require_once __DIR__ . '/../models/Auditoria.php';

class ViajeController {

    private PDO $pdo;
    private Viaje $model;

    public function __construct(PDO $pdo) {
        $this->pdo   = $pdo;
        $this->model = new Viaje($pdo);
    }

    // ─── LISTADO (admin) ─────────────────────────────────────────

    public function listado(): void {
        if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1, 2])) {
            header("Location: /admin/login.php"); exit();
        }

        // POST: solo admin puede completar/cancelar/modificar
        if ($_SESSION['id_rol'] == 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['completar'])) {
                $this->model->completar(trim($_POST['completar']));
                header("Location: /admin/router.php?pagina=viajes"); exit();
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar'])) {
                $cod = trim($_POST['cancelar']);
                $check = $this->pdo->prepare("SELECT id_suc_origen, fecha_llegada_real FROM viaje WHERE cod_viaje = :cod AND cancelado = 0");
                $check->execute([':cod' => $cod]);
                $v = $check->fetch();
                if ($v && $v['fecha_llegada_real'] === null) {
                    // Paquetes en estado=2 (ya retirados por el chofer) → revertir a estado=1
                    $trks_stmt = $this->pdo->prepare("
                        SELECT ve.nro_tracking FROM viaje_envio ve
                        WHERE ve.cod_viaje = :cod
                          AND (SELECT id_estado FROM historialestado WHERE nro_tracking = ve.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1) = 2
                    ");
                    $trks_stmt->execute([':cod' => $cod]);
                    $en_viaje = $trks_stmt->fetchAll(PDO::FETCH_COLUMN);

                    // Paquetes en estado=1 (asignados pero aún no retirados) → vuelven al pool de despacho
                    $trks_deposito = $this->pdo->prepare("
                        SELECT ve.nro_tracking FROM viaje_envio ve
                        WHERE ve.cod_viaje = :cod
                          AND (SELECT id_estado FROM historialestado WHERE nro_tracking = ve.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1) = 1
                    ");
                    $trks_deposito->execute([':cod' => $cod]);
                    $en_deposito = $trks_deposito->fetchAll(PDO::FETCH_COLUMN);

                    $this->pdo->prepare("UPDATE viaje SET cancelado = 1 WHERE cod_viaje = :cod")->execute([':cod' => $cod]);

                    foreach ($en_viaje as $trk) {
                        $sp = $this->pdo->prepare("CALL SP_CambiarEstadoEnvio(:trk, 1, :uid, 'Viaje cancelado — paquete devuelto a depósito origen', @res)");
                        $sp->execute([':trk' => $trk, ':uid' => $_SESSION['id_usuario']]);
                    }

                    // Sacar de viaje_envio los paquetes que vuelven al depósito (estado=1 y estado=2 revertidos)
                    foreach (array_merge($en_deposito, $en_viaje) as $trk) {
                        $this->pdo->prepare("DELETE FROM viaje_envio WHERE cod_viaje = :cod AND nro_tracking = :trk")
                            ->execute([':cod' => $cod, ':trk' => $trk]);
                    }
                }
                header("Location: /admin/router.php?pagina=viajes"); exit();
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar_fecha'])) {
                $cod   = trim($_POST['cod_viaje'] ?? '');
                $fecha = trim($_POST['fecha_llegada_est'] ?? '');
                if ($cod && $fecha) {
                    $this->pdo->prepare("UPDATE viaje SET fecha_llegada_est = :f WHERE cod_viaje = :cod")
                        ->execute([':f' => $fecha, ':cod' => $cod]);
                }
                header("Location: /admin/router.php?pagina=viajes"); exit();
            }
        }

        $model   = $this->model;
        $manager = $this->model;
        $viajes  = $this->model->obtenerTodos();

        // Empleado: filtrar solo viajes de su sucursal
        if ($_SESSION['id_rol'] == 2) {
            $stmt = $this->pdo->prepare(
                "SELECT e.id_sucursal FROM empleado e JOIN usuario u ON u.legajo = e.legajo WHERE u.id_usuario = :id"
            );
            $stmt->execute([':id' => $_SESSION['id_usuario']]);
            $id_suc_emp = $stmt->fetchColumn() ?: 0;
            if ($id_suc_emp) {
                $viajes = array_filter($viajes, fn($v) => ($v['id_suc_origen'] ?? 0) == $id_suc_emp);
            }
        }

        $filtro_estado = trim($_GET['estado'] ?? '');
        $ahora = time();
        if ($filtro_estado === 'en_curso') {
            $viajes = array_filter($viajes, fn($v) =>
                $v['fecha_llegada_real'] === null && strtotime($v['fecha_salida']) <= $ahora
            );
        } elseif ($filtro_estado === 'pendiente') {
            $viajes = array_filter($viajes, fn($v) =>
                $v['fecha_llegada_real'] === null && strtotime($v['fecha_salida']) > $ahora
            );
        } elseif ($filtro_estado === 'completado') {
            $viajes = array_filter($viajes, fn($v) => $v['fecha_llegada_real'] !== null);
        }

        $busqueda = trim($_GET['q'] ?? '');
        if ($busqueda) {
            $b = strtolower($busqueda);
            $viajes = array_filter($viajes, fn($v) =>
                str_contains(strtolower($v['patente']), $b)
                || str_contains(strtolower($v['legajo_chofer']), $b)
                || str_contains(strtolower($v['nombre_chofer'] ?? ''), $b)
                || str_contains(strtolower($v['apellido_chofer'] ?? ''), $b)
                || str_contains(strtolower($v['cod_viaje']), $b)
            );
        }

        require_once __DIR__ . '/../views/admin/viajes/listado.php';
    }

    // ─── CREAR VIAJE (admin) ──────────────────────────────────────

    public function crear(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            header("Location: /admin/login.php"); exit();
        }

        $error   = '';
        $success = '';

        $sucursales = $this->pdo->query(
            "SELECT id_sucursal, nombre FROM sucursal WHERE activo=1 ORDER BY nombre"
        )->fetchAll();

        // La sucursal de origen se elige primero; chofer, vehículo y envíos disponibles
        // se filtran a partir de ahí para no poder combinar recursos de otra sucursal.
        $id_suc_origen = intval($_POST['id_suc_origen'] ?? $_GET['suc_origen'] ?? 0);

        $choferes = [];
        $vehiculos = [];
        $envios_sin_viaje = [];

        if ($id_suc_origen) {
            $ch_stmt = $this->pdo->prepare(
                "SELECT u.id_usuario, e.nombre, e.apellido, u.legajo AS legajo_chofer
                 FROM usuario u JOIN empleado e ON e.legajo = u.legajo
                 WHERE u.id_rol = 3 AND u.estado = 1 AND e.id_sucursal = :suc
                   AND e.legajo NOT IN (
                       SELECT legajo_chofer FROM viaje
                       WHERE fecha_llegada_real IS NULL AND cancelado = 0 AND legajo_chofer IS NOT NULL
                   )
                 ORDER BY e.apellido, e.nombre"
            );
            $ch_stmt->execute([':suc' => $id_suc_origen]);
            $choferes = $ch_stmt->fetchAll();

            $veh_list_stmt = $this->pdo->prepare(
                "SELECT v.patente, tv.nombre AS tipo, tv.capacidad_kg_max
                 FROM vehiculo v JOIN tipovehiculo tv ON tv.id_tipo_veh = v.id_tipo_veh
                 WHERE v.estado = 'Activo' AND v.id_sucursal = :suc
                   AND v.patente NOT IN (
                       SELECT patente FROM viaje
                       WHERE fecha_llegada_real IS NULL AND cancelado = 0 AND patente IS NOT NULL
                   )
                 ORDER BY v.patente"
            );
            $veh_list_stmt->execute([':suc' => $id_suc_origen]);
            $vehiculos = $veh_list_stmt->fetchAll();

            $env_stmt = $this->pdo->prepare(
                "SELECT e.nro_tracking, cr.nombre AS nom_rem, cr.apellido AS ape_rem, sd.nombre AS suc_dest
                 FROM envio e
                 LEFT JOIN viaje_envio ve ON ve.nro_tracking = e.nro_tracking
                 LEFT JOIN cliente cr ON cr.dni = e.dni_remitente
                 LEFT JOIN sucursal sd ON sd.id_sucursal = e.id_suc_destino
                 WHERE ve.nro_tracking IS NULL
                   AND e.id_suc_origen = :suc
                   AND (
                       SELECT id_estado FROM historialestado
                       WHERE nro_tracking = e.nro_tracking
                       ORDER BY fecha_hora DESC, id_hist DESC
                       LIMIT 1
                   ) = 1
                 ORDER BY e.fecha_creacion DESC"
            );
            $env_stmt->execute([':suc' => $id_suc_origen]);
            $envios_sin_viaje = $env_stmt->fetchAll();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $legajo      = trim($_POST['legajo_chofer']     ?? '');
            $id_suc      = intval($_POST['id_suc_origen']   ?? 0);
            $id_suc_dest = intval($_POST['id_suc_destino']  ?? 0) ?: null;
            $patente     = trim($_POST['patente']           ?? '');
            $fecha_sal   = trim($_POST['fecha_salida']      ?? '');
            $fecha_est   = trim($_POST['fecha_llegada_est'] ?? '');
            $envios_sel  = $_POST['envios'] ?? [];

            if (!$legajo || !$id_suc || !$id_suc_dest || !$patente || !$fecha_sal || !$fecha_est) {
                $error = 'Completá todos los campos obligatorios.';
            } elseif ($id_suc === $id_suc_dest) {
                $error = 'La sucursal de origen y de destino no pueden ser la misma.';
            } elseif (empty($envios_sel)) {
                $error = 'Seleccioná al menos un envío para el viaje.';
            } elseif ($fecha_est <= $fecha_sal) {
                $error = 'La fecha de llegada estimada debe ser posterior a la de salida.';
            } else {
                $chk_chofer_suc = $this->pdo->prepare("SELECT id_sucursal FROM empleado WHERE legajo = :l");
                $chk_chofer_suc->execute([':l' => $legajo]);
                $chk_veh_suc = $this->pdo->prepare("SELECT id_sucursal FROM vehiculo WHERE patente = :p");
                $chk_veh_suc->execute([':p' => strtoupper($patente)]);
                if ($chk_chofer_suc->fetchColumn() != $id_suc || $chk_veh_suc->fetchColumn() != $id_suc) {
                    $error = 'El chofer y el vehículo deben pertenecer a la sucursal de origen del viaje.';
                }
            }

            if (!$error) {
                $chk_chofer = $this->pdo->prepare(
                    "SELECT cod_viaje FROM viaje WHERE legajo_chofer = :leg AND fecha_llegada_real IS NULL AND cancelado = 0 LIMIT 1 FOR UPDATE"
                );
                $chk_chofer->execute([':leg' => $legajo]);
                if ($chk_chofer->fetch()) {
                    $error = 'El chofer ya tiene un viaje activo.';
                }
                if (!$error) {
                    $chk_veh = $this->pdo->prepare(
                        "SELECT cod_viaje FROM viaje WHERE patente = :pat AND fecha_llegada_real IS NULL AND cancelado = 0 LIMIT 1"
                    );
                    $chk_veh->execute([':pat' => strtoupper($patente)]);
                    if ($chk_veh->fetch()) {
                        $error = 'El vehículo ya está asignado a un viaje activo.';
                    }
                }

                $vehiculo = null;
                if (!$error) {
                    $veh_stmt = $this->pdo->prepare(
                        "SELECT v.*, tv.capacidad_kg_max, tv.id_licencia AS lic_requerida
                         FROM vehiculo v JOIN tipovehiculo tv ON tv.id_tipo_veh = v.id_tipo_veh WHERE v.patente = :p"
                    );
                    $veh_stmt->execute([':p' => strtoupper($patente)]);
                    $vehiculo = $veh_stmt->fetch();

                    $ch_stmt = $this->pdo->prepare("SELECT id_licencia FROM datos_chofer WHERE legajo = :l");
                    $ch_stmt->execute([':l' => $legajo]);
                    $lic_chofer = $ch_stmt->fetchColumn();

                    if (!$lic_chofer || $lic_chofer < $vehiculo['lic_requerida']) {
                        $error = "El chofer no tiene la licencia requerida (necesita {$vehiculo['lic_requerida']}, tiene " . ($lic_chofer ?: '—') . ").";
                    }
                }

                if (!$error) {
                    $peso_total = 0;
                    foreach ($envios_sel as $trk) {
                        $p = $this->pdo->prepare("SELECT peso_kg FROM paquete WHERE nro_tracking = :t");
                        $p->execute([':t' => trim($trk)]);
                        $peso_total += (float) ($p->fetchColumn() ?? 0);
                    }
                    if ($peso_total > (float) $vehiculo['capacidad_kg_max']) {
                        $error = "Peso total ({$peso_total} kg) supera la capacidad del vehículo ({$vehiculo['capacidad_kg_max']} kg).";
                    }
                }
            }

            if (!$error) {
                try {
                    $this->pdo->beginTransaction();
                    $cod = 'VJ-' . strtoupper(substr(md5(uniqid()), 0, 6));
                    $this->pdo->prepare(
                        "INSERT INTO viaje (cod_viaje, legajo_chofer, id_suc_origen, id_suc_destino, patente, fecha_salida, fecha_llegada_est)
                         VALUES (:cod, :legajo, :suc, :suc_dest, :pat, :sal, :est)"
                    )->execute([':cod' => $cod, ':legajo' => $legajo, ':suc' => $id_suc, ':suc_dest' => $id_suc_dest,
                                ':pat' => strtoupper($patente), ':sal' => $fecha_sal, ':est' => $fecha_est]);

                    foreach ($envios_sel as $trk) {
                        $trk = trim($trk);
                        $this->pdo->prepare("INSERT IGNORE INTO viaje_envio (cod_viaje, nro_tracking) VALUES (:cod, :trk)")
                            ->execute([':cod' => $cod, ':trk' => $trk]);
                        $this->pdo->prepare(
                            "INSERT INTO historialestado (nro_tracking, id_estado, id_sucursal, id_usuario, fecha_hora, observacion)
                             VALUES (:trk, 2, :suc, :uid, NOW(), 'Viaje asignado - En Transito')"
                        )->execute([':trk' => $trk, ':suc' => $id_suc, ':uid' => $_SESSION['id_usuario']]);
                    }

                    $this->pdo->commit();
                    header("Location: /admin/router.php?pagina=viajes"); exit();
                } catch (Exception $e) {
                    $this->pdo->rollBack();
                    $error = 'Error en la transacción: ' . $e->getMessage();
                }
            }
        }

        require_once __DIR__ . '/../views/admin/viajes/crear.php';
    }

    // ─── VER DETALLE (admin + chofer) ────────────────────────────

    public function ver(): void {
        if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1,2,3])) {
            header("Location: /admin/index.php"); exit();
        }

        // Modificar fecha estimada
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar_fecha'])) {
            $cod   = trim($_POST['cod_viaje'] ?? '');
            $fecha = trim($_POST['fecha_llegada_est'] ?? '');
            if ($cod && $fecha) {
                $this->pdo->prepare("UPDATE viaje SET fecha_llegada_est = :f WHERE cod_viaje = :cod")
                    ->execute([':f' => $fecha, ':cod' => $cod]);
            }
            header("Location: /admin/router.php?pagina=ver_viaje&cod=" . urlencode($cod)); exit();
        }



        $cod   = trim($_GET['cod'] ?? '');
        $viaje = $this->model->obtenerDetalle($cod);
        if (!$viaje) {
            header("Location: /admin/router.php?pagina=viajes"); exit();
        }

        $model   = $this->model;
        $envios  = $this->model->obtenerEnvios($cod);
        $estado  = $this->model->estadoViaje($viaje);
        $badgeClass = match($estado) {
            'Pendiente'  => 'badge-pendiente',
            'En curso'   => 'badge-encurso',
            'Completado' => 'badge-completado',
            default      => ''
        };

        require_once __DIR__ . '/../views/admin/viajes/ver.php';
    }

    // ─── EDITAR VIAJE (admin + empleado) ─────────────────────────

    public function editar(): void {
        if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1, 2])) {
            header("Location: /admin/login.php"); exit();
        }

        $cod = trim($_GET['cod'] ?? '');
        if (!$cod) { header("Location: /admin/router.php?pagina=viajes"); exit(); }

        $error = '';

        $viaje = $this->model->obtenerDetalle($cod);
        if (!$viaje) { header("Location: /admin/router.php?pagina=viajes"); exit(); }

        if ($viaje['fecha_llegada_real'] !== null) {
            header("Location: /admin/router.php?pagina=ver_viaje&cod=" . urlencode($cod)); exit();
        }

        // La sucursal de origen no se puede cambiar acá: chofer y vehículo se filtran por la
        // sucursal actual del viaje para no poder asignar recursos de otra sucursal.
        $id_suc = $viaje['id_suc_origen'];

        // Se excluyen los choferes/vehículos con OTRO viaje activo, pero no el que ya
        // tiene asignado este mismo viaje (si no, desaparecería de su propio formulario).
        $ch_stmt = $this->pdo->prepare(
            "SELECT u.id_usuario, e.nombre, e.apellido, u.legajo AS legajo_chofer
             FROM usuario u JOIN empleado e ON e.legajo = u.legajo
             WHERE u.id_rol = 3 AND u.estado = 1 AND e.id_sucursal = :suc
               AND e.legajo NOT IN (
                   SELECT legajo_chofer FROM viaje
                   WHERE fecha_llegada_real IS NULL AND cancelado = 0
                     AND legajo_chofer IS NOT NULL AND cod_viaje != :cod
               )
             ORDER BY e.apellido, e.nombre"
        );
        $ch_stmt->execute([':suc' => $id_suc, ':cod' => $cod]);
        $choferes = $ch_stmt->fetchAll();

        $veh_list_stmt = $this->pdo->prepare(
            "SELECT v.patente, tv.nombre AS tipo FROM vehiculo v
             JOIN tipovehiculo tv ON tv.id_tipo_veh = v.id_tipo_veh
             WHERE v.estado = 'Activo' AND v.id_sucursal = :suc
               AND v.patente NOT IN (
                   SELECT patente FROM viaje
                   WHERE fecha_llegada_real IS NULL AND cancelado = 0
                     AND patente IS NOT NULL AND cod_viaje != :cod
               )
             ORDER BY v.patente"
        );
        $veh_list_stmt->execute([':suc' => $id_suc, ':cod' => $cod]);
        $vehiculos = $veh_list_stmt->fetchAll();

        $sucursales = $this->pdo->query(
            "SELECT id_sucursal, nombre FROM sucursal WHERE activo = 1 ORDER BY nombre"
        )->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $legajo      = trim($_POST['legajo_chofer']     ?? '');
            $patente     = strtoupper(trim($_POST['patente'] ?? ''));
            $id_suc_dest = intval($_POST['id_suc_destino']  ?? 0) ?: null;
            $fecha_est   = trim($_POST['fecha_llegada_est'] ?? '');

            if (!$legajo || !$patente || !$id_suc_dest || !$fecha_est) {
                $error = 'Completá todos los campos obligatorios.';
            } elseif ($id_suc === $id_suc_dest) {
                $error = 'La sucursal de origen y de destino no pueden ser la misma.';
            } elseif ($fecha_est <= $viaje['fecha_salida']) {
                $error = 'La fecha de llegada estimada debe ser posterior a la de salida.';
            } else {
                $chk_chofer_suc = $this->pdo->prepare("SELECT id_sucursal FROM empleado WHERE legajo = :l");
                $chk_chofer_suc->execute([':l' => $legajo]);
                $chk_veh_suc = $this->pdo->prepare("SELECT id_sucursal FROM vehiculo WHERE patente = :p");
                $chk_veh_suc->execute([':p' => $patente]);
                if ($chk_chofer_suc->fetchColumn() != $id_suc || $chk_veh_suc->fetchColumn() != $id_suc) {
                    $error = 'El chofer y el vehículo deben pertenecer a la sucursal de origen del viaje.';
                } else {
                    $veh_stmt = $this->pdo->prepare(
                        "SELECT tv.id_licencia AS lic_requerida
                         FROM vehiculo v JOIN tipovehiculo tv ON tv.id_tipo_veh = v.id_tipo_veh WHERE v.patente = :p"
                    );
                    $veh_stmt->execute([':p' => $patente]);
                    $lic_requerida = $veh_stmt->fetchColumn();

                    $lic_chofer = $this->pdo->prepare("SELECT id_licencia FROM datos_chofer WHERE legajo = :l");
                    $lic_chofer->execute([':l' => $legajo]);
                    $lic_chofer = $lic_chofer->fetchColumn();

                    if (!$lic_chofer || $lic_chofer < $lic_requerida) {
                        $error = "El chofer no tiene la licencia requerida (necesita {$lic_requerida}, tiene " . ($lic_chofer ?: '—') . ").";
                    } else {
                        $this->pdo->prepare(
                            "UPDATE viaje SET legajo_chofer=:legajo, patente=:pat, id_suc_destino=:suc_dest, fecha_llegada_est=:est WHERE cod_viaje=:cod"
                        )->execute([':legajo' => $legajo, ':pat' => $patente, ':suc_dest' => $id_suc_dest, ':est' => $fecha_est, ':cod' => $cod]);
                        header("Location: /admin/router.php?pagina=ver_viaje&cod=" . urlencode($cod)); exit();
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/admin/viajes/editar.php';
    }

    // ─── MIS VIAJES (chofer) ─────────────────────────────────────

    public function misViajes(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 3) {
            header("Location: /admin/index.php"); exit();
        }

        $stmt = $this->pdo->prepare("SELECT legajo FROM usuario WHERE id_usuario = :id");
        $stmt->execute([':id' => $_SESSION['id_usuario']]);
        $res = $stmt->fetch();
        $legajo_chofer = $res['legajo'] ?? null;

        if (!$legajo_chofer) {
            header("Location: /admin/index.php"); exit();
        }

        $model   = $this->model;
        $manager = $this->model;
        $viajes  = $this->model->obtenerPorChofer($legajo_chofer);

        require_once __DIR__ . '/../views/chofer/mis_viajes.php';
    }
}
