<?php
// vars: $error, $success, $edit, $vehiculos, $tipos, $sucursales
$page_subtitle = 'Vehículos';
$nav_links = [['href' => '/admin/index.php', 'label' => '← Panel']];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 1050px; }
    .layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
    @media (max-width: 750px) { .layout { grid-template-columns: 1fr; } }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    thead th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase;
               letter-spacing: 0.8px; color: var(--text-soft); padding: 10px 14px;
               border-bottom: 1.5px solid var(--border); }
    tbody tr { border-bottom: 1px solid var(--nude-dark); transition: background 0.15s; }
    tbody tr:hover { background: var(--nude); }
    tbody td { padding: 10px 14px; vertical-align: middle; }
    .badge { display:inline-block; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
    .badge-activo   { background:#e8f8ee; color:#3a6050; }
    .badge-inactivo { background:#fde8e8; color:#b03030; }
    .btn-sm { padding: 5px 11px; border-radius: 8px; font-size: 12px; font-weight: 600;
              border: 1.5px solid var(--border); background: var(--white); color: var(--text-soft);
              text-decoration: none; cursor: pointer; transition: all 0.2s; display:inline-block; }
    .btn-sm:hover { border-color: var(--rose); color: var(--rose-dark); }
    .btn-danger  { border-color: #f5a0a0; color: #b03030; }
    .btn-danger:hover  { background: #b03030; color: #fff; border-color: #b03030; }
    .btn-success { border-color: #a0d0b0; color: #3a6050; }
    .btn-success:hover { background: #3a6050; color: #fff; border-color: #3a6050; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase;
                        letter-spacing: 0.5px; color: var(--text-soft); margin-bottom: 5px; }
    .form-group input, .form-group select {
        width: 100%; padding: 9px 11px; border: 1.5px solid var(--border);
        border-radius: 9px; font-size: 14px; background: var(--white); color: var(--text);
        box-sizing: border-box; transition: border-color 0.2s; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--rose); }
    .btn-submit { width: 100%; padding: 10px; background: var(--rose-dark); color: #fff;
                  border: none; border-radius: 9px; font-size: 14px; font-weight: 700;
                  cursor: pointer; transition: opacity 0.2s; }
    .btn-submit:hover { opacity: 0.88; }
    .alert-error { background: #fde8e8; border: 1.5px solid #f5a0a0; color: #b03030;
                   border-radius: 9px; padding: 9px 13px; margin-bottom: 14px; font-size: 13px; }
    .alert-ok    { background: #e8f8ee; border: 1.5px solid #a0d0b0; color: #3a6050;
                   border-radius: 9px; padding: 9px 13px; margin-bottom: 14px; font-size: 13px; }
    .patente-mono { font-family: monospace; font-weight: 700; font-size: 13px; letter-spacing: 1px; color: var(--rose-dark); }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);margin-bottom:20px;">Vehículos</div>

    <?php if ($error):   ?><div class="alert-error">&#9888; <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-ok">&#10003; <?= $success ?></div><?php endif; ?>

    <?php if (isset($_GET['confirmar_baja'])): ?>
    <div class="lt-card" style="border:1.5px solid #f5a0a0;margin-bottom:20px;">
        <div style="font-weight:700;color:#b03030;margin-bottom:6px;">&#9888; Confirmar baja</div>
        <div style="font-size:13px;margin-bottom:14px;">
            ¿Dar de baja el vehículo <strong><?= htmlspecialchars($_GET['confirmar_baja']) ?></strong>?
            No podrá usarse en viajes hasta que se reactive.
        </div>
        <form method="POST" style="display:flex;gap:10px;">
            <input type="hidden" name="accion"  value="baja">
            <input type="hidden" name="patente" value="<?= htmlspecialchars($_GET['confirmar_baja']) ?>">
            <button type="submit" class="btn-sm btn-danger" style="padding:8px 16px;">Sí, dar de baja</button>
            <a href="/admin/router.php?pagina=vehiculos" class="btn-sm">Cancelar</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="lt-card" style="margin-bottom:20px;">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <input type="hidden" name="pagina" value="vehiculos">
            <div class="form-group" style="margin-bottom:0;min-width:160px;">
                <label>Sucursal</label>
                <select name="sucursal">
                    <option value="0">Todas</option>
                    <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s['id_sucursal'] ?>" <?= $filtro_sucursal == $s['id_sucursal'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;min-width:140px;">
                <label>Estado</label>
                <select name="estado">
                    <option value=""         <?= $filtro_estado === ''         ? 'selected' : '' ?>>Todos</option>
                    <option value="Activo"   <?= $filtro_estado === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                    <option value="Inactivo" <?= $filtro_estado === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;min-width:160px;">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="0">Todos</option>
                    <?php foreach ($tipos as $t): ?>
                    <option value="<?= $t['id_tipo_veh'] ?>" <?= $filtro_tipo == $t['id_tipo_veh'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-sm" style="padding:9px 16px;">Filtrar</button>
            <?php if ($filtro_sucursal || $filtro_estado || $filtro_tipo): ?>
            <a href="/admin/router.php?pagina=vehiculos" class="btn-sm">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="layout">

        <!-- Tabla de vehículos -->
        <div class="lt-card" style="padding:0;overflow:hidden;">
            <?php if (empty($vehiculos)): ?>
                <div style="text-align:center;padding:40px;color:var(--text-soft);">
                    <div style="font-size:36px;margin-bottom:8px;">🚚</div>
                    <em>No hay vehículos registrados.</em>
                </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Patente</th>
                        <th>Modelo</th>
                        <th>Tipo / Cap.</th>
                        <th>Sucursal</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehiculos as $v): ?>
                    <tr>
                        <td><span class="patente-mono"><?= htmlspecialchars($v['patente']) ?></span></td>
                        <td style="font-weight:600;"><?= htmlspecialchars($v['modelo']) ?></td>
                        <td style="font-size:13px;color:var(--text-soft);">
                            <?= htmlspecialchars($v['tipo']) ?>
                            <span style="color:#bbb;">·</span>
                            <?= number_format($v['capacidad_kg_max'], 0) ?> kg
                        </td>
                        <td style="font-size:13px;"><?= htmlspecialchars($v['sucursal'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $v['estado'] === 'Activo' ? 'badge-activo' : 'badge-inactivo' ?>">
                                <?= htmlspecialchars($v['estado']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                <a href="/admin/router.php?pagina=vehiculos&editar=<?= urlencode($v['patente']) ?>" class="btn-sm">✏️ Editar</a>
                                <?php if ($v['estado'] === 'Activo'): ?>
                                <a href="/admin/router.php?pagina=vehiculos&confirmar_baja=<?= urlencode($v['patente']) ?>" class="btn-sm btn-danger">&#8681; Baja</a>
                                <?php else: ?>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="accion"  value="activar">
                                    <input type="hidden" name="patente" value="<?= htmlspecialchars($v['patente']) ?>">
                                    <button type="submit" class="btn-sm btn-success">✅ Activar</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Formulario crear / editar -->
        <div class="lt-card">
            <div style="font-weight:700;font-size:15px;margin-bottom:14px;color:var(--text);">
                <?= $edit ? '✏️ Editar vehículo' : '🚚 Nuevo vehículo' ?>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="<?= $edit ? 'editar' : 'crear' ?>">

                <div class="form-group">
                    <label>Patente *</label>
                    <input type="text" name="patente"
                           placeholder="Ej: ABC123"
                           value="<?= htmlspecialchars($edit['patente'] ?? strtoupper($_POST['patente'] ?? '')) ?>"
                           <?= $edit ? 'readonly style="background:#f5f5f5;color:#999;"' : '' ?>
                           required>
                </div>

                <div class="form-group">
                    <label>Modelo *</label>
                    <input type="text" name="modelo"
                           placeholder="Ej: Ford Transit 2022"
                           value="<?= htmlspecialchars($edit['modelo'] ?? $_POST['modelo'] ?? '') ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>Tipo de vehículo *</label>
                    <select name="id_tipo_veh" required>
                        <option value="">Seleccioná...</option>
                        <?php foreach ($tipos as $t): ?>
                        <option value="<?= $t['id_tipo_veh'] ?>"
                            <?= (($edit['id_tipo_veh'] ?? $_POST['id_tipo_veh'] ?? '') == $t['id_tipo_veh']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nombre']) ?> — <?= number_format($t['capacidad_kg_max'], 0) ?> kg máx.
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Sucursal asignada</label>
                    <select name="id_sucursal">
                        <option value="">Sin asignar</option>
                        <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>"
                            <?= (($edit['id_sucursal'] ?? $_POST['id_sucursal'] ?? '') == $s['id_sucursal']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($edit): ?>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="Activo"   <?= ($edit['estado'] === 'Activo')   ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($edit['estado'] === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn-submit">
                    <?= $edit ? 'Guardar cambios' : 'Crear vehículo' ?>
                </button>
                <?php if ($edit): ?>
                    <a href="/admin/router.php?pagina=vehiculos"
                       style="display:block;text-align:center;margin-top:10px;font-size:13px;color:var(--text-soft);">
                        Cancelar
                    </a>
                <?php endif; ?>
            </form>
        </div>

    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
