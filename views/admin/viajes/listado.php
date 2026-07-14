<?php
// vars: $viajes, $busqueda, $model
$page_subtitle = 'Viajes';
$nav_links = [['href' => '/admin/index.php', 'label' => '← Panel']];
if ($_SESSION['id_rol'] == 1) {
    $nav_links[] = ['href' => '/admin/router.php?pagina=crear_viaje', 'label' => '+ Nuevo viaje', 'class' => 'btn-logout'];
}
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 1000px; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    thead th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase;
               letter-spacing: 0.8px; color: var(--text-soft); padding: 10px 14px;
               border-bottom: 1.5px solid var(--border); }
    tbody tr { border-bottom: 1px solid var(--nude-dark); transition: background 0.15s; }
    tbody tr:hover { background: var(--nude); }
    tbody td { padding: 12px 14px; vertical-align: middle; }
    .cod-viaje { font-family: monospace; font-weight: 700; font-size: 13px; color: var(--rose-dark); }
    .badge-viaje { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .badge-pendiente  { background: #fef6e4; color: #c89040; }
    .badge-encurso    { background: #e8effe; color: #5b7bd6; }
    .badge-completado { background: #e8f8ee; color: #3a9060; }
    .btn-sm { padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;
              border: 1.5px solid var(--border); background: var(--white); color: var(--text-soft);
              text-decoration: none; transition: all 0.2s; cursor: pointer; }
    .btn-sm:hover { border-color: var(--rose); color: var(--rose-dark); }
    .btn-completar { border-color: #3a9060; color: #3a9060; }
    .btn-completar:hover { background: #3a9060; color: #fff; }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);">
                Viajes
                <?php
                $labels_estado = ['en_curso'=>'En curso','pendiente'=>'Pendientes','completado'=>'Completados'];
                if ($filtro_estado && isset($labels_estado[$filtro_estado])): ?>
                    <span style="font-size:13px;font-weight:600;padding:3px 10px;border-radius:10px;background:#e8effe;color:#5b7bd6;margin-left:8px;vertical-align:middle;">
                        <?= $labels_estado[$filtro_estado] ?>
                    </span>
                <?php endif; ?>
            </div>
            <div style="color:var(--text-soft);font-size:13px;margin-top:3px;">
                <?= count($viajes) ?> viaje<?= count($viajes) != 1 ? 's' : '' ?>
            </div>
            <?php $qs_filtros = ($busqueda ? '&q=' . urlencode($busqueda) : '') . ($filtro_estado ? '&estado=' . urlencode($filtro_estado) : ''); ?>
        </div>
        <?php if ($_SESSION['id_rol'] == 1): ?>
        <a href="/admin/router.php?pagina=crear_viaje" class="btn-sm" style="border-color:var(--rose);color:var(--rose-dark);">+ Nuevo viaje</a>
        <?php elseif ($_SESSION['id_rol'] == 2): ?>
        <a href="/admin/router.php?pagina=despacho" class="btn-sm" style="border-color:var(--rose);color:var(--rose-dark);">+ Despacho</a>
        <?php endif; ?>
    </div>

    <form method="get" action="/admin/router.php" style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;">
        <input type="hidden" name="pagina" value="viajes">
        <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>"
               placeholder="Buscar por patente, legajo o código..."
               style="flex:1;min-width:160px;padding:9px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-family:inherit;">
        <select name="estado" style="padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-family:inherit;background:#fff;">
            <option value="">Todos los estados</option>
            <option value="en_curso"   <?= $filtro_estado === 'en_curso'   ? 'selected' : '' ?>>En curso</option>
            <option value="pendiente"  <?= $filtro_estado === 'pendiente'  ? 'selected' : '' ?>>Pendientes</option>
            <option value="completado" <?= $filtro_estado === 'completado' ? 'selected' : '' ?>>Completados</option>
        </select>
        <button type="submit" style="padding:9px 20px;background:var(--rose-dark);color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;">Filtrar</button>
        <?php if ($busqueda || $filtro_estado): ?>
            <a href="/admin/router.php?pagina=viajes" style="padding:9px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;color:var(--text-soft);text-decoration:none;">Limpiar</a>
        <?php endif; ?>
    </form>
    <div class="lt-card" style="padding:0;overflow:hidden;">
        <?php if (empty($viajes)): ?>
            <div style="text-align:center;padding:48px;color:var(--text-soft);">
                <div style="font-size:40px;margin-bottom:10px;">🚛</div>
                <em>No hay viajes registrados.</em><br>
                <a href="/admin/router.php?pagina=crear_viaje" class="btn-sm" style="margin-top:14px;display:inline-block;">+ Crear primer viaje</a>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Chofer</th>
                    <th>Patente</th>
                    <th>Origen → Destino</th>
                    <th>Salida</th>
                    <th>Llegada est.</th>
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
                    <td><?= htmlspecialchars(!empty($v['nombre_chofer']) ? $v['apellido_chofer'].', '.$v['nombre_chofer'] : $v['legajo_chofer']) ?></td>
                    <td style="font-family:monospace;font-weight:600;"><?= htmlspecialchars($v['patente']) ?></td>
                    <td>
                        <?= htmlspecialchars($v['suc_origen'] ?? '—') ?>
                        <?php if (!empty($v['suc_destino'])): ?>
                        <span style="color:#94a3b8;"> → </span><?= htmlspecialchars($v['suc_destino']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($v['fecha_salida'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['fecha_llegada_est'])) ?></td>
                    <td style="text-align:center;font-weight:600;"><?= $v['total_envios'] ?></td>
                    <td><span class="badge-viaje <?= $badgeClass ?>"><?= $icono ?> <?= $estado ?></span></td>
                    <td style="display:flex;gap:4px;align-items:center;white-space:nowrap;">
                        <a href="/admin/router.php?pagina=ver_viaje&cod=<?= urlencode($v['cod_viaje']) ?>" class="btn-sm" title="Ver detalle">&#128065;</a>

                        <?php if ($estado !== 'Completado'): ?>
                        <a href="/admin/router.php?pagina=editar_viaje&cod=<?= urlencode($v['cod_viaje']) ?>" class="btn-sm" title="Editar viaje">&#9998;</a>
                        <?php endif; ?>

                        <?php if ($estado === 'En curso'): ?>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="completar" value="<?= htmlspecialchars($v['cod_viaje']) ?>">
                            <button type="submit" class="btn-sm btn-completar" title="Completar viaje">&#10003;</button>
                        </form>
                        <?php endif; ?>

                        <?php if ($estado !== 'Completado'): ?>
                            <?php if (($_GET['confirmar_cancelar'] ?? '') === $v['cod_viaje']): ?>
                            <form method="POST" style="margin:0;display:flex;gap:6px;align-items:center;">
                                <input type="hidden" name="cancelar" value="<?= htmlspecialchars($v['cod_viaje']) ?>">
                                <span style="font-size:12px;color:var(--text-soft);white-space:nowrap;">¿Cancelar?</span>
                                <button type="submit" class="btn-sm btn-danger">Sí</button>
                                <a href="/admin/router.php?pagina=viajes<?= $qs_filtros ?>" class="btn-sm">No</a>
                            </form>
                            <?php else: ?>
                            <a href="/admin/router.php?pagina=viajes&confirmar_cancelar=<?= urlencode($v['cod_viaje']) . $qs_filtros ?>"
                               class="btn-sm btn-danger" title="Cancelar viaje">&#128465;</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        