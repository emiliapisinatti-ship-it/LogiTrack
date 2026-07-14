<?php
// vars: $envio, $historial, $paquete, $error
$page_subtitle = 'Rastrear Pedido';
$nav_links = [['href' => '/cliente/index.php', 'label' => '← Panel']];
$extra_css = '
    .search-box { background: var(--white); border-radius: 20px; padding: 32px; margin-bottom: 24px; text-align: center; border: 1px solid var(--border); box-shadow: var(--shadow); }
    .search-box h2 { font-family: "DM Serif Display", serif; font-size: 22px; margin-bottom: 6px; color: var(--text); }
    .search-box p { color: var(--text-soft); font-size: 13px; margin-bottom: 22px; }
    .search-row { display: flex; gap: 10px; max-width: 460px; margin: 0 auto; }
    .search-row input { flex: 1; padding: 12px 16px; background: var(--nude); border: 1.5px solid var(--border); border-radius: 12px; color: var(--text); font-size: 14px; font-family: "DM Sans", sans-serif; outline: none; text-transform: uppercase; letter-spacing: 1px; }
    .search-row input:focus { border-color: var(--rose); background: var(--white); }
    .search-row input::placeholder { text-transform: none; letter-spacing: 0; color: #a0a8c0; }
    .search-row button { padding: 12px 20px; background: var(--rose-dark); color: white; border: none; border-radius: 12px; font-size: 14px; font-weight: 600; font-family: "DM Sans", sans-serif; cursor: pointer; transition: background 0.2s; }
    .search-row button:hover { background: #4338ca; }
    .result-card { background: var(--white); border-radius: 20px; padding: 28px; margin-bottom: 20px; border: 1px solid var(--border); box-shadow: var(--shadow); }
    .result-card h3 { font-size: 15px; font-weight: 600; margin-bottom: 16px; color: var(--rose-dark); }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
    <div class="search-box">
        <h2>🔍 Rastrear mi envío</h2>
        <p>Ingresá el número de tracking que recibiste al hacer tu envío</p>
        <form method="post">
            <div class="search-row">
                <input type="text" name="tracking" placeholder="Ej: A1B2C3D4E5"
                    value="<?= htmlspecialchars($_POST['tracking'] ?? $_GET['tracking'] ?? '') ?>">
                <button type="submit">Buscar</button>
            </div>
        </form>
        <?php if ($error): ?>
            <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>

    <?php if ($envio): ?>

    <!-- Info del envío -->
    <div class="result-card">
        <h3>📦 Información del envío</h3>
        <div class="tracking-badge"><?= htmlspecialchars($envio['nro_tracking']) ?></div>
        <div class="info-grid">
            <div class="info-item">
                <label>Remitente</label>
                <span><?= htmlspecialchars($envio['nombre_remitente'] . ' ' . $envio['apellido_remitente']) ?></span>
            </div>
            <div class="info-item">
                <label>Destinatario</label>
                <span><?= htmlspecialchars($envio['nombre_dest'] . ' ' . $envio['apellido_dest']) ?></span>
            </div>
            <div class="info-item">
                <label>Sucursal origen</label>
                <span><?= htmlspecialchars($envio['suc_origen']) ?></span>
            </div>
            <div class="info-item">
                <label>Sucursal destino</label>
                <span><?= htmlspecialchars($envio['suc_destino'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Dirección de entrega</label>
                <span><?= htmlspecialchars($envio['direccion_entrega'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Fecha de creación</label>
                <span><?= date('d/m/Y H:i', strtotime($envio['fecha_creacion'])) ?></span>
            </div>
        </div>
    </div>

    <!-- Info de paquetes -->
    <?php if (!empty($paquetes)): ?>
    <div class="result-card">
        <h3>📐 Paquete<?= count($paquetes) > 1 ? 's (' . count($paquetes) . ')' : '' ?></h3>
        <?php foreach ($paquetes as $i => $paquete): ?>
            <?php if (count($paquetes) > 1): ?>
            <p style="font-size:13px;font-weight:600;color:var(--text-soft);margin:<?= $i > 0 ? '16px' : '0' ?> 0 8px;">
                Paquete #<?= $i + 1 ?>
            </p>
            <?php endif; ?>
            <div class="info-grid">
                <div class="info-item">
                    <label>Peso</label>
                    <span><?= htmlspecialchars($paquete['peso_kg']) ?> kg</span>
                </div>
                <div class="info-item">
                    <label>Tipo de contenido</label>
                    <span><?= htmlspecialchars($paquete['tipo'] ?? 'No especificado') ?></span>
                </div>
                <div class="info-item">
                    <label>Dimensiones</label>
                    <span><?= htmlspecialchars($paquete['alto_cm']) ?>×<?= htmlspecialchars($paquete['ancho_cm']) ?>×<?= htmlspecialchars($paquete['largo_cm']) ?> cm</span>
                </div>
                <div class="info-item">
                    <label>Descripción</label>
                    <span><?= htmlspecialchars($paquete['descripcion'] ?? '-') ?></span>
                </div>
            </div>
            <?php if ($i < count($paquetes) - 1): ?>
            <hr style="border:none;border-top:1px solid var(--border);margin:12px 0;">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Historial de estados -->
    <div class="result-card">
        <h3>📋 Historial de estados</h3>
        <?php if (!empty($historial)): ?>
        <div class="timeline">
            <?php foreach ($historial as $i => $h): ?>
            <div class="timeline-item" <?= $i === 0 ? 'style="background:var(--nude);border-radius:12px;padding:10px 14px;border-left:3px solid var(--rose-dark);"' : '' ?>>
                <div class="timeline-estado">
                    <?= htmlspecialchars($h['estado']) ?>
                    <?php if ($i === 0): ?>
                        <span style="display:inline-block;font-size:10px;font-weight:700;background:var(--rose-dark);color:white;border-radius:8px;padding:1px 7px;margin-left:6px;text-transform:uppercase;letter-spacing:0.5px;">actual</span>
                    <?php endif; ?>
                </div>
                <div class="timeline-fecha"><?= date('d/m/Y H:i', strtotime($h['fecha_hora'])) ?></div>
                <?php if ($h['observacion']): ?>
                <div class="timeline-obs"><?= htmlspecialchars($h['observacion']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-timeline">Sin movimientos registrados aún.</div>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
