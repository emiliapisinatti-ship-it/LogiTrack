<?php
$page_subtitle = 'Incidentes';
$nav_links = [['href' => '/admin/index.php', 'label' => '← Panel']];
$extra_css = '
    body { background:linear-gradient(145deg,#f0f0ff 0%,#eef2ff 100%); min-height:100vh; }
    .lt-container { max-width:900px; }
    table { width:100%; border-collapse:collapse; font-size:14px; }
    thead th { text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-soft); padding:10px 14px; border-bottom:1.5px solid var(--border); }
    tbody tr { border-bottom:1px solid var(--nude-dark); }
    tbody tr:hover { background:var(--nude); }
    tbody td { padding:11px 14px; vertical-align:middle; }
    .cod-mono { font-family:monospace; font-weight:700; font-size:13px; color:var(--rose-dark); }
    .badge-tipo { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; background:#fef6e4; color:#c89040; }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;">
                Incidentes
                <?php if ($filtro_estado): ?>
                    <span style="font-size:13px;font-weight:600;padding:3px 10px;border-radius:10px;background:#fef6e4;color:#c89040;margin-left:8px;vertical-align:middle;">
                        <?= $filtro_estado === 'abierto' ? 'Abiertos' : 'Resueltos' ?>
                    </span>
                <?php endif; ?>
            </div>
            <div style="color:var(--text-soft);font-size:13px;margin-top:3px;"><?= count($incidentes) ?> registrado<?= count($incidentes) != 1 ? 's' : '' ?></div>
        </div>
        <a href="/admin/router.php?pagina=crear_incidente" class="btn-sm" style="border-color:var(--rose);color:var(--rose-dark);padding:8px 16px;border-radius:9px;border:1.5px solid;font-weight:600;text-decoration:none;">+ Reportar incidente</a>
    </div>

    <form method="get" action="/admin/router.php" style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap;">
        <input type="hidden" name="pagina" value="incidentes">
        <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>"
               placeholder="Buscar por viaje, tipo o descripción..."
               style="flex:1;min-width:180px;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-family:inherit;">
        <select name="estado" style="padding:10px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-family:inherit;background:#fff;">
            <option value="">Todos los estados</option>
            <option value="abierto"  <?= $filtro_estado === 'abierto'  ? 'selected' : '' ?>>Abiertos</option>
            <option value="cerrado" <?= $filtro_estado === 'cerrado' ? 'selected' : '' ?>>Resueltos</option>
        </select>
        <button type="submit" style="padding:10px 20px;background:var(--rose-dark);color:#fff;border:none;border-radius:10px;font-weight:600;font-family:inherit;cursor:pointer;">Filtrar</button>
        <?php if ($busqueda || $filtro_estado): ?>
            <a href="/admin/router.php?pagina=incidentes"
               style="padding:10px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;color:var(--text-soft);text-decoration:none;">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="lt-card" style="padding:0;overflow:hidden;">
        <?php if (empty($incidentes)): ?>
            <div style="text-align:center;padding:48px;color:var(--text-soft);">
                <div style="font-size:40px;margin-bottom:10px;">&#9989;</div>
                <em>Sin incidentes registrados.</em>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>#</th><th>Viaje</th><th>Patente</th><th>Tipo</th><th>Descripcion</th><th>Fecha</th><th>Estado</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($incidentes as $inc): ?>
                <tr>
                    <td><span class="cod-mono"><?= $inc['nro_incidente'] ?></span></td>
                    <td><span class="cod-mono"><?= htmlspecialchars($inc['cod_viaje']) ?></span></td>
                    <td style="font-family:monospace;"><?= htmlspecialchars($inc['patente'] ?? '-') ?></td>
                    <td><span class="badge-tipo">&#9888; <?= htmlspecialchars($inc['tipo'] ?? '-') ?></span></td>
                    <td style="max-width:280px;font-size:13px;"><?= htmlspecialchars(mb_strimwidth($inc['descripcion'] ?? '', 0, 80, '...')) ?></td>
                    <td style="font-size:13px;color:var(--text-soft);white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($inc['fecha_hora'])) ?></td>
                    <td>
                        <?php if (($inc['estado'] ?? 'abierto') === 'abierto'): ?>
                            <?php if (in_array($_SESSION['id_rol'], [1, 2])): ?>
                                <form method="POST" style="margin:0;display:flex;flex-direction:column;gap:4px;">
                                    <input type="hidden" name="cerrar" value="<?= $inc['nro_incidente'] ?>">
                                    <input type="text" name="obs_resolucion" placeholder="Nota de resolución (opcional)"
                                           style="padding:3px 7px;border:1.5px solid var(--border);border-radius:6px;font-size:11px;min-width:160px;">
                                    <button type="submit" style="padding:4px 10px;border-radius:8px;font-size:11px;font-weight:600;border:1.5px solid #a0d0b0;background:#e8f8ee;color:#3a6050;cursor:pointer;">&#10003; Cerrar incidente</button>
                                </form>
                            <?php else: ?>
                                <span style="font-size:11px;font-weight:600;color:#c89040;">&#9679; Abierto</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="font-size:11px;font-weight:600;color:#3a9060;">&#10003; Resuelto</div>
                            <?php if (!empty($inc['fecha_resolucion'])): ?>
                            <div style="font-size:11px;color:#94a3b8;margin-top:2px;">
                                <?= date('d/m/Y H:i', strtotime($inc['fecha_resolucion'])) ?>
                                <?php if (!empty($inc['username_resolucion'])): ?>
                                · <?= htmlspecialchars($inc['username_resolucion']) ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td style="display:flex;gap:5px;white-space:nowrap;">
                        <?php if (in_array($_SESSION['id_rol'], [1, 2])): ?>
                        <a href="/admin/router.php?pagina=editar_incidente&id=<?= $inc['nro_incidente'] ?>"
                           style="padding:4px 10px;border-radius:8px;font-size:11px;font-weight:600;border:1.5px solid var(--border);color:var(--text-soft);text-decoration:none;">&#9998;</a>
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
