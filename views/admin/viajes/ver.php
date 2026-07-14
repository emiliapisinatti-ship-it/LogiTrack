<?php
// vars: $viaje, $envios, $model, $cod
$page_subtitle = 'Detalle Viaje';
$nav_links = [['href' => '/admin/router.php?pagina=viajes', 'label' => '← Viajes']];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 900px; }
    .viaje-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
    .cod-grande { font-family: monospace; font-size: 20px; font-weight: 700; color: var(--rose-dark); background: var(--nude); padding: 6px 14px; border-radius: 8px; }
    .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .info-item label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-soft); margin-bottom: 3px; }
    .info-item span { font-size: 14px; color: var(--text); font-weight: 500; }
    .badge-viaje { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .badge-pendiente  { background: #fef6e4; color: #c89040; }
    .badge-encurso    { background: #e8effe; color: #5b7bd6; }
    .badge-completado { background: #e8f8ee; color: #3a9060; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    thead th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-soft); padding: 10px 14px; border-bottom: 1.5px solid var(--border); }
    tbody tr { border-bottom: 1px solid var(--nude-dark); transition: background 0.15s; }
    tbody tr:hover { background: var(--nude); }
    tbody td { padding: 12px 14px; vertical-align: middle; }
    .tracking-mono { font-family: monospace; font-weight: 600; font-size: 13px; color: var(--rose-dark); }
    .badge-estado { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: var(--nude-dark); color: var(--text-soft); }
    .badge-entregado { background: #e8f8ee; color: #3a9060; }
    .badge-viaje-e   { background: #e8effe; color: #5b7bd6; }
    .btn-sm { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1.5px solid var(--border); background: var(--white); color: var(--text-soft); text-decoration: none; transition: all 0.2s; }
    .btn-sm:hover { border-color: var(--rose); color: var(--rose-dark); }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">

    <!-- Datos del viaje -->
    <div class="lt-card" style="margin-bottom:20px;">
        <div class="viaje-header">
            <div>
                <div style="font-size:12px;color:var(--text-soft);margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:0.6px;">Viaje</div>
                <span class="cod-grande"><?= htmlspecialchars($viaje['cod_viaje']) ?></span>
            </div>
            <span class="badge-viaje <?= $badgeClass ?>" style="font-size:13px;padding:6px 16px;">
                <?= match($estado) { 'Pendiente' => '⏳', 'En curso' => '🚛', 'Completado' => '✅', default => '' } ?>
                <?= $estado ?>
            </span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Chofer</label>
                <span><?= htmlspecialchars($viaje['apellido_chofer'] . ', ' . $viaje['nombre_chofer']) ?></span>
            </div>
            <div class="info-item">
                <label>Patente</label>
                <span style="font-family:monospace;font-weight:700;"><?= htmlspecialchars($viaje['patente']) ?></span>
            </div>
            <div class="info-item">
                <label>Sucursal origen</label>
                <span><?= htmlspecialchars($viaje['suc_origen'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Sucursal destino</label>
                <span><?= htmlspecialchars($viaje['suc_destino'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Fecha de salida</label>
                <span><?= date('d/m/Y H:i', strtotime($viaje['fecha_salida'])) ?></span>
            </div>
            <div class="info-item">
                <label>Llegada estimada</label>
                <?php if ($estado !== 'Completado'): ?>
                <form method="POST" style="margin:0;display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                    <input type="hidden" name="cod_viaje" value="<?= htmlspecialchars($viaje['cod_viaje']) ?>">
                    <input type="datetime-local" name="fecha_llegada_est"
                           value="<?= date('Y-m-d\TH:i', strtotime($viaje['fecha_llegada_est'])) ?>"
                           style="padding:4px 7px;border:1.5px solid var(--border);border-radius:7px;font-size:13px;">
                    <button type="submit" name="modificar_fecha" value="1" class="btn-sm" title="Guardar nueva fecha">💾 Guardar</button>
                </form>
                <?php else: ?>
                <span><?= date('d/m/Y H:i', strtotime($viaje['fecha_llegada_est'])) ?></span>
                <?php endif; ?>
            </div>
            <div class="info-item">
                <label>Llegada real</label>
                <span>
                    <?= $viaje['fecha_llegada_real']
                        ? date('d/m/Y H:i', strtotime($viaje['fecha_llegada_real']))
                        : '<em style="color:var(--text-soft);">En tránsito</em>' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Envíos del viaje -->
    <div class="lt-card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--nude-dark);display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-family:\'DM Serif Display\',serif;font-size:18px;color:var(--text);">Envíos del viaje</div>
                <div style="font-size:13px;color:var(--text-soft);margin-top:2px;"><?= count($envios) ?> paquete<?= count($envios) != 1 ? 's' : '' ?></div>
            </div>
        </div>

        <?php if (empty($envios)): ?>
            <div style="text-align:center;padding:40px;color:var(--text-soft);">
                <div style="font-size:36px;margin-bottom:8px;">📦</div>
                <em>Sin envíos registrados para este viaje.</em>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Tracking</th>
                    <th>Destinatario</th>
                    <th>Destino</th>
                    <th>Estado actual</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($envios as $e):
                    $estadoEnvio = strtolower($e['estado_actual'] ?? '');
                    $badgeEnvio  = str_contains($estadoEnvio, 'entregado') ? 'badge-entregado'
                                 : (str_contains($estadoEnvio, 'viaje') ? 'badge-viaje-e' : '');
                ?>
                <tr>
                    <td><span class="tracking-mono"><?= htmlspecialchars($e['nro_tracking']) ?></span></td>
                    <td><?= htmlspecialchars(($e['apellido_dest'] ?? '') . ', ' . ($e['nombre_dest'] ?? '')) ?></td>
                    <td>
                        <?php if ($e['suc_destino']): ?>
                            🏢 <?= htmlspecialchars($e['suc_destino']) ?>
                        <?php elseif ($e['direccion_entrega']): ?>
                            🏠 <?= htmlspecialchars($e['direccion_entrega']) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge-estado <?= $badgeEnvio ?>">
                            <?= htmlspecialchars($e['estado_actual'] ?? 'Sin estado') ?>
                        </span>
                    </td>
                    <td>
                        <?php if (empty($viaje['fecha_llegada_real'])): ?>
                        <a href="/admin/router.php?pagina=gestionar_envios&tracking=<?= urlencode($e['nro_tracking']) ?>" class="btn-sm">
                            Actualizar estado →
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
