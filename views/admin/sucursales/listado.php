<?php
// vars: $error, $success, $edit, $sucursales, $tiene_direccion
$page_subtitle = 'Sucursales';
$nav_links = [['href' => '/admin/index.php', 'label' => '← Panel']];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 900px; }
    .layout { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
    @media (max-width: 700px) { .layout { grid-template-columns: 1fr; } }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    thead th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase;
               letter-spacing: 0.8px; color: var(--text-soft); padding: 10px 14px;
               border-bottom: 1.5px solid var(--border); }
    tbody tr { border-bottom: 1px solid var(--nude-dark); transition: background 0.15s; }
    tbody tr:hover { background: var(--nude); }
    tbody td { padding: 11px 14px; vertical-align: middle; }
    .btn-sm { padding: 5px 11px; border-radius: 8px; font-size: 12px; font-weight: 600;
              border: 1.5px solid var(--border); background: var(--white); color: var(--text-soft);
              text-decoration: none; cursor: pointer; transition: all 0.2s; display:inline-block; }
    .btn-sm:hover { border-color: var(--rose); color: var(--rose-dark); }
    .btn-danger { border-color: #f5a0a0; color: #b03030; }
    .btn-danger:hover { background: #b03030; color: #fff; border-color: #b03030; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase;
                        letter-spacing: 0.5px; color: var(--text-soft); margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; padding: 9px 11px; border: 1.5px solid var(--border);
        border-radius: 9px; font-size: 14px; background: var(--white); color: var(--text);
        box-sizing: border-box; transition: border-color 0.2s; font-family: inherit; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--rose); }
    .btn-submit { width: 100%; padding: 10px; background: var(--rose-dark); color: #fff;
                  border: none; border-radius: 9px; font-size: 14px; font-weight: 700;
                  cursor: pointer; transition: opacity 0.2s; }
    .btn-submit:hover { opacity: 0.88; }
    .alert-error { background: #fde8e8; border: 1.5px solid #f5a0a0; color: #b03030;
                   border-radius: 9px; padding: 9px 13px; margin-bottom: 14px; font-size: 13px; }
    .alert-ok    { background: #e8f8ee; border: 1.5px solid #a0d0b0; color: #3a6050;
                   border-radius: 9px; padding: 9px 13px; margin-bottom: 14px; font-size: 13px; }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);margin-bottom:20px;">Sucursales</div>

    <?php if ($error):   ?><div class="alert-error">&#9888; <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-ok">&#10003; <?= $success ?></div><?php endif; ?>

    <?php if (isset($_GET['confirmar_baja'])): ?>
    <div class="lt-card" style="border:1.5px solid #f5a0a0;margin-bottom:20px;">
        <div style="font-weight:700;color:#b03030;margin-bottom:6px;">&#9888; Confirmar baja</div>
        <div style="font-size:13px;margin-bottom:14px;">
            ¿Dar de baja la sucursal <strong>#<?= intval($_GET['confirmar_baja']) ?></strong>?
            Esta acción no puede deshacerse si tiene envíos asociados.
        </div>
        <form method="POST" style="display:flex;gap:10px;">
            <input type="hidden" name="accion"       value="baja">
            <input type="hidden" name="id_sucursal"  value="<?= intval($_GET['confirmar_baja']) ?>">
            <button type="submit" class="btn-sm btn-danger" style="padding:8px 16px;">Sí, dar de baja</button>
            <a href="/admin/router.php?pagina=sucursales" class="btn-sm">Cancelar</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="layout">
        <!-- Tabla -->
        <div class="lt-card" style="padding:0;overflow:hidden;">
            <?php if (empty($sucursales)): ?>
                <div style="text-align:center;padding:40px;color:var(--text-soft);">
                    <div style="font-size:36px;margin-bottom:8px;">🏢</div>
                    <em>No hay sucursales registradas.</em>
                </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <?php if ($tiene_direccion): ?><th>Dirección</th><?php endif; ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sucursales as $s):
                    $activa = !empty($s['activo']);
                ?>
                    <tr style="<?= !$activa ? 'opacity:0.55;' : '' ?>">
                        <td style="font-weight:600;">
                            <?= htmlspecialchars($s['nombre']) ?>
                            <?php if (!$activa): ?>
                                <span style="font-size:10px;font-weight:700;color:#b03030;background:#fde8e8;padding:2px 7px;border-radius:8px;margin-left:6px;">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($tiene_direccion): ?>
                        <td style="font-size:13px;color:var(--text-soft);"><?= htmlspecialchars($s['direccion'] ?? '—') ?></td>
                        <?php endif; ?>
                        <td style="display:flex;gap:6px;">
                            <?php if ($activa): ?>
                                <a href="/admin/router.php?pagina=sucursales&editar=<?= $s['id_sucursal'] ?>" class="btn-sm">&#9998; Editar</a>
                                <a href="/admin/router.php?pagina=sucursales&confirmar_baja=<?= $s['id_sucursal'] ?>" class="btn-sm btn-danger">&#128465;</a>
                            <?php else: ?>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="accion"      value="activar">
                                    <input type="hidden" name="id_sucursal" value="<?= $s['id_sucursal'] ?>">
                                    <button type="submit" class="btn-sm" style="border-color:#a0d0b0;color:#3a6050;">&#10003; Reactivar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Formulario -->
        <div class="lt-card">
            <div style="font-weight:700;font-size:15px;margin-bottom:14px;color:var(--text);">
                <?= $edit ? '✏️ Editar sucursal' : '+ Nueva sucursal' ?>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="<?= $edit ? 'editar' : 'crear' ?>">
                <?php if ($edit): ?>
                    <input type="hidden" name="id_sucursal" value="<?= $edit['id_sucursal'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" placeholder="Ej: Sucursal Centro"
                           value="<?= htmlspecialchars($edit['nombre'] ?? $_POST['nombre'] ?? '') ?>" required>
                </div>
                <?php if ($tiene_direccion): ?>
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" placeholder="Ej: Av. Corrientes 1234"
                           value="<?= htmlspecialchars($edit['direccion'] ?? $_POST['direccion'] ?? '') ?>">
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" placeholder="Ej: 0261-4123456"
                           value="<?= htmlspecialchars($edit['telefono'] ?? $_POST['telefono'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Localidad</label>
                    <select name="id_localidad">
                        <option value="">— Sin localidad —</option>
                        <?php
                        $sel_localidad = $edit['id_localidad'] ?? $_POST['id_localidad'] ?? null;
                        foreach ($localidades as $l):
                        ?>
                        <option value="<?= $l['id_localidad'] ?>" <?= $sel_localidad == $l['id_localidad'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['nombre']) ?> (<?= htmlspecialchars($l['provincia'] ?? '?') ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit"><?= $edit ? 'Guardar cambios' : 'Crear sucursal' ?></button>
                <?php if ($edit): ?>
                    <a href="/admin/router.php?pagina=sucursales" style="display:block;text-align:center;margin-top:10px;font-size:13px;color:var(--text-soft);">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
