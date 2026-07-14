<?php
// vars: $usuario, $error, $success, $sucursales, $id
$page_subtitle = 'Detalle Usuario';
$nav_links = [['href' => '/admin/router.php?pagina=usuarios', 'label' => '← Usuarios']];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 560px; }
    .perfil-header { display: flex; align-items: center; gap: 18px; margin-bottom: 28px; }
    .perfil-avatar { width: 64px; height: 64px; border-radius: 50%; background: var(--nude-dark); display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0; }
    .perfil-nombre { font-family: "DM Serif Display", serif; font-size: 22px; color: var(--text); }
    .perfil-meta { font-size: 13px; color: var(--text-soft); margin-top: 2px; }
    .badge-estado-pill { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-left: 8px; }
    .activo  { background: #e8f8ee; color: #3a9060; }
    .inactivo { background: #fee2e2; color: #dc2626; }
    .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-soft); margin: 24px 0 12px; }
    .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--nude-dark); font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: var(--text-soft); }
    .info-value { font-weight: 500; color: var(--text); }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">

    <!-- Perfil -->
    <div class="lt-card">
        <div class="perfil-header">
            <div class="perfil-avatar">👤</div>
            <div>
                <div class="perfil-nombre">
                    <?= htmlspecialchars($usuario['apellido'] . ', ' . $usuario['nombre']) ?>
                    <span class="badge-estado-pill <?= $usuario['estado'] ? 'activo' : 'inactivo' ?>">
                        <?= $usuario['estado'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                </div>
                <div class="perfil-meta">@<?= htmlspecialchars($usuario['username']) ?> · <?= htmlspecialchars($usuario['rol']) ?></div>
            </div>
        </div>

        <div class="section-title">Datos del sistema</div>
        <?php if ($usuario['legajo']): ?>
        <div class="info-row"><span class="info-label">Legajo</span><span class="info-value"><?= htmlspecialchars($usuario['legajo']) ?></span></div>
        <?php elseif ($usuario['dni_cliente']): ?>
        <div class="info-row"><span class="info-label">DNI cliente</span><span class="info-value"><?= htmlspecialchars($usuario['dni_cliente']) ?></span></div>
        <?php endif; ?>
    </div>

    <!-- Formulario de edición (UPDATE) -->
    <div class="lt-card" style="margin-top:20px;">
        <div class="lt-card-title">Editar datos</div>
        <div class="lt-card-sub">Modificá los datos del usuario</div>

        <?php if ($error): ?>
            <div class="msg-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="accion" value="editar">

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
                </div>
            </div>

            <?php if (in_array($usuario['id_rol'], [2, 3])): ?>
            <div class="form-group">
                <label>Sucursal</label>
                <select name="id_sucursal">
                    <option value="">Sin cambios</option>
                    <?php $id_suc_actual = $usuario['id_sucursal'] ?? null; foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>" <?= $id_suc_actual == $s['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="lt-section-label">Nueva contraseña (dejar vacío para no cambiar)</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label>Repetir</label>
                    <input type="password" name="password2" placeholder="••••••">
                </div>
            </div>

            <button type="submit" class="btn-primary">Guardar cambios</button>
        </form>
    </div>

    <!-- Baja lógica -->
    <div class="lt-card" style="margin-top:20px; border: 1.5px solid <?= $usuario['estado'] ? '#fecaca' : '#e8f8ee' ?>;">
        <div class="lt-card-title" style="color: <?= $usuario['estado'] ? '#dc2626' : '#3a9060' ?>;">
            <?= $usuario['estado'] ? '⚠️ Desactivar usuario' : '✅ Reactivar usuario' ?>
        </div>
        <div class="lt-card-sub">
            <?php if ($usuario['estado']): ?>
                El usuario dejará de poder iniciar sesión. Sus datos y registros se conservan.
            <?php else: ?>
                El usuario podrá volver a iniciar sesión en el sistema.
            <?php endif; ?>
        </div>
        <?php if (isset($_GET['confirmar_toggle'])): ?>
        <form method="post" style="display:flex;gap:10px;margin-top:12px;">
            <input type="hidden" name="accion"       value="toggle_estado">
            <input type="hidden" name="nuevo_estado" value="<?= $usuario['estado'] ? 0 : 1 ?>">
            <button type="submit" class="btn-primary" style="background:<?= $usuario['estado'] ? '#dc2626' : '#3a9060' ?>;">
                Sí, confirmar
            </button>
            <a href="/admin/router.php?pagina=ver_usuario&id=<?= $id ?>" class="btn-primary" style="background:#999;text-decoration:none;display:inline-flex;align-items:center;">Cancelar</a>
        </form>
        <?php else: ?>
        <a href="/admin/router.php?pagina=ver_usuario&id=<?= $id ?>&confirmar_toggle=1"
           class="btn-primary" style="background:<?= $usuario['estado'] ? '#dc2626' : '#3a9060' ?>;display:inline-block;margin-top:12px;text-decoration:none;">
            <?= $usuario['estado'] ? 'Desactivar cuenta' : 'Reactivar cuenta' ?>
        </a>
        <?php endif; ?>
    </div>

</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
