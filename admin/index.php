<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireAuth([1,2,3], '/admin/login.php');

switch ($_SESSION['id_rol']) {

    // ─── ADMIN ───────────────────────────────────────────────────────
    case 1:
        try {
            $kpi_envios   = (int)$pdo->query("SELECT COUNT(*) FROM Envio")->fetchColumn();
            $kpi_choferes = (int)$pdo->query("SELECT COUNT(*) FROM usuario WHERE id_rol = 3 AND estado = 1")->fetchColumn();
            $kpi_viajes   = (int)$pdo->query("SELECT COUNT(*) FROM viaje WHERE fecha_llegada_real IS NULL AND fecha_salida <= NOW() AND cancelado = 0")->fetchColumn();
            try { $kpi_incidentes = (int)$pdo->query("SELECT COUNT(*) FROM incidente WHERE estado = 'abierto'")->fetchColumn(); }
            catch (PDOException $e) { $kpi_incidentes = 0; }
            $id_est_ini = (int)$pdo->query("SELECT id_estado FROM EstadoEnvio ORDER BY id_estado ASC LIMIT 1")->fetchColumn();
            $sp = $pdo->prepare("
                SELECT COUNT(DISTINCT e.nro_tracking) FROM Envio e
                JOIN HistorialEstado h ON h.nro_tracking = e.nro_tracking
                WHERE h.id_estado = :id AND h.fecha_hora = (
                    SELECT MAX(h2.fecha_hora) FROM HistorialEstado h2 WHERE h2.nro_tracking = e.nro_tracking
                )");
            $sp->execute([':id' => $id_est_ini]);
            $kpi_pendientes = (int)$sp->fetchColumn();
        } catch (PDOException $e) {
            $kpi_envios = $kpi_choferes = $kpi_viajes = $kpi_incidentes = $kpi_pendientes = 0;
        }

        // Viajes activos (últimos 5)
        try {
            $viajes_activos = $pdo->query("
                SELECT v.cod_viaje, v.fecha_salida, v.fecha_llegada_est,
                       e.nombre AS nom_chofer, e.apellido AS ape_chofer,
                       s.nombre AS suc_origen
                FROM viaje v
                LEFT JOIN empleado e ON e.legajo = v.legajo_chofer
                LEFT JOIN sucursal s ON s.id_sucursal = v.id_suc_origen
                WHERE v.fecha_llegada_real IS NULL AND v.cancelado = 0
                ORDER BY v.fecha_salida DESC LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $viajes_activos = []; }

        // Incidentes abiertos recientes (últimos 5)
        try {
            $incidentes_recientes = $pdo->query("
                SELECT i.nro_incidente, i.descripcion, i.fecha_hora, i.cod_viaje,
                       t.nombre AS tipo
                FROM incidente i
                LEFT JOIN tipoincidente t ON t.id_tipo_inc = i.id_tipo_inc
                WHERE i.estado = 'abierto'
                ORDER BY i.fecha_hora DESC LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $incidentes_recientes = []; }

        require_once __DIR__ . '/../views/admin/panel.php';
        break;

    // ─── EMPLEADO ────────────────────────────────────────────────────
    case 2:
        $id_sucursal_emp = null;
        $nombre_sucursal_emp = null;
        try {
            $s = $pdo->prepare("
                SELECT e.id_sucursal, s.nombre AS suc_nombre
                FROM empleado e JOIN usuario u ON u.legajo = e.legajo
                LEFT JOIN sucursal s ON s.id_sucursal = e.id_sucursal
                WHERE u.id_usuario = :id
            ");
            $s->execute([':id' => $_SESSION['id_usuario']]);
            $row = $s->fetch();
            $id_sucursal_emp    = $row['id_sucursal'] ?? null;
            $nombre_sucursal_emp = $row['suc_nombre'] ?? null;
        } catch (PDOException $e) {}

        $emp_pendientes = $emp_incidentes = $emp_viajes_activos = 0;
        $emp_envios_lista = $emp_viajes_lista = [];

        if ($id_sucursal_emp) {
            try {
                $id_est_ini = (int)$pdo->query("SELECT id_estado FROM EstadoEnvio ORDER BY id_estado ASC LIMIT 1")->fetchColumn();

                // Envíos pendientes: misma lógica que despacho
                $sp = $pdo->prepare("
                    SELECT COUNT(*) FROM Envio e
                    WHERE COALESCE((SELECT id_estado FROM historialestado WHERE nro_tracking = e.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1), 1) = 1
                      AND e.id_suc_origen = :suc
                      AND e.nro_tracking NOT IN (SELECT nro_tracking FROM viaje_envio)
                ");
                $sp->execute([':suc' => $id_sucursal_emp]);
                $emp_pendientes = (int)$sp->fetchColumn();

                // Incidentes abiertos: todos (igual que la página de incidentes)
                $si = $pdo->query("SELECT COUNT(*) FROM incidente WHERE estado = 'abierto'");
                $emp_incidentes = (int)$si->fetchColumn();

                // Viajes activos de esta sucursal
                $sv = $pdo->prepare("SELECT COUNT(*) FROM viaje WHERE id_suc_origen = :suc AND cancelado = 0 AND fecha_llegada_real IS NULL");
                $sv->execute([':suc' => $id_sucursal_emp]);
                $emp_viajes_activos = (int)$sv->fetchColumn();

                // Lista de envíos pendientes (hasta 5)
                $sl = $pdo->prepare("
                    SELECT e.nro_tracking,
                           CONCAT(COALESCE(cd.nombre,''),' ',COALESCE(cd.apellido,'')) AS destinatario,
                           sd.nombre AS suc_destino
                    FROM Envio e
                    LEFT JOIN cliente cd ON cd.dni = e.dni_destinatario
                    LEFT JOIN sucursal sd ON sd.id_sucursal = e.id_suc_destino
                    WHERE COALESCE((SELECT id_estado FROM historialestado WHERE nro_tracking = e.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1), 1) = 1
                      AND e.id_suc_origen = :suc
                      AND e.nro_tracking NOT IN (SELECT nro_tracking FROM viaje_envio)
                    LIMIT 5
                ");
                $sl->execute([':suc' => $id_sucursal_emp]);
                $emp_envios_lista = $sl->fetchAll(PDO::FETCH_ASSOC);

                // Lista de viajes activos (hasta 5)
                $svl = $pdo->prepare("
                    SELECT v.cod_viaje, v.fecha_salida, v.fecha_llegada_est,
                           e.nombre AS nom_chofer, e.apellido AS ape_chofer,
                           (SELECT COUNT(*) FROM viaje_envio ve WHERE ve.cod_viaje = v.cod_viaje) AS total_envios
                    FROM viaje v
                    LEFT JOIN empleado e ON e.legajo = v.legajo_chofer
                    WHERE v.id_suc_origen = :suc AND v.cancelado = 0 AND v.fecha_llegada_real IS NULL
                    ORDER BY v.fecha_salida DESC LIMIT 5
                ");
                $svl->execute([':suc' => $id_sucursal_emp]);
                $emp_viajes_lista = $svl->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {}
        }

        require_once __DIR__ . '/../views/empleado/panel.php';
        break;

    // ─── CHOFER ───────────────────────────────────────────────────────
    case 3:
        $chofer_legajo     = null;
        $viaje_actual      = null;
        $viaje_proximo     = null;
        $viaje_paquetes    = 0;
        $chofer_incidentes = [];

        try {
            $r = $pdo->prepare("SELECT legajo FROM usuario WHERE id_usuario = :id");
            $r->execute([':id' => $_SESSION['id_usuario']]);
            $chofer_legajo = $r->fetchColumn() ?: null;
        } catch (PDOException $e) {}

        $chofer_vehiculos = [];

        if ($chofer_legajo) {
            try {
                $viaje_cols = "v.cod_viaje, v.fecha_salida, v.fecha_llegada_est,
                           s.nombre AS suc_origen, sd.nombre AS suc_destino, v.patente,
                           veh.modelo, tv.nombre AS tipo_veh, tv.capacidad_kg_max
                    FROM viaje v
                    LEFT JOIN sucursal s  ON s.id_sucursal  = v.id_suc_origen
                    LEFT JOIN sucursal sd ON sd.id_sucursal = v.id_suc_destino
                    LEFT JOIN vehiculo veh ON veh.patente = v.patente
                    LEFT JOIN tipovehiculo tv ON tv.id_tipo_veh = veh.id_tipo_veh";

                // Viaje en curso: ya salió pero no llegó
                $sv = $pdo->prepare("SELECT $viaje_cols
                    WHERE v.legajo_chofer = :leg AND v.cancelado = 0
                      AND v.fecha_llegada_real IS NULL AND v.fecha_salida <= NOW()
                    ORDER BY v.fecha_salida DESC LIMIT 1");
                $sv->execute([':leg' => $chofer_legajo]);
                $viaje_actual = $sv->fetch(PDO::FETCH_ASSOC) ?: null;

                // Próximo viaje: todavía no salió
                $svp = $pdo->prepare("SELECT $viaje_cols
                    WHERE v.legajo_chofer = :leg AND v.cancelado = 0
                      AND v.fecha_llegada_real IS NULL AND v.fecha_salida > NOW()
                    ORDER BY v.fecha_salida ASC LIMIT 1");
                $svp->execute([':leg' => $chofer_legajo]);
                $viaje_proximo = $svp->fetch(PDO::FETCH_ASSOC) ?: null;

                $viaje_paquetes = 0;
                $viaje_ref = $viaje_actual ?? $viaje_proximo;
                if ($viaje_ref) {
                    $cnt = $pdo->prepare("SELECT COUNT(*) FROM viaje_envio WHERE cod_viaje = :cod");
                    $cnt->execute([':cod' => $viaje_ref['cod_viaje']]);
                    $viaje_paquetes = (int)$cnt->fetchColumn();
                }

                // Vehículos usados por este chofer (solo viajes finalizados)
                $sveh = $pdo->prepare("
                    SELECT v.patente, veh.modelo, tv.nombre AS tipo_veh,
                           tv.capacidad_kg_max, veh.estado,
                           MAX(v.fecha_llegada_real) AS ultimo_uso
                    FROM viaje v
                    LEFT JOIN vehiculo veh ON veh.patente = v.patente
                    LEFT JOIN tipovehiculo tv ON tv.id_tipo_veh = veh.id_tipo_veh
                    WHERE v.legajo_chofer = :leg AND v.cancelado = 0
                      AND v.fecha_llegada_real IS NOT NULL
                    GROUP BY v.patente, veh.modelo, tv.nombre, tv.capacidad_kg_max, veh.estado
                    ORDER BY ultimo_uso DESC
                    LIMIT 5
                ");
                $sveh->execute([':leg' => $chofer_legajo]);
                $chofer_vehiculos = $sveh->fetchAll(PDO::FETCH_ASSOC);

                // Incidentes abiertos del chofer
                $si = $pdo->prepare("
                    SELECT i.nro_incidente, i.descripcion, i.fecha_hora, i.cod_viaje, t.nombre AS tipo
                    FROM incidente i
                    JOIN viaje v ON v.cod_viaje = i.cod_viaje
                    LEFT JOIN tipoincidente t ON t.id_tipo_inc = i.id_tipo_inc
                    WHERE v.legajo_chofer = :leg AND i.estado = 'abierto'
                    ORDER BY i.fecha_hora DESC LIMIT 5
                ");
                $si->execute([':leg' => $chofer_legajo]);
                $chofer_incidentes = $si->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {}
        }

        require_once __DIR__ . '/../views/chofer/panel.php';
        break;

    default:
        session_destroy();
        header("Location: /admin/login.php");
        exit();
}
