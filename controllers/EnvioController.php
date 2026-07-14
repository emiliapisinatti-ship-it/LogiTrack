<?php
// controllers/EnvioController.php — Lógica de envíos

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Envio.php';
require_once __DIR__ . '/../models/Paquete.php';
require_once __DIR__ . '/../models/Auditoria.php';

class EnvioController {

    private PDO $pdo;
    private Envio $model;
    private Paquete $paqueteModel;

    public function __construct(PDO $pdo) {
        $this->pdo          = $pdo;
        $this->model        = new Envio($pdo);
        $this->paqueteModel = new Paquete($pdo);
    }

    // ─── LISTADO ADMIN/EMPLEADO ───────────────────────────────────

    public function listado(): void {
        if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1,2])) {
            header("Location: /admin/login.php"); exit();
        }

        // Sucursal del empleado (rol 2)
        $id_sucursal_usuario = null;
        if ($_SESSION['id_rol'] == 2) {
            $stmt = $this->pdo->prepare(
                "SELECT e.id_sucursal FROM empleado e JOIN usuario u ON u.legajo = e.legajo WHERE u.id_usuario = :id"
            );
            $stmt->execute([':id' => $_SESSION['id_usuario']]);
            $res = $stmt->fetch();
            $id_sucursal_usuario = $res['id_sucursal'] ?? null;
        }

        // POST: anular envío (solo admin, solo desde Depósito Origen)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['anular']) && $_SESSION['id_rol'] == 1) {
            $trk = trim($_POST['anular']);
            $check_final = $this->pdo->prepare(
                "SELECT h.id_estado FROM historialestado h
                 WHERE h.nro_tracking = :trk ORDER BY h.fecha_hora DESC, h.id_hist DESC LIMIT 1"
            );
            $check_final->execute([':trk' => $trk]);
            $estado_actual = (int)$check_final->fetchColumn();
            if ($estado_actual === 1) {
                $sp = $this->pdo->prepare("CALL SP_CambiarEstadoEnvio(:trk, 8, :uid, 'Envío anulado por administrador', @res)");
                $sp->execute([':trk' => $trk, ':uid' => $_SESSION['id_usuario']]);
            } elseif (in_array($estado_actual, [5, 6, 8])) {
                // ya en estado final, ignorar silenciosamente
            } else {
                header("Location: /admin/router.php?pagina=envios&error_anular=" . urlencode($trk)); exit();
            }
            header("Location: /admin/router.php?pagina=envios"); exit();
        }

        $filtro_sucursal = $_SESSION['id_rol'] == 1
            ? intval($_GET['sucursal'] ?? 0)
            : $id_sucursal_usuario;
        $filtro_estado = intval($_GET['estado'] ?? 0);
        $filtro_tipo   = trim($_GET['tipo'] ?? ''); // 'saliente' | 'entrante' | 'pendientes' | ''

        // Confirmar recepción de paquete (empleado marca estado 1 desde la lista)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'confirmar_recepcion'
            && $_SESSION['id_rol'] == 2 && $id_sucursal_usuario) {
            $trk = strtoupper(trim($_POST['nro_tracking'] ?? ''));
            if ($trk) {
                // Solo confirmar si el paquete es de esta sucursal origen y todavía no tiene historial
                $chk = $this->pdo->prepare(
                    "SELECT COUNT(*) FROM envio e
                     WHERE e.nro_tracking = :trk AND e.id_suc_origen = :suc
                       AND NOT EXISTS (SELECT 1 FROM historialestado WHERE nro_tracking = e.nro_tracking)"
                );
                $chk->execute([':trk' => $trk, ':suc' => $id_sucursal_usuario]);
                if ((int)$chk->fetchColumn() > 0) {
                    $sp = $this->pdo->prepare("CALL SP_CambiarEstadoEnvio(:trk, 1, :uid, 'Paquete recibido en sucursal', @res)");
                    $sp->execute([':trk' => $trk, ':uid' => $_SESSION['id_usuario']]);
                }
            }
            header("Location: /admin/router.php?pagina=envios&tipo=pendientes"); exit();
        }

        // Paquetes sin estado pendientes de recepción (solo empleados)
        $envios_pendientes = [];
        if ($_SESSION['id_rol'] == 2 && $id_sucursal_usuario) {
            $sp = $this->pdo->prepare("
                SELECT e.nro_tracking, e.fecha_creacion,
                       cr.nombre AS nombre_remitente, cr.apellido AS apellido_remitente,
                       cd.nombre AS nombre_dest, cd.apellido AS apellido_dest,
                       sd.nombre AS suc_destino, e.direccion_entrega
                FROM envio e
                LEFT JOIN cliente cr ON cr.dni = e.dni_remitente
                LEFT JOIN cliente cd ON cd.dni = e.dni_destinatario
                LEFT JOIN sucursal sd ON sd.id_sucursal = e.id_suc_destino
                LEFT JOIN historialestado h ON h.nro_tracking = e.nro_tracking
                WHERE h.nro_tracking IS NULL AND e.id_suc_origen = :suc
                ORDER BY e.fecha_creacion DESC
            ");
            $sp->execute([':suc' => $id_sucursal_usuario]);
            $envios_pendientes = $sp->fetchAll();
        }

        $sucursales = $this->pdo->query("SELECT id_sucursal, nombre FROM sucursal WHERE activo=1 ORDER BY nombre")->fetchAll();
        $estados    = $this->pdo->query("SELECT id_estado, nombre FROM EstadoEnvio ORDER BY id_estado")->fetchAll();

        $busqueda = strtolower(trim($_GET['q'] ?? ''));
        $model  = $this->model;
        $envios = $filtro_sucursal
            ? $this->model->obtenerPorSucursal($filtro_sucursal, $filtro_estado)
            : $this->model->obtenerTodos();

        // Filtro saliente/entrante/pendientes para empleados
        if ($_SESSION['id_rol'] == 2 && $id_sucursal_usuario) {
            if ($filtro_tipo === 'saliente') {
                $envios = array_filter($envios, fn($e) => (int)($e['id_suc_origen'] ?? 0) === (int)$id_sucursal_usuario);
            } elseif ($filtro_tipo === 'entrante') {
                $envios = array_filter($envios, fn($e) => (int)($e['id_suc_destino'] ?? 0) === (int)$id_sucursal_usuario);
            } elseif ($filtro_tipo === 'pendientes') {
                $envios = []; // el listado principal se oculta, solo se muestra la sección de pendientes
            }
        }

        if ($filtro_estado > 0 && !$filtro_sucursal) {
            $nombre_est = '';
            foreach ($estados as $est) {
                if ($est['id_estado'] == $filtro_estado) { $nombre_est = $est['nombre']; break; }
            }
            if ($nombre_est) {
                $envios = array_filter($envios, fn($e) => ($e['estado_actual'] ?? '') === $nombre_est);
            }
        }
        if ($busqueda) {
            $envios = array_filter($envios, fn($e) =>
                str_contains(strtolower($e['nro_tracking']       ?? ''), $busqueda)
                || str_contains(strtolower($e['nombre_remitente'] ?? ''), $busqueda)
                || str_contains(strtolower($e['apellido_remitente'] ?? ''), $busqueda)
                || str_contains(strtolower($e['nombre_dest']      ?? ''), $busqueda)
                || str_contains(strtolower($e['apellido_dest']    ?? ''), $busqueda)
                || str_contains(strtolower($e['suc_destino']      ?? ''), $busqueda)
                || str_contains(strtolower($e['suc_origen']       ?? ''), $busqueda)
            );
        }

        $nombre_sucursal = '—';
        foreach ($sucursales as $s) {
            if ($s['id_sucursal'] == $filtro_sucursal) { $nombre_sucursal = $s['nombre']; break; }
        }

        require_once __DIR__ . '/../views/admin/envios/listado.php';
    }

    // ─── EDITAR DATOS BÁSICOS (admin) ─────────────────────────────

    public function editar(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            header("Location: /admin/router.php?pagina=envios"); exit();
        }

        $trk = trim($_GET['tracking'] ?? '');
        if (!$trk) { header("Location: /admin/router.php?pagina=envios"); exit(); }

        $error   = '';
        $success = '';

        $sucursales = $this->pdo->query("SELECT id_sucursal, nombre FROM sucursal WHERE activo=1 ORDER BY nombre")->fetchAll();

        $stmt = $this->pdo->prepare(
            "SELECT e.*, s.nombre AS suc_destino_nombre
             FROM envio e
             LEFT JOIN sucursal s ON s.id_sucursal = e.id_suc_destino
             WHERE e.nro_tracking = :trk"
        );
        $stmt->execute([':trk' => $trk]);
        $envio = $stmt->fetch();
        if (!$envio) { header("Location: /admin/router.php?pagina=envios"); exit(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_suc_destino   = intval($_POST['id_suc_destino']  ?? 0) ?: null;
            $direccion_entrega = trim($_POST['direccion_entrega'] ?? '') ?: null;

            $cur_q = $this->pdo->prepare("SELECT id_estado FROM historialestado WHERE nro_tracking=:t ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1");
            $cur_q->execute([':t' => $trk]);
            $cur_est = (int)$cur_q->fetchColumn();

            if (in_array($cur_est, [5, 6, 8])) {
                $error = 'No se puede editar un envío ya finalizado (Entregado, Devuelto o Anulado).';
            } else {
                $this->pdo->prepare(
                    "UPDATE envio SET id_suc_destino=:suc, direccion_entrega=:dir WHERE nro_tracking=:trk"
                )->execute([':suc' => $id_suc_destino, ':dir' => $direccion_entrega, ':trk' => $trk]);

                header("Location: /admin/router.php?pagina=gestionar_envios&tracking=" . urlencode($trk)); exit();
            }
        }

        require_once __DIR__ . '/../views/admin/envios/editar.php';
    }

    // ─── GESTIONAR ESTADO ─────────────────────────────────────────

    public function gestionar(): void {
        if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1,2,3])) {
            header("Location: /admin/login.php"); exit();
        }

        $rol = $_SESSION['id_rol'];
        $error   = "";
        $success = "";
        $envio   = null;
        $historial = [];
        $puede_cambiar_estado = true;
        $error_acceso = '';

        // Sucursal del empleado logueado
        $id_suc_usuario = null;
        if ($rol == 2) {
            $stmp = $this->pdo->prepare("SELECT e.id_sucursal FROM empleado e JOIN usuario u ON u.legajo = e.legajo WHERE u.id_usuario = :id");
            $stmp->execute([':id' => $_SESSION['id_usuario']]);
            $id_suc_usuario = $stmp->fetchColumn() ?: null;
        }

        // Legajo del chofer (para Fix 1 y Fix 4)
        $legajo_chofer_actual = null;
        $cod_viaje_activo     = null;
        if ($rol == 3) {
            $ls = $this->pdo->prepare("SELECT legajo FROM usuario WHERE id_usuario = :id");
            $ls->execute([':id' => $_SESSION['id_usuario']]);
            $legajo_chofer_actual = $ls->fetchColumn() ?: null;
            if ($legajo_chofer_actual) {
                $vs = $this->pdo->prepare("SELECT cod_viaje FROM viaje WHERE legajo_chofer = :leg AND fecha_llegada_real IS NULL AND cancelado = 0 AND fecha_salida <= NOW() LIMIT 1");
                $vs->execute([':leg' => $legajo_chofer_actual]);
                $cod_viaje_activo = $vs->fetchColumn() ?: null;
            }
        }

        // Estados según rol (se ajustan después de encontrar el envío para empleados)
        if ($rol == 3) {
            $todos_estados = $this->pdo->query("SELECT id_estado, nombre FROM EstadoEnvio WHERE id_estado IN (2,3,5,7) ORDER BY id_estado")->fetchAll();
        } else {
            $todos_estados = $this->pdo->query("SELECT id_estado, nombre FROM EstadoEnvio ORDER BY id_estado")->fetchAll();
        }
        $estados = $todos_estados;

        $sucursales = $this->pdo->query("SELECT id_sucursal, nombre FROM sucursal WHERE activo=1 ORDER BY nombre")->fetchAll();
        $tracking_buscado = strtoupper(trim($_GET['tracking'] ?? $_POST['tracking_buscar'] ?? ''));

        if ($tracking_buscado) {
            $q = $this->pdo->prepare(
                "SELECT e.nro_tracking, e.fecha_creacion, e.direccion_entrega,
                        e.id_suc_origen, e.id_suc_destino,
                        so.nombre AS suc_origen, sd.nombre AS suc_destino,
                        cr.nombre AS nombre_remitente, cr.apellido AS apellido_remitente,
                        cd.nombre AS nombre_dest, cd.apellido AS apellido_dest
                 FROM Envio e
                 LEFT JOIN Sucursal so ON so.id_sucursal = e.id_suc_origen
                 LEFT JOIN Sucursal sd ON sd.id_sucursal = e.id_suc_destino
                 LEFT JOIN Cliente  cr ON cr.dni = e.dni_remitente
                 LEFT JOIN Cliente  cd ON cd.dni = e.dni_destinatario
                 WHERE e.nro_tracking = :tracking"
            );
            $q->execute([':tracking' => $tracking_buscado]);
            $envio = $q->fetch();

            if (!$envio) {
                $error = "No se encontró el tracking " . htmlspecialchars($tracking_buscado);
            } else {
                // Fix 1: chofer solo puede operar paquetes de su viaje activo
                if ($rol == 3) {
                    if (!$cod_viaje_activo) {
                        $puede_cambiar_estado = false;
                        $error_acceso = 'No tenés un viaje activo en este momento.';
                    } else {
                        $chk = $this->pdo->prepare("SELECT cod_viaje FROM viaje_envio WHERE cod_viaje = :cod AND nro_tracking = :trk");
                        $chk->execute([':cod' => $cod_viaje_activo, ':trk' => $tracking_buscado]);
                        if (!$chk->fetchColumn()) {
                            $puede_cambiar_estado = false;
                            $error_acceso = 'Este paquete no pertenece a tu viaje activo.';
                        }
                    }

                    // Restricción según estado actual y tipo de entrega
                    if ($puede_cambiar_estado) {
                        $cur_q = $this->pdo->prepare("SELECT id_estado FROM historialestado WHERE nro_tracking = :t ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1");
                        $cur_q->execute([':t' => $tracking_buscado]);
                        $cur_chofer_estado = (int)$cur_q->fetchColumn();

                        if ($cur_chofer_estado === 1) {
                            // Paquete asignado al viaje pero aún no retirado → el chofer confirma retiro
                            $estados = array_values(array_filter($todos_estados, fn($e) => $e['id_estado'] === 2));
                        } elseif (!empty($envio['id_suc_destino'])) {
                            $estados = array_values(array_filter($todos_estados, fn($e) => in_array($e['id_estado'], [3, 7])));
                        } else {
                            $estados = array_values(array_filter($todos_estados, fn($e) => in_array($e['id_estado'], [5, 7])));
                        }
                    }
                }

                // Restricción por sucursal para empleados
                if ($rol == 2 && $id_suc_usuario) {
                    $es_origen  = ((int)$envio['id_suc_origen']  === (int)$id_suc_usuario);
                    $es_destino = ((int)$envio['id_suc_destino'] === (int)$id_suc_usuario);

                    if (!$es_origen && !$es_destino) {
                        $puede_cambiar_estado = false;
                        $error_acceso = 'Este envío no pertenece a tu sucursal. Solo podés consultar su historial.';
                    } elseif ($es_origen) {
                        $cur = $this->pdo->prepare("SELECT id_estado FROM historialestado WHERE nro_tracking = :t ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1");
                        $cur->execute([':t' => $tracking_buscado]);
                        $cur_estado = (int)$cur->fetchColumn();
                        if ($cur_estado > 1) {
                            $puede_cambiar_estado = false;
                            $error_acceso = 'Este envío ya fue despachado desde tu sucursal. El estado lo actualiza el chofer o la sucursal destino.';
                        } else {
                            // Solo puede confirmar recepción (estado 1)
                            $estados = array_values(array_filter($todos_estados, fn($e) => $e['id_estado'] == 1));
                        }
                    } else {
                        // Sucursal destino: Listo para Retirar, Entregado, Devuelto
                        $estados = array_values(array_filter($todos_estados, fn($e) => in_array($e['id_estado'], [4, 5, 6])));
                    }
                }

                $qh = $this->pdo->prepare(
                    "SELECT h.id_estado, h.fecha_hora, h.observacion, es.nombre AS estado, u.username
                     FROM HistorialEstado h
                     JOIN EstadoEnvio es ON es.id_estado = h.id_estado
                     LEFT JOIN Usuario u ON u.id_usuario = h.id_usuario
                     WHERE h.nro_tracking = :tracking ORDER BY h.fecha_hora DESC, h.id_hist DESC"
                );
                $qh->execute([':tracking' => $tracking_buscado]);
                $historial = $qh->fetchAll();

                // Estado actual para controlar qué acciones mostrar
                $estado_actual_display = !empty($historial) ? (int)$historial[0]['id_estado'] : 0;
                $es_estado_final = in_array($estado_actual_display, [5, 6, 8]);
                if ($es_estado_final) {
                    $puede_cambiar_estado = false;
                    $error_acceso = 'Este envío ya está en un estado final (Entregado, Devuelto o Anulado) y no puede modificarse.';
                }
            }
        }

        $estado_actual_display = $estado_actual_display ?? 0;
        $es_estado_final       = $es_estado_final       ?? false;

        // Cambiar sucursal: solo admin
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_sucursal') {
            if ($rol != 1) {
                $error = 'Solo el administrador puede cambiar la sucursal de un envío.';
            } else {
                $tracking = strtoupper(trim($_POST['nro_tracking'] ?? ''));
                $tipo_suc = $_POST['tipo_sucursal'] ?? '';
                $id_nueva = intval($_POST['id_sucursal_nueva'] ?? 0);

                if ($tracking && $id_nueva && in_array($tipo_suc, ['origen', 'destino'])) {
                    $campo = $tipo_suc === 'origen' ? 'id_suc_origen' : 'id_suc_destino';
                    $env_check = $this->pdo->prepare("SELECT id_suc_origen, id_suc_destino FROM envio WHERE nro_tracking=:t");
                    $env_check->execute([':t' => $tracking]);
                    $env_row = $env_check->fetch();
                    $otra = $tipo_suc === 'origen' ? (int)($env_row['id_suc_destino'] ?? 0) : (int)($env_row['id_suc_origen'] ?? 0);

                    $cur_q = $this->pdo->prepare("SELECT id_estado FROM historialestado WHERE nro_tracking=:t ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1");
                    $cur_q->execute([':t' => $tracking]);
                    $cur_est = (int)$cur_q->fetchColumn();

                    if (in_array($cur_est, [5, 6, 8])) {
                        $error = 'No se puede modificar la sucursal de un envío ya finalizado (Entregado, Devuelto o Anulado).';
                    } elseif ($cur_est >= 2) {
                        $error = 'No se puede modificar la sucursal de un envío que ya está en viaje. Cancelá el viaje primero si necesitás corregirlo.';
                    } elseif ($otra && $otra === $id_nueva) {
                        $error = "La sucursal de origen y de destino no pueden ser la misma.";
                    } else {
                        $this->pdo->prepare("UPDATE envio SET $campo = :suc WHERE nro_tracking = :t")
                            ->execute([':suc' => $id_nueva, ':t' => $tracking]);
                        $aud = new Auditoria($this->pdo);
                        $aud->registrar('envio', $tracking, 'UPDATE', $_SESSION['id_usuario'], null, [$campo => $id_nueva]);
                        $success = "Sucursal de $tipo_suc actualizada.";
                        header("Location: /admin/router.php?pagina=gestionar_envios&tracking=" . urlencode($tracking)); exit();
                    }
                }
            }
        }

        // Cambiar estado: validar permisos por sucursal
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_estado') {
            $tracking    = strtoupper(trim($_POST['nro_tracking'] ?? ''));
            $id_estado   = intval($_POST['id_estado'] ?? 0);
            $observacion = trim($_POST['observacion'] ?? '');

            if (!$tracking || !$id_estado) {
                $error = "Seleccioná un estado válido.";
            } else {
                // Verificar permisos de sucursal para empleados
                if ($rol == 2 && $id_suc_usuario) {
                    $ec = $this->pdo->prepare("SELECT id_suc_origen, id_suc_destino FROM envio WHERE nro_tracking = :t");
                    $ec->execute([':t' => $tracking]);
                    $er = $ec->fetch();
                    $es_origen  = ((int)($er['id_suc_origen']  ?? 0) === (int)$id_suc_usuario);
                    $es_destino = ((int)($er['id_suc_destino'] ?? 0) === (int)$id_suc_usuario);

                    if (!$es_origen && !$es_destino) {
                        $error = 'No tenés permiso para cambiar el estado de este envío.';
                    } elseif ($es_origen && $id_estado != 1) {
                        $error = 'Desde sucursal origen solo podés marcar "En Depósito Origen".';
                    } elseif ($es_destino && !in_array($id_estado, [4, 5, 6])) {
                        $error = 'Desde sucursal destino solo podés marcar "Listo para Retirar", "Entregado" o "Devuelto".';
                    }
                }

                // Fix 3: validar secuencia de estados
                if (!$error) {
                    $transiciones = [
                        1 => [],         // En Depósito Origen → solo admin avanza (via viaje/despacho)
                        2 => [3, 5, 7],  // En Viaje → En Sucursal Destino | Entregado | Intento fallido
                        3 => [4, 6],     // En Sucursal Destino → Listo para Retirar | Devuelto
                        4 => [5, 6],     // Listo para Retirar → Entregado | Devuelto
                        7 => [3, 5],     // Intento fallido → En Sucursal Destino | Entregado
                    ];
                    $estados_finales = [5, 6, 8]; // Entregado, Devuelto, Anulado

                    $ea = $this->pdo->prepare("SELECT id_estado FROM historialestado WHERE nro_tracking = :t ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1");
                    $ea->execute([':t' => $tracking]);
                    $estado_actual = (int)$ea->fetchColumn();

                    if ($rol == 2 && $estado_actual === 0 && $id_estado != 1) {
                        $error = 'Este envío aún no fue recibido en la sucursal de origen. No se puede cambiar su estado.';
                    }

                    if (!$error && $estado_actual && in_array($estado_actual, $estados_finales)) {
                        $error = 'Este envío ya está en un estado final y no puede modificarse.';
                    }

                    if (!$error && $id_estado === 8 && $estado_actual !== 1) {
                        $error = 'Solo se puede anular un envío que está en Depósito Origen. Si ya fue despachado, debe ser devuelto primero.';
                    }

                    if (!$error && $estado_actual) {
                        $es_retiro_chofer = ($rol == 3 && $estado_actual === 1 && $id_estado === 2);
                        if (!$es_retiro_chofer && isset($transiciones[$estado_actual]) && !in_array($id_estado, $transiciones[$estado_actual])) {
                            $error = 'Transición de estado no válida para el estado actual del envío.';
                        }
                    }
                }
   //¿Qué pasa si el nro_tracking que se le pasa al SP no existe en la base de datos:Llega a php un error
                if (!$error) {
                    try {
                        $sp = $this->pdo->prepare("CALL SP_CambiarEstadoEnvio(:trk, :estado, :uid, :obs, @res)");
                        $sp->execute([':trk' => $tracking, ':estado' => $id_estado, ':uid' => $_SESSION['id_usuario'], ':obs' => $observacion ?: '']);
                        $resultado = $this->pdo->query("SELECT @res")->fetchColumn();
                        if ($resultado !== 'OK') {
                            $error = $resultado ?: "Error al cambiar estado.";
                        } else {
                            // Auto-completar viaje solo si TODOS los paquetes están en estado final (5=Entregado, 6=Devuelto, 8=Anulado)
                            if ($rol == 3 && $cod_viaje_activo) {
                                $pend = $this->pdo->prepare("
                                    SELECT COUNT(*) FROM viaje_envio ve
                                    WHERE ve.cod_viaje = :cod
                                      AND (SELECT id_estado FROM historialestado WHERE nro_tracking = ve.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1) NOT IN (5, 6, 8)
                                ");
                                $pend->execute([':cod' => $cod_viaje_activo]);
                                if ((int)$pend->fetchColumn() === 0) {
                                    $this->pdo->prepare("UPDATE viaje SET fecha_llegada_real = NOW() WHERE cod_viaje = :cod")
                                        ->execute([':cod' => $cod_viaje_activo]);
                                    $success = "Estado actualizado. Todos los paquetes procesados — viaje completado automáticamente.";
                                    header("Location: /admin/router.php?pagina=gestionar_envios&tracking=" . urlencode($tracking)); exit();
                                }
                            }
                            $success = "Estado actualizado correctamente.";
                            header("Location: /admin/router.php?pagina=gestionar_envios&tracking=" . urlencode($tracking)); exit();
                        }
                    } catch (Exception $e) {
                        $error = "Error al cambiar estado: " . $e->getMessage();
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/admin/envios/gestionar.php';
    }

    // ─── MIS ENVÍOS (cliente) ─────────────────────────────────────

    public function misEnvios(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
            header("Location: /cliente/login.php"); exit();
        }

        $stmt = $this->pdo->prepare("SELECT dni_cliente FROM usuario WHERE id_usuario = :id");
        $stmt->execute([':id' => $_SESSION['id_usuario']]);
        $row = $stmt->fetch();
        $dni = $row['dni_cliente'] ?? null;

        // Cancelar envío (solo si está en estado 1 o sin estado aún)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar'])) {
            $trk = trim($_POST['cancelar']);
            $id_anulado = $this->pdo->query(
                "SELECT id_estado FROM EstadoEnvio WHERE nombre LIKE '%nulad%' OR nombre LIKE '%ancelad%' LIMIT 1"
            )->fetchColumn();
            $check = $this->pdo->prepare(
                "SELECT h.id_estado FROM historialestado h
                 JOIN envio e ON e.nro_tracking = h.nro_tracking
                 WHERE e.nro_tracking = :trk AND e.dni_remitente = :dni
                 ORDER BY h.fecha_hora DESC LIMIT 1"
            );
            $check->execute([':trk' => $trk, ':dni' => $dni]);
            $estado_actual = $check->fetchColumn();
            // Permite cancelar si: no tiene historial aún, o está en En Depósito Origen (1)
            $puede_cancelar = ($estado_actual === false || (int)$estado_actual === 1);
            if ($id_anulado && $puede_cancelar) {
                $sp = $this->pdo->prepare("CALL SP_CambiarEstadoEnvio(:trk, :est, :uid, 'Cancelado por el cliente', @res)");
                $sp->execute([':trk' => $trk, ':est' => $id_anulado, ':uid' => $_SESSION['id_usuario']]);
            }
            header("Location: /cliente/router.php?pagina=mis_envios"); exit();
        }

        $model  = $this->model;
        $envios = $dni ? $this->model->obtenerPorCliente($dni) : [];

        $filtro_estado = trim($_GET['estado'] ?? '');
        if ($filtro_estado === 'en_curso') {
            $envios = array_filter($envios, function($e) {
                $s = strtolower($e['estado_actual'] ?? '');
                return str_contains($s, 'viaje') || str_contains($s, 'tránsito') || str_contains($s, 'transito') || str_contains($s, 'dep');
            });
        } elseif ($filtro_estado === 'entregado') {
            $envios = array_filter($envios, fn($e) => str_contains(strtolower($e['estado_actual'] ?? ''), 'entregado'));
        }

        $id_estado_inicial = (int) $this->pdo->query(
            "SELECT id_estado FROM EstadoEnvio ORDER BY id_estado ASC LIMIT 1"
        )->fetchColumn();

        require_once __DIR__ . '/../views/cliente/mis_envios.php';
    }

    // ─── RASTREAR PEDIDO (cliente) ────────────────────────────────

    public function rastrear(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
            header("Location: /cliente/login.php"); exit();
        }

        $stmt = $this->pdo->prepare("SELECT dni_cliente FROM Usuario WHERE id_usuario = :id");
        $stmt->execute([':id' => $_SESSION['id_usuario']]);
        $res = $stmt->fetch();
        $dni_cliente = $res['dni_cliente'] ?? null;

        $envio    = null;
        $historial = [];
        $paquetes = [];
        $error    = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['tracking'])) {
            $tracking = strtoupper(trim($_POST['tracking'] ?? $_GET['tracking'] ?? ''));

            $q = $this->pdo->prepare(
                "SELECT e.*, so.nombre AS suc_origen, sd.nombre AS suc_destino,
                        cr.nombre AS nombre_remitente, cr.apellido AS apellido_remitente,
                        cd.nombre AS nombre_dest, cd.apellido AS apellido_dest
                 FROM Envio e
                 LEFT JOIN Sucursal so ON so.id_sucursal = e.id_suc_origen
                 LEFT JOIN Sucursal sd ON sd.id_sucursal = e.id_suc_destino
                 LEFT JOIN Cliente  cr ON cr.dni = e.dni_remitente
                 LEFT JOIN Cliente  cd ON cd.dni = e.dni_destinatario
                 WHERE e.nro_tracking = :tracking
                   AND (e.dni_remitente = :dni1 OR e.dni_destinatario = :dni2)"
            );
            $q->execute([':tracking' => $tracking, ':dni1' => $dni_cliente, ':dni2' => $dni_cliente]);
            $envio = $q->fetch();

            if (!$envio) {
                $error = "No se encontró ese tracking o no pertenece a tu cuenta.";
            } else {
                $paquetes = $this->paqueteModel->obtenerTodosPorTracking($tracking);

                $qh = $this->pdo->prepare(
                    "SELECT h.fecha_hora, h.observacion, es.nombre AS estado
                     FROM HistorialEstado h
                     JOIN EstadoEnvio es ON es.id_estado = h.id_estado
                     WHERE h.nro_tracking = :tracking ORDER BY h.fecha_hora DESC"
                );
                $qh->execute([':tracking' => $tracking]);
                $historial = $qh->fetchAll();
            }
        }

        require_once __DIR__ . '/../views/cliente/rastrear.php';
    }

    // ─── ENVIAR PAQUETE (cliente) ─────────────────────────────────

    public function enviarPaquete(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
            header("Location: /cliente/login.php"); exit();
        }

        $error   = "";
        $success = "";

        if (!empty($_GET['ok'])) {
            $trk = htmlspecialchars($_GET['ok']);
            $success = "Envío registrado. Tu número de tracking es: <strong>$trk</strong>";
        }

        $stmt = $this->pdo->prepare("SELECT dni_cliente FROM Usuario WHERE id_usuario = :id");
        $stmt->execute([':id' => $_SESSION['id_usuario']]);
        $res = $stmt->fetch();
        $dni_remitente = $res['dni_cliente'] ?? null;

        $sucursales = $this->pdo->query("SELECT id_sucursal, nombre FROM Sucursal WHERE activo=1 ORDER BY nombre")->fetchAll();
        $tipos      = $this->paqueteModel->obtenerTiposContenido();

        // Detectar columnas opcionales de la tabla cliente
        $cols_cliente   = array_column($this->pdo->query("SHOW COLUMNS FROM cliente")->fetchAll(), 'Field');
        $tiene_telefono = in_array('telefono', $cols_cliente);
        $tiene_email    = in_array('email',    $cols_cliente);

        // Inicializar variables de vista para evitar warnings en GET
        $dest_existe = false;

        $n_paquetes = max(1, intval($_POST['n_paquetes'] ?? 1));
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_paquete'])) {
            $n_paquetes = min($n_paquetes + 1, 10);
            require_once __DIR__ . '/../views/cliente/enviar.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dni_dest      = trim($_POST['dni_destinatario']    ?? '');
            $nombre_dest   = trim($_POST['nombre_destinatario'] ?? '');
            $apellido_dest = trim($_POST['apellido_destinatario'] ?? '');
            $telefono_dest = $tiene_telefono ? (trim($_POST['telefono_destinatario'] ?? '') ?: null) : null;
            $email_dest    = $tiene_email    ? (trim($_POST['email_destinatario']    ?? '') ?: null) : null;
            $modalidad     = $_POST['modalidad'] ?? 'sucursal';
            $id_suc_origen = intval($_POST['id_suc_origen'] ?? 0);

            $id_suc_destino = $modalidad === 'sucursal' ? intval($_POST['id_suc_destino'] ?? 0) : null;
            $dir_entrega    = $modalidad === 'domicilio' ? trim($_POST['direccion_entrega'] ?? '') : null;

            // Construir lista de paquetes desde arrays del POST
            $paquetes_post = [];
            foreach (($_POST['peso_kg'] ?? []) as $i => $peso) {
                $p = floatval($peso);
                if ($p > 0) {
                    $paquetes_post[] = [
                        'peso'       => $p,
                        'alto'       => intval($_POST['alto_cm'][$i] ?? 0),
                        'ancho'      => intval($_POST['ancho_cm'][$i] ?? 0),
                        'largo'      => intval($_POST['largo_cm'][$i] ?? 0),
                        'descripcion'=> trim($_POST['descripcion'][$i] ?? '') ?: null,
                        'id_tipo'    => intval($_POST['id_tipo_cont'][$i] ?? 0) ?: null,
                    ];
                }
            }

            $checkDest = $this->pdo->prepare("SELECT dni FROM Cliente WHERE dni = :dni");
            $checkDest->execute([':dni' => $dni_dest]);
            $dest_existe = (bool) $checkDest->fetch();

            if (empty($dni_dest) || !$id_suc_origen || empty($paquetes_post)) {
                $error = "Completá todos los campos obligatorios (al menos un paquete con peso).";
            } elseif (empty($nombre_dest) || empty($apellido_dest)) {
                $error = "Ingresá el nombre y apellido del destinatario.";
            } elseif ($modalidad === 'sucursal' && !$id_suc_destino) {
                $error = "Seleccioná la sucursal de destino.";
            } elseif ($modalidad === 'sucursal' && $id_suc_destino === $id_suc_origen) {
                $error = "La sucursal de origen y de destino no pueden ser la misma.";
            } elseif ($modalidad === 'domicilio' && empty($dir_entrega)) {
                $error = "Ingresá la dirección de entrega.";
            } else {
                try {
                    // 1. Upsert destinatario
                    if (!$dest_existe) {
                        $campos_sql = 'dni, nombre, apellido';
                        $params_sql = ':dni, :nombre, :apellido';
                        $params = [':dni' => $dni_dest, ':nombre' => $nombre_dest, ':apellido' => $apellido_dest];
                        if ($tiene_telefono) { $campos_sql .= ', telefono'; $params_sql .= ', :tel'; $params[':tel'] = $telefono_dest; }
                        if ($tiene_email)    { $campos_sql .= ', email';    $params_sql .= ', :email'; $params[':email'] = $email_dest; }
                        $this->pdo->prepare("INSERT INTO Cliente ($campos_sql) VALUES ($params_sql)")->execute($params);
                    } else {
                        $set_sql = 'nombre=:nombre, apellido=:apellido';
                        $params  = [':dni' => $dni_dest, ':nombre' => $nombre_dest, ':apellido' => $apellido_dest];
                        if ($tiene_telefono) { $set_sql .= ', telefono=:tel';   $params[':tel']   = $telefono_dest; }
                        if ($tiene_email)    { $set_sql .= ', email=:email';    $params[':email'] = $email_dest; }
                        $this->pdo->prepare("UPDATE Cliente SET $set_sql WHERE dni=:dni")->execute($params);
                    }
             //p.almacenado
                    // 2. SP registra Envio + primer Paquete; la DB genera el tracking
                    $first = $paquetes_post[0];
                    $sp = $this->pdo->prepare(
                        "CALL SP_RegistrarNuevoEnvio(:rem, :dest, :orig, :dst, :dir, :uid,
                                                     :peso, :alto, :ancho, :largo, :desc, :tipo, @trk)"
                    );
                    $sp->execute([
                        ':rem'  => $dni_remitente,
                        ':dest' => $dni_dest,
                        ':orig' => $id_suc_origen,
                        ':dst'  => $id_suc_destino ?? 0,
                        ':dir'  => $dir_entrega    ?? '',
                        ':uid'  => $_SESSION['id_usuario'],
                        ':peso' => $first['peso'],
                        ':alto' => $first['alto'],
                        ':ancho'=> $first['ancho'],
                        ':largo'=> $first['largo'],
                        ':desc' => $first['descripcion'],
                        ':tipo' => $first['id_tipo'] ?? 0,
                    ]);
                    $nro_tracking = $this->pdo->query("SELECT @trk")->fetchColumn();
                    if (!$nro_tracking) {
                        throw new Exception("El procedimiento no pudo registrar el envío.");
                    }

                    // 3. Paquetes adicionales (2+), HistorialEstado y Auditoria
                    $this->pdo->beginTransaction();
                    for ($i = 1; $i < count($paquetes_post); $i++) {
                        $paq = $paquetes_post[$i];
                        $this->paqueteModel->insertar(
                            $nro_tracking, $paq['peso'], $paq['alto'], $paq['ancho'],
                            $paq['largo'], $paq['descripcion'], $paq['id_tipo']
                        );
                    }
                    // No se asigna estado automáticamente — el empleado de la sucursal
                    // confirma la recepción física del paquete y marca "En Depósito Origen".
                    $aud = new Auditoria($this->pdo);
                    $aud->registrar('envio', $nro_tracking, 'INSERT', $_SESSION['id_usuario'], null,
                        ['dni_remitente' => $dni_remitente, 'dni_destinatario' => $dni_dest]);
                    $this->pdo->commit();

                    header("Location: /cliente/router.php?pagina=enviar&ok=" . urlencode($nro_tracking));
                    exit();
                } catch (Exception $e) {
                    if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                    // Si el SP ya insertó pero el paso 3 falló, eliminar el envío para no dejar datos colgados
                    if (isset($nro_tracking)) {
                        $this->pdo->prepare("DELETE FROM Envio WHERE nro_tracking = :trk")
                            ->execute([':trk' => $nro_tracking]);
                    }
                    error_log("EnvioController::enviarPaquete — " . $e->getMessage());
                    $error = "Error al registrar el envío. Intentá de nuevo.";
                }
            }
        }

        require_once __DIR__ . '/../views/cliente/enviar.php';
    }
}
