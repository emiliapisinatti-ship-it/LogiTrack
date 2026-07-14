<?php
// vars: $envios, $sucursales, $estados, $filtro_sucursal, $filtro_estado, $nombre_sucursal, $model
$page_subtitle = 'Listado de Envios';
$nav_links = [
    ['href' => '/admin/index.php',            'label' => '← Panel'],
    ['href' => '/admin/router.php?pagina=gestionar_envios', 'label' => '🔄 Cambiar estado'],
];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 1000px; }
    .filtros-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; align-items: flex-end; }
    .filtros-bar .form-group { margin-bottom: 0; flex: 1; min-width: 180px; }
    .filtros-bar label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-soft); display: block; margin-bottom: 5px; }
    .filtros-bar select { width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 10px; font-family: "DM Sans", sans-serif; font-size: 13px; background: var(--white); color: var(--text); }
    .filtros-bar button { padding: 9px 20px; background: var(--rose-dark); color: white; border: none; border-radius: 10px; font-weight: 600; font-family: "DM Sans", sans-serif; cursor: pointer; white-space: nowrap; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    thead th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-soft); padding: 10px 14px; border-bottom: 1.5px solid var(--border); }
    tbody tr { border-bottom: 1px solid var(--nude-dark); transition: background 0.15s; }
    tbody tr:hover { background: var(--nude); }
    tbody td { padding: 12px 14px; vertical-align: middle; }
    .tracking-mono { font-family: monospace; font-weight: 600; font-size: 13px; color: var(--rose-dark); }
    .badge-estado { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: var(--nude-dark); color: var(--text-soft); }
    .badge-entregado { background: #e8f8ee; color: #3a9060; }
    .badge-viaje     { background: #e8effe; color: #5b7bd6; }
    .badge-devuelto  { background: #ede9fe; color: #6366a0; }
    .destino-icon { font-size: 13px; }
    .btn-sm { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1.5px solid var(--border); background: var(--white); color: var(--text-soft); text-decoration: none; transition: all 0.2s; white-space: nowrap; }
    .btn-sm:hover { border-color: var(--rose); color: var(--rose-dark); }
    .resumen { display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
    .resumen-chip { padding: 6px 14px; border-radius: 20px; background: var(--white); border: 1.5px solid var(--border); font-size: 13px; font-weight: 600; color: var(--text-soft); }
    .resumen-chip span { color: var(--rose-dark); }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">

    <div style="margin-bottom:20px;">
        <div style="font-family:\'DM Serif Display\',serif;font-size:22px;color:var(--text);">
            Envíos
            <?php if ($filtro_sucursal): ?>
                — <?= htmlspecialchars($nombre_sucursal) ?>
            <?php endif; ?>
        </div>
        <div style="color:var(--text-soft);font-size:13px;margin-top:3px;">
            <?= count($envios) ?> envío<?= count($envios) != 1 ? 's' : '' ?> encontrado<?= count($envios) != 1 ? 's' : '' ?>
        </div>
    </div>

    <?php if (isset($_GET['error_anular']) && $_SESSION['id_rol'] == 1): ?>
    <div class="lt-card" style="border:1.5px solid #f5a0a0;margin-bottom:20px;background:#fde8e8;">
        <div style="font-weight:700;color:#b03030;margin-bottom:4px;">&#9888; No se puede anular</div>
        <div style="font-size:13px;color:#b03030;">
            El envío <strong><?= htmlspecialchars($_GET['error_anular']) ?></strong> ya fue despachado.
            Solo se pueden anular envíos en <strong>Depósito Origen</strong>. Si fue despachado, debe ser devuelto primero.
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['confirmar_anular']) && $_SESSION['id_rol'] == 1): ?>
    <div class="lt-card" style="border:1.5px solid #f5a0a0;margin-bottom:20px;">
        <div style="font-weight:700;color:#b03030;margin-bottom:6px;">&#9888; Confirmar anulación</div>
        <div style="font-size:13px;margin-bottom:14px;">
            ¿Anular el envío <strong><?= htmlspecialchars($_GET['confirmar_anular']) ?></strong>?
            Esta acción no puede revertirse.
        </div>
        <form method="POST" style="display:flex;gap:10px;">
            <input type="hidden" name="anular" value="<?= htmlspecialchars($_GET['confirmar_anular']) ?>">
            <button type="submit" style="padding:8px 16px;background:#b03030;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Sí, anular</button>
            <a href="/admin/router.php?pagina=envios" class="btn-sm">Cancelar</a>
        </form>
    </div>
    <?php endif; ?>

    <!-- Filtro saliente/entrante/pendientes para empleados -->
    <?php if ($_SESSION['id_rol'] == 2): ?>
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <?php
        $tabs = [
            ''           => 'Todos',
            'saliente'   => 'Salientes',
            'entrante'   => 'Entrantes',
            'pendientes' => 'Pendientes de recepción' . (!empty($envios_pendientes) ? ' (' . count($envios_pendientes) . ')' : ''),
        ];
        foreach ($tabs as $val => $label):
            $activo = $filtro_tipo === $val;
            $esPend = $val === 'pendientes' && !empty($envios_pendientes) && !$activo;
        ?>
        <a href="/admin/router.php?pagina=envios<?= $val ? '&tipo='.$val : '' ?>"
           style="padding:8px 18px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;
                  <?= $activo ? 'background:#4f46e5;color:#fff;' : ($esPend ? 'background:#fef9ec;color:#92400e;border:1px solid #fbbf24;' : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;') ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($filtro_tipo === 'pendientes'): ?>
    <?php if (!empty($envios_pendientes)): ?>
    <div class="lt-card" style="padding:0;overflow:hidden;margin-bottom:20px;border:1.5px solid #fbbf24;">
        <div style="padding:12px 16px;background:#fef9ec;border-bottom:1px solid #fde68a;font-size:12px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.6px;">
            ⏳ Paquetes que esperan confirmación de recepción en tu sucursal
        </div>
        <table>
            <thead>
                <tr><th>Tracking</th><th>Remitente</th><th>Destinatario</th><th>Destino</th><th>Fecha creación</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($envios_pendientes as $p): ?>
            <tr>
                <td><span class="tracking-mono"><?= htmlspecialchars($p['nro_tracking']) ?></span></td>
                <td><?= htmlspecialchars(($p['apellido_remitente'] ?? '') . ', ' . ($p['nombre_remitente'] ?? '')) ?></td>
                <td><?= htmlspecialchars(($p['apellido_dest'] ?? '') . ', ' . ($p['nombre_dest'] ?? '')) ?></td>
                <td style="font-size:12px;"><?= htmlspecialchars($p['suc_destino'] ?? $p['direccion_entrega'] ?? '—') ?></td>
                <td style="font-size:12px;color:#94a3b8;"><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="accion" value="confirmar_recepcion">
                        <input type="hidden" name="nro_tracking" value="<?= htmlspecialchars($p['nro_tracking']) ?>">
                        <button type="submit" style="padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid #a0d0b0;background:#e8f8ee;color:#3a6050;cursor:pointer;">✓ Confirmar recepción</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="lt-card" style="text-align:center;padding:32px;color:#94a3b8;margin-bottom:20px;">✅ No hay paquetes pendientes de recepción.</div>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Filtros (ocultar en modo pendientes) -->
    <?php if ($filtro_tipo !== 'pendientes'): ?>
    <form method="get" action="/admin/router.php" class="filtros-bar">
        <input type="hidden" name="pagina" value="envios">
        <?php if (!empty($filtro_tipo)): ?>
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($filtro_tipo) ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>Buscar</label>
            <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>"
                   placeholder="Tracking, nombre, destino..."
                   style="width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-family:inherit;">
        </div>
        <?php if ($_SESSION['id_rol'] == 1): ?>
        <div class="form-group">
            <label>Sucursal</label>
            <select name="sucursal">
                <option value="0">Todas las sucursales</option>
                <?php foreach ($sucursales as $s): ?>
                <option value="<?= $s['id_sucursal'] ?>" <?= $filtro_sucursal == $s['id_sucursal'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado">
                <option value="0">Todos los estados</option>
                <?php foreach ($estados as $e): ?>
                <option value="<?= $e['id_estado'] ?>" <?= $filtro_estado == $e['id_estado'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">Filtrar</button>
        <a href="/admin/router.php?pagina=envios" class="btn-sm" style="padding:9px 16px;">Limpiar</a>
    </form>
    <?php endif; ?>

    <!-- Tabla -->
    <?php if ($filtro_tipo !== 'pendientes'): ?>
    <div class="lt-card" style="padding:0;overflow:hidden;">
        <?php if (empty($envios)): ?>
            <div style="text-align:center;padding:48px;color:var(--text-soft);">
                <div style="font-size:40px;margin-bottom:10px;">📦</div>
                <em>Sin envíos encontrados para los filtros seleccionados.</em>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Tracking</th>
                    <th>Remitente</th>
                    <th>Destinatario</th>
                    <th>Destino</th>
                    <th>Estado actual</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($envios as $e): ?>
                <?php
                    // Clase del badge según estado
                    $badgeClass = '';
                    $estado = strtolower($e['estado_actual'] ?? '');
                    if (str_contains($estado, 'entregado'))  $badgeClass = 'badge-entregado';
                    elseif (str_contains($estado, 'viaje'))  $badgeClass = 'badge-viaje';
                    elseif (str_contains($estado, 'devuelto')) $badgeClass = 'badge-devuelto';
                ?>
                <tr>
                    <td><span class="tracking-mono"><?= htmlspecialchars($e['nro_tracking']) ?></span></td>
                    <td><?= htmlspecialchars($e['apellido_remitente'] . ', ' . $e['nombre_remitente']) ?></td>
                    <td><?= htmlspecialchars($e['apellido_dest'] . ', ' . $e['nombre_dest']) ?></td>
                    <td class="destino-icon">
                        <?php if ($e['suc_destino']): ?>
                            🏢 <?= htmlspecialchars($e['suc_destino']) ?>
                        <?php elseif ($e['direccion_entrega']): ?>
                            🏠 <?= htmlspecialchars($e['direccion_entrega']) ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge-estado <?= $badgeClass ?>">
                            <?= htmlspecialchars($e['estado_actual'] ?? 'Sin estado') ?>
                        </span>
                    </td>
                    <td style="color:var(--text-soft);font-size:13px;">
                        <?= date('d/m/Y', strtotime($e['fecha_creacion'])) ?>
                    </td>
                    <td style="display:flex;gap:6px;">
                        <a href="/admin/router.php?pagina=gestionar_envios&tracking=<?= urlencode($e['nro_tracking']) ?>" class="btn-sm">
                            Gestionar →
                        </a>
                        <?php if ($_SESSION['id_rol'] == 1): ?>
                        <a href="/admin/router.php?pagina=editar_envio&tracking=<?= urlencode($e['nro_tracking']) ?>"
                           class="btn-sm" title="Editar datos">&#9998;</a>
                        <?php endif; ?>
                        <?php
                        $est_lower = strtolower($e['estado_actual'] ?? '');
                        $es_final = str_contains($est_lower, 'nulad')
                                 || str_contains($est_lower, 'ntregado')
                                 || str_contains($est_lower, 'devuelto');
                        if ($_SESSION['id_rol'] == 1 && !$es_final): ?>
                        <a href="/admin/router.php?pagina=envios&confirmar_anular=<?= urlencode($e['nro_tracking']) ?>"
                           class="btn-sm" style="border-color:#f5a0a0;color:#b03030;" title="Anular envío">&#128465;</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
