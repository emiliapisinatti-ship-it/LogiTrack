<?php
// vars: $viajes, $legajo_chofer, $model
$page_subtitle = 'Mis Viajes';
$nav_links = [['href' => '/admin/index.php', 'label' => '← Panel']];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 900px; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    thead th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-soft); padding: 10px 14px; border-bottom: 1.5px solid var(--border); }
    tbody tr { border-bottom: 1px solid var(--nude-dark); transition: background 0.15s; }
    tbody tr:hover { background: var(--nude); }
    tbody td { padding: 12px 14px; vertical-align: middle; }
    .cod-viaje { font-family: monospace; font-weight: 700; font-size: 13px; color: var(--rose-dark); }
    .badge-viaje { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .badge-pendiente  { background: #fef6e4; color: #c89040; }
    .badge-encurso    { background: #e8effe; color: #5b7bd6; }
    .badge-completado { background: #e8f8ee; color: #3a9060; }
    .btn-sm { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1.5px solid var(--border); background: var(--white); color: var(--text-soft); text-decoration: none; transition: all 0.2s; }
    .btn-sm:hover { border-color: var(--rose); color: var(--rose-dark); }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
    <div style="margin-bottom:20px;">
        <div style="font-family:\'DM Serif Display\',serif;font-size:22px;color:var(--text);">Mis viajes asignados</div>
        <div style="color:var(--text-soft);font-size:13px;margin-top:3px;">
            Legajo: <strong><?= htmlspecialchars($legajo_chofer) ?></strong>
            · <?= count($viajes) ?> viaje<?= count($viajes) != 1 ? 's' : '' ?> en total
        </div>
    </div>

    <div class="lt-card" style="padding:0;overflow:hidden;">
        <?php if (empty($viajes)): ?>
            <div style="text-align:center;padding:48px;color:var(--text-soft);">
                <div style="font-size:40px;margin-bottom:10px;">🚛</div>
                <em>No tenés viajes asignados por el momento.</em>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Salida</th>
                    <th>Llegada estimada</th>
                    <th>Patente</th>
                    <th>Sucursal origen</th>
                    <th>Envíos</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($viajes as $v):
                    $estado = $manager->estadoViaje($v);
                    $badgeClass = match($estado) {
                        'Pendiente'  => 'badge-pendiente',
                        'En curso'   => 'badge-encurso',
                        'Completado' => 'badge-completado',
                        default      => ''
                    };
                    $icono = match($estado) {
                        'Pendiente'  => '⏳',
                        'En curso'   => '🚛',
                        'Completado' => '✅',
                        default      => ''
                    };
                ?>
                <tr>
                    <td><span class="cod-viaje"><?= htmlspecialchars($v['cod_viaje']) ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['fecha_salida'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['fecha_llegada_est'])) ?></td>
                    <td style="font-family:monospace;font-weight:600;"><?= htmlspecialchars($v['patente']) ?></td>
                    <td><?= htmlspecialchars($v['suc_origen'] ?? '—') ?></td>
                    <td style="text-align:center;font-weight:600;"><?= $v['total_envios'] ?></td>
                    <td><span class="badge-viaje <?= $badgeClass ?>"><?= $icono ?> <?= $estado ?></span></td>
                    <td>
                        <a href="/admin/router.php?pagina=ver_viaje&cod=<?= urlencode($v['cod_viaje']) ?>" class="btn-sm">Ver envíos →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
