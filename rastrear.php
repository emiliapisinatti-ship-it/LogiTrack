<?php
require_once __DIR__ . '/config/db.php';

$envio_publico  = null;
$historial_pub  = [];
$error_rastreo  = '';
$tracking_input = strtoupper(trim($_GET['q'] ?? ''));

if ($tracking_input) {
    try {
        $stmt = $pdo->prepare(
            "SELECT e.nro_tracking, e.fecha_creacion,
                    so.nombre AS suc_origen,
                    sd.nombre AS suc_destino,
                    es.nombre AS estado_actual
             FROM Envio e
             LEFT JOIN Sucursal      so ON so.id_sucursal = e.id_suc_origen
             LEFT JOIN Sucursal      sd ON sd.id_sucursal = e.id_suc_destino
             LEFT JOIN HistorialEstado h ON h.nro_tracking = e.nro_tracking
               AND h.fecha_hora = (SELECT MAX(h2.fecha_hora) FROM HistorialEstado h2 WHERE h2.nro_tracking = e.nro_tracking)
             LEFT JOIN EstadoEnvio   es ON es.id_estado = h.id_estado
             WHERE e.nro_tracking = :trk"
        );
        $stmt->execute([':trk' => $tracking_input]);
        $envio_publico = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($envio_publico) {
            $qh = $pdo->prepare(
                "SELECT h.fecha_hora, es.nombre AS estado, h.observacion
                 FROM HistorialEstado h
                 JOIN EstadoEnvio es ON es.id_estado = h.id_estado
                 WHERE h.nro_tracking = :trk
                 ORDER BY h.fecha_hora DESC"
            );
            $qh->execute([':trk' => $tracking_input]);
            $historial_pub = $qh->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_rastreo = 'No encontramos ningún envío con ese número de seguimiento.';
        }
    } catch (PDOException $e) {
        $error_rastreo = 'Error al consultar. Intentá de nuevo.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiTrack — Rastrear pedido</title>
    <link rel="stylesheet" href="/assets/estilo.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; min-height: 100vh; }

        .page-nav {
            background: #1e1b4b;
            padding: 0 40px;
            height: 56px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-nav .brand {
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
            color: #a5b4fc;
            text-decoration: none;
        }
        .page-nav .nav-links { display: flex; gap: 8px; }
        .page-nav .nav-links a {
            color: #c7d2fe;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .page-nav .nav-links a:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .page-nav .btn-nav {
            background: rgba(99,102,241,0.3);
            color: #c7d2fe !important;
            font-weight: 600 !important;
        }
        .page-nav .btn-nav:hover { background: var(--rose-dark) !important; color: #fff !important; }

        .page-wrap {
            max-width: 680px;
            margin: 52px auto;
            padding: 0 20px;
        }

        .page-header { text-align: center; margin-bottom: 36px; }
        .page-header h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 36px;
            color: #1e1b3a;
            margin-bottom: 8px;
        }
        .page-header p { color: #64748b; font-size: 15px; }

        /* Formulario */
        .track-form {
            display: flex;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: border-color 0.2s, box-shadow 0.2s;
            margin-bottom: 28px;
        }
        .track-form:focus-within {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79,70,229,0.10);
        }
        .track-form input {
            flex: 1;
            border: none;
            outline: none;
            padding: 16px 22px;
            font-size: 16px;
            font-family: 'DM Sans', sans-serif;
            color: #1e1b3a;
            letter-spacing: 0.5px;
            background: transparent;
        }
        .track-form input::placeholder { color: #94a3b8; }
        .track-form button {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 16px 28px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .track-form button:hover { background: #4338ca; }

        /* Error */
        .track-error {
            background: #fff1f1;
            border: 1.5px solid #fca5a5;
            color: #b91c1c;
            padding: 16px 22px;
            border-radius: 10px;
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Resultado */
        .track-result {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .trk-head {
            background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%);
            color: white;
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .trk-label { font-size: 11px; opacity: 0.7; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 5px; }
        .trk-num   { font-family: monospace; font-size: 20px; font-weight: 700; letter-spacing: 1.5px; }
        .trk-badge {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
            padding: 7px 18px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.25);
        }

        .trk-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid #f1f5f9;
        }
        .trk-info-cell {
            padding: 16px 28px;
            border-right: 1px solid #f1f5f9;
        }
        .trk-info-cell:nth-child(2n) { border-right: none; }
        .trk-info-cell:nth-child(n+3) { border-top: 1px solid #f1f5f9; }
        .trk-info-cell label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            display: block;
            margin-bottom: 4px;
        }
        .trk-info-cell span { font-size: 14px; font-weight: 500; color: #1e1b3a; }
        .trk-info-cell .estado-val { color: #4f46e5; font-weight: 700; }

        /* Historial */
        .trk-history { padding: 24px 28px; }
        .trk-history-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            margin-bottom: 16px;
        }
        .hist-line { display: flex; gap: 16px; padding: 10px 0; position: relative; }
        .hist-line:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 7px;
            top: 26px;
            bottom: -10px;
            width: 2px;
            background: #e2e8f0;
        }
        .hist-dot {
            width: 16px; height: 16px;
            border-radius: 50%;
            background: #e2e8f0;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e2e8f0;
            flex-shrink: 0;
            margin-top: 3px;
        }
        .hist-dot.current { background: #4f46e5; box-shadow: 0 0 0 2px #c7d2fe; }
        .hist-estado { font-size: 14px; font-weight: 600; color: #1e1b3a; }
        .hist-meta   { font-size: 12px; color: #94a3b8; margin-top: 2px; }
        .hist-obs    { font-size: 12px; color: #64748b; margin-top: 3px; font-style: italic; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            margin-top: 20px;
        }
        .back-link:hover { color: #4f46e5; }
    </style>
</head>
<body>

<nav class="page-nav">
    <a href="/index.php" class="brand">LogiTrack</a>
    <div class="nav-links">
        <a href="/index.php">← Inicio</a>
        <a href="/cliente/login.php" class="btn-nav">Ingresar</a>
    </div>
</nav>

<div class="page-wrap">

    <div class="page-header">
        <h1><i class="fa-solid fa-magnifying-glass" style="color:#4f46e5;font-size:28px;"></i><br>Rastrear pedido</h1>
        <p>Ingresá el número de seguimiento para ver el estado de tu envío.</p>
    </div>

    <form method="GET" action="/rastrear.php" class="track-form">
        <input type="text" name="q"
               value="<?= htmlspecialchars($tracking_input) ?>"
               placeholder="Número de seguimiento (ej: LT-20250628-0001)"
               maxlength="30"
               autocomplete="off"
               autofocus>
        <button type="submit"><i class="fa-solid fa-search"></i> Rastrear</button>
    </form>

    <?php if ($tracking_input && $error_rastreo): ?>
        <div class="track-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($error_rastreo) ?>
        </div>
    <?php endif; ?>

    <?php if ($envio_publico): ?>
    <div class="track-result">
        <div class="trk-head">
            <div>
                <div class="trk-label">Número de seguimiento</div>
                <div class="trk-num"><?= htmlspecialchars($envio_publico['nro_tracking']) ?></div>
            </div>
            <div class="trk-badge"><?= htmlspecialchars($envio_publico['estado_actual'] ?? 'Sin estado') ?></div>
        </div>

        <div class="trk-info">
            <div class="trk-info-cell">
                <label>Sucursal origen</label>
                <span><?= htmlspecialchars($envio_publico['suc_origen'] ?? '—') ?></span>
            </div>
            <div class="trk-info-cell">
                <label>Sucursal destino</label>
                <span><?= htmlspecialchars($envio_publico['suc_destino'] ?? '—') ?></span>
            </div>
            <div class="trk-info-cell">
                <label>Fecha de ingreso</label>
                <span><?= $envio_publico['fecha_creacion'] ? date('d/m/Y', strtotime($envio_publico['fecha_creacion'])) : '—' ?></span>
            </div>
            <div class="trk-info-cell">
                <label>Estado actual</label>
                <span class="estado-val"><?= htmlspecialchars($envio_publico['estado_actual'] ?? 'Sin estado') ?></span>
            </div>
        </div>

        <?php if (!empty($historial_pub)): ?>
        <div class="trk-history">
            <div class="trk-history-title">Historial de estados</div>
            <?php foreach ($historial_pub as $i => $h): ?>
            <div class="hist-line">
                <div class="hist-dot <?= $i === 0 ? 'current' : '' ?>"></div>
                <div>
                    <div class="hist-estado"><?= htmlspecialchars($h['estado']) ?></div>
                    <div class="hist-meta"><?= date('d/m/Y \a \l\a\s H:i', strtotime($h['fecha_hora'])) ?></div>
                    <?php if (!empty($h['observacion'])): ?>
                        <div class="hist-obs"><?= htmlspecialchars($h['observacion']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <a href="/rastrear.php" class="back-link"><i class="fa-solid fa-rotate-left"></i> Rastrear otro pedido</a>
    <?php endif; ?>

    <?php if (!$tracking_input): ?>
    <div style="text-align:center;margin-top:48px;color:#94a3b8;">
        <i class="fa-solid fa-box" style="font-size:40px;display:block;margin-bottom:12px;opacity:0.3;"></i>
        <p style="font-size:14px;">Ingresá el código de seguimiento que recibiste<br>al momento de registrar tu envío.</p>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
