<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireAuth([1,2,3,4], '/cliente/login.php');
$rol = $_SESSION['id_rol'];
if ($rol == 4) {
    // DNI del cliente
    $stmt = $pdo->prepare("SELECT dni_cliente FROM usuario WHERE id_usuario = :id");
    $stmt->execute([':id' => $_SESSION['id_usuario']]);
    $dni_cliente = $stmt->fetchColumn() ?: null;

    $cli_activos = $cli_entregados = $cli_total = 0;
    $cli_recientes = [];

    if ($dni_cliente) {
        // Conteos por estado actual
        $sc = $pdo->prepare("
            SELECT es.nombre AS estado, COUNT(*) AS qty
            FROM envio e
            LEFT JOIN historialestado h ON h.id_hist = (SELECT id_hist FROM historialestado WHERE nro_tracking = e.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1)
            LEFT JOIN estadoenvio es ON es.id_estado = h.id_estado
            WHERE e.dni_remitente = :dni OR e.dni_destinatario = :dni2
            GROUP BY es.nombre
        ");
        $sc->execute([':dni' => $dni_cliente, ':dni2' => $dni_cliente]);
        foreach ($sc->fetchAll() as $row) {
            $cli_total += $row['qty'];
            $nombre = strtolower($row['estado'] ?? '');
            if (str_contains($nombre, 'viaje') || str_contains($nombre, 'tránsito') || str_contains($nombre, 'deposito') || str_contains($nombre, 'depósito')) {
                $cli_activos += $row['qty'];
            } elseif (str_contains($nombre, 'entregado')) {
                $cli_entregados += $row['qty'];
            }
        }

        // Últimos 3 envíos
        $sr = $pdo->prepare("
            SELECT e.nro_tracking, e.fecha_creacion,
                   sd.nombre AS suc_destino,
                   es.nombre AS estado_actual
            FROM envio e
            LEFT JOIN sucursal sd ON sd.id_sucursal = e.id_suc_destino
            LEFT JOIN historialestado h ON h.id_hist = (SELECT id_hist FROM historialestado WHERE nro_tracking = e.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1)
            LEFT JOIN estadoenvio es ON es.id_estado = h.id_estado
            WHERE e.dni_remitente = :dni OR e.dni_destinatario = :dni2
            ORDER BY e.fecha_creacion DESC
            LIMIT 3
        ");
        $sr->execute([':dni' => $dni_cliente, ':dni2' => $dni_cliente]);
        $cli_recientes = $sr->fetchAll();
    }

    require_once __DIR__ . '/../views/cliente/panel.php';
} else {
    header("Location: /admin/index.php"); exit();
}
