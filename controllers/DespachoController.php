<?php
// controllers/DespachoController.php — Despacho masivo de paquetes

require_once __DIR__ . '/../config/db.php';

class DespachoController {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 2) {
            header("Location: /admin/router.php?pagina=viajes"); exit();
        }

        $error   = '';
        $success = htmlspecialchars($_GET['ok'] ?? '');

        // Sucursal del empleado
        $id_sucursal      = null;
        $nombre_sucursal  = null;
        if ($_SESSION['id_rol'] == 2) {
            $stmt = $this->pdo->prepare(
                "SELECT e.id_sucursal, s.nombre AS suc_nombre
                 FROM empleado e
                 JOIN usuario u ON u.legajo = e.legajo
                 LEFT JOIN sucursal s ON s.id_sucursal = e.id_sucursal
                 WHERE u.id_usuario = :id"
            );
            $stmt->execute([':id' => $_SESSION['id_usuario']]);
            $res = $stmt->fetch();
            $id_sucursal     = $res['id_sucursal'] ?? null;
            $nombre_sucursal = $res['suc_nombre']  ?? null;
        }

        // Sucursal destino seleccionada (filtro GET); 'domicilio' = entrega a domicilio
        $filtro_suc_dest = trim($_GET['suc_dest'] ?? '');
        $es_domicilio    = ($filtro_suc_dest === 'domicilio');

        // Todas las sucursales para el selector de destino
        $sucursales_destino = $this->pdo->query(
            "SELECT id_sucursal, nombre FROM sucursal WHERE activo=1 ORDER BY nombre"
        )->fetchAll();

        // Paquetes cuyo último estado es "En Depósito Origen" (id_estado = 1) y sin viaje asignado
        $sql_paquetes = "
            SELECT e.nro_tracking, e.id_suc_destino, p.peso_kg,
                   cr.nombre AS nom_rem, cr.apellido AS ape_rem,
                   cd.nombre AS nom_dest, cd.apellido AS ape_dest,
                   sd.nombre AS suc_destino, e.direccion_entrega,
                   so.nombre AS suc_origen_nombre
            FROM envio e
            JOIN paquete p ON p.nro_tracking = e.nro_tracking
            LEFT JOIN cliente cr ON cr.dni = e.dni_remitente
            LEFT JOIN cliente cd ON cd.dni = e.dni_destinatario
            LEFT JOIN sucursal sd ON sd.id_sucursal = e.id_suc_destino
            LEFT JOIN sucursal so ON so.id_sucursal = e.id_suc_origen
            WHERE (
                SELECT id_estado FROM historialestado
                WHERE nro_tracking = e.nro_tracking
                ORDER BY fecha_hora DESC, id_hist DESC
                LIMIT 1
            ) = 1
              AND e.nro_tracking NOT IN (SELECT nro_tracking FROM viaje_envio)";
        if ($_SESSION['id_rol'] == 2 && $id_sucursal) {
            $sql_paquetes .= " AND e.id_suc_origen = " . intval($id_sucursal);
        }
        if ($es_domicilio) {
            $sql_paquetes .= " AND e.id_suc_destino IS NULL";
        } elseif (intval($filtro_suc_dest)) {
            $sql_paquetes .= " AND e.id_suc_destino = " . intval($filtro_suc_dest);
        }
        $sql_paquetes .= " ORDER BY e.nro_tracking";
        $paquetes = $this->pdo->query($sql_paquetes)->fetchAll();

        // Vehículos activos
        $sql_veh = "SELECT v.patente, v.modelo, tv.nombre AS tipo, tv.capacidad_kg_max, tv.id_licencia, tv.id_tipo_veh
                    FROM vehiculo v JOIN tipovehiculo tv ON tv.id_tipo_veh = v.id_tipo_veh WHERE v.estado = 'Activo'";
        if ($id_sucursal) $sql_veh .= " AND v.id_sucursal = " . intval($id_sucursal);
        $sql_veh .= " ORDER BY tv.capacidad_kg_max";
        $vehiculos = $this->pdo->query($sql_veh)->fetchAll();

        // Choferes disponibles (sin viaje activo, misma sucursal del empleado)
        $ch_sql = "SELECT u.id_usuario, e.nombre, e.apellido, u.legajo AS legajo_chofer, dc.id_licencia
                   FROM usuario u
                   JOIN empleado e ON e.legajo = u.legajo
                   LEFT JOIN datos_chofer dc ON dc.legajo = u.legajo
                   WHERE u.id_rol = 3 AND u.estado = 1
                     AND u.legajo NOT IN (
                         SELECT legajo_chofer FROM viaje
                         WHERE fecha_llegada_real IS NULL AND cancelado = 0
                         AND legajo_chofer IS NOT NULL
                     )";
        $ch_params = [];
        if ($id_sucursal) { $ch_sql .= " AND e.id_sucursal = :suc"; $ch_params[':suc'] = $id_sucursal; }
        $ch_sql .= " ORDER BY e.apellido, e.nombre";
        $ch_stmt = $this->pdo->prepare($ch_sql);
        $ch_stmt->execute($ch_params);
        $choferes = $ch_stmt->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patente           = trim($_POST['patente']           ?? '');
            $legajo_chofer     = trim($_POST['legajo_chofer']     ?? '');
            $fecha_salida      = trim($_POST['fecha_salida']      ?? '');
            $fecha_est         = trim($_POST['fecha_llegada_est'] ?? '');
            $id_suc_destino    = intval($_POST['id_suc_destino']  ?? 0) ?: null;
            $es_domicilio_post = !empty($_POST['es_domicilio']);
            $trackings         = $_POST['trackings'] ?? [];

            if (!$patente || !$legajo_chofer || !$fecha_salida || !$fecha_est || (!$id_suc_destino && !$es_domicilio_post) || empty($trackings)) {
                $error = 'Completá todos los campos, seleccioná el tipo de destino y al menos un paquete.';
            } elseif (strtotime($fecha_salida) < time()) {
                $fmt_sal = date('d/m/Y H:i', strtotime($fecha_salida));
                $ahora   = date('d/m/Y H:i');
                $error = "La fecha de salida ($fmt_sal) no puede ser en el pasado. Ahora son las $ahora.";
            } elseif ($fecha_est <= $fecha_salida) {
                $fmt_sal = date('d/m/Y H:i', strtotime($fecha_salida));
                $fmt_est = date('d/m/Y H:i', strtotime($fecha_est));
                $error = "La llegada estimada ($fmt_est) no puede ser anterior o igual a la salida ($fmt_sal).";
            } else {
                $chk = $this->pdo->prepare("SELECT cod_viaje FROM viaje WHERE legajo_chofer = :leg AND fecha_llegada_real IS NULL AND cancelado = 0 LIMIT 1");
                $chk->execute([':leg' => $legajo_chofer]);
                if ($chk->fetch()) { $error = 'El chofer ya tiene un viaje activo.'; }

                if (!$error) {
                    $chk2 = $this->pdo->prepare("SELECT cod_viaje FROM viaje WHERE patente = :pat AND fecha_llegada_real IS NULL AND cancelado = 0 LIMIT 1");
                    $chk2->execute([':pat' => $patente]);
                    if ($chk2->fetch()) { $error = 'El vehículo ya está asignado a un viaje activo.'; }
                }
            }

            if (!$error) {
                $veh_stmt = $this->pdo->prepare(
                    "SELECT v.*, tv.capacidad_kg_max, tv.id_licencia AS lic_requerida
                     FROM vehiculo v JOIN tipovehiculo tv ON tv.id_tipo_veh = v.id_tipo_veh WHERE v.patente = :p"
                );
                $veh_stmt->execute([':p' => $patente]);
                $vehiculo = $veh_stmt->fetch();

                $ch_stmt = $this->pdo->prepare("SELECT dc.id_licencia FROM datos_chofer dc WHERE dc.legajo = :l");
                $ch_stmt->execute([':l' => $legajo_chofer]);
                $chofer_data = $ch_stmt->fetch();

                $peso_total = 0;
                foreach ($trackings as $trk) {
                    $p = $this->pdo->prepare("SELECT peso_kg FROM paquete WHERE nro_tracking = :t");
                    $p->execute([':t' => $trk]);
                    $peso_total += (float) ($p->fetchColumn() ?? 0);
                }

                // Validar que los paquetes con sucursal destino coincidan con el destino del viaje
                if (!$error) {
                    $placeholders = implode(',', array_fill(0, count($trackings), '?'));
                    $chk_dest = $this->pdo->prepare(
                        "SELECT COUNT(*) FROM envio
                         WHERE nro_tracking IN ($placeholders)
                           AND id_suc_destino IS NOT NULL
                           AND id_suc_destino != ?"
                    );
                    $chk_dest->execute([...array_values($trackings), $id_suc_destino]);
                    if ((int)$chk_dest->fetchColumn() > 0) {
                        $error = 'Algunos paquetes tienen una sucursal destino diferente a la del viaje. Usá el filtro de sucursal destino para seleccionarlos correctamente.';
                    }
                }

                if ($peso_total > (float) $vehiculo['capacidad_kg_max']) {
                    $error = "Peso total ({$peso_total} kg) supera la capacidad del vehículo ({$vehiculo['capacidad_kg_max']} kg).";
                } elseif (!$chofer_data || $chofer_data['id_licencia'] < $vehiculo['lic_requerida']) {
                    $lic_tiene = $chofer_data['id_licencia'] ?? '—';
                    $error = "El chofer no tiene la licencia requerida (necesita {$vehiculo['lic_requerida']}, tiene {$lic_tiene}).";
                } else {
                    try {
                        $this->pdo->beginTransaction();
                        $cod_viaje = 'VJ-' . strtoupper(substr(md5(uniqid()), 0, 6));
                        $this->pdo->prepare(
                            "INSERT INTO viaje (cod_viaje, legajo_chofer, id_suc_origen, id_suc_destino, patente, fecha_salida, fecha_llegada_est)
                             VALUES (:cod, :leg, :suc, :suc_dest, :pat, :sal, :est)"
                        )->execute([
                            ':cod'      => $cod_viaje,
                            ':leg'      => $legajo_chofer,
                            ':suc'      => $id_sucursal,
                            ':suc_dest' => $id_suc_destino,
                            ':pat'      => $patente,
                            ':sal'      => $fecha_salida,
                            ':est'      => $fecha_est,
                        ]);
                        foreach ($trackings as $trk) {
                            $trk = trim($trk);
                            $this->pdo->prepare("INSERT IGNORE INTO viaje_envio (cod_viaje, nro_tracking) VALUES (:cod, :trk)")
                                ->execute([':cod' => $cod_viaje, ':trk' => $trk]);
                            // No cambiamos el estado aquí: el chofer confirma el retiro marcando "En Viaje"
                        }
                        $this->pdo->commit();
                        $msg = "Viaje {$cod_viaje} creado con " . count($trackings) . " paquete(s). Peso total: {$peso_total} kg.";
                        header("Location: /admin/router.php?pagina=despacho&ok=" . urlencode($msg));
                        exit();
                    } catch (Exception $e) {
                        $this->pdo->rollBack();
                        $error = "Error en la transacción: " . $e->getMessage();
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/empleado/despacho.php';
    }
}
