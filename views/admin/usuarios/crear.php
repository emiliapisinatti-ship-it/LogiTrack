<?php
// vars: $error, $success, $sucursales, $licencias
$page_subtitle = 'Nuevo Usuario';
$nav_links = [['href' => '/admin/router.php?pagina=usuarios', 'label' => '← Usuarios']];
$rol_actual = $_POST['id_rol'] ?? '2';
$extra_css = '
    body { min-height: 100vh; background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); }
    .lt-container { max-width: 520px; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--text-soft);
                 text-decoration: none; font-size: 13px; font-weight: 500; margin-bottom: 20px;
                 transition: color 0.2s; }
    .back-link:hover { color: var(--rose-dark); }

    /* Radios funcionales invisibles */
    #r-emp, #r-chof {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px;
        height: 1px;
    }

    .rol-selector { display: flex; gap: 12px; margin-bottom: 20px; }
    .rol-option {
        flex: 1; border: 2px solid var(--border); border-radius: 14px;
        padding: 16px; text-align: center; cursor: pointer;
        transition: border-color 0.2s, background 0.2s; background: var(--white);
        display: block;
    }
    .rol-option .rol-icon  { font-size: 28px; margin-bottom: 6px; }
    .rol-option .rol-label { font-size: 13px; font-weight: 600; color: var(--text); }
    .rol-option .rol-desc  { font-size: 11px; color: var(--text-soft); margin-top: 3px; }

    /* Resaltar opción activa */
    #r-emp:checked  ~ .rol-selector label[for="r-emp"]  { border-color: var(--rose-dark); background: var(--nude); }
    #r-chof:checked ~ .rol-selector label[for="r-chof"] { border-color: var(--rose-dark); background: var(--nude); }

    /* Por defecto (empleado): mostrar teléfono, ocultar licencia */
    .campo-licencia { display: none; }

    /* Al elegir chofer: intercambiar */
    #r-chof:checked ~ .campo-telefono { display: none; }
    #r-chof:checked ~ .campo-licencia { display: block; }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <a href="/admin/router.php?pagina=usuarios" class="back-link">&larr; Volver a usuarios</a>

    <div class="lt-card">
        <div class="lt-card-title">Crear usuario</div>
        <div class="lt-card-sub">Agregá un empleado de sucursal o un chofer al sistema</div>

        <?php if ($error): ?>
            <div class="msg-error">&#9888; <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg-success">&#10003; <?= $success ?>
                <a href="/admin/router.php?pagina=crear_usuario" style="color:#3a9060;font-weight:600;">Crear otro &rarr;</a>
            </div>
        <?php endif; ?>

        <form method="post">

            <!-- Radios funcionales: PRIMERO en el form para que el CSS :checked funcione -->
            <input type="radio" id="r-emp"  name="id_rol" value="2" <?= $rol_actual == '2' ? 'checked' : '' ?>>
            <input type="radio" id="r-chof" name="id_rol" value="3" <?= $rol_actual == '3' ? 'checked' : '' ?>>

            <div class="lt-section-label">Rol</div>
            <div class="rol-selector">
                <label for="r-emp" class="rol-option">
                    <div class="rol-icon">&#127962;</div>
                    <div class="rol-label">Empleado de sucursal</div>
                    <div class="rol-desc">Gestiona envíos e incidentes</div>
                </label>
                <label for="r-chof" class="rol-option">
                    <div class="rol-icon">&#128667;</div>
                    <div class="rol-label">Chofer</div>
                    <div class="rol-desc">Realiza y gestiona viajes</div>
                </label>
            </div>

            <div class="lt-section-label">Datos personales</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" placeholder="Juan"
                           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Apellido *</label>
                    <input type="text" name="apellido" placeholder="Pérez"
                           value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>DNI *</label>
                <input type="text" name="dni" placeholder="12345678"
                       value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Sucursal *</label>
                <select name="id_sucursal" required>
                    <option value="">— Seleccioná una sucursal —</option>
                    <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s['id_sucursal'] ?>"
                        <?= (isset($_POST['id_sucursal']) && $_POST['id_sucursal'] == $s['id_sucursal']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Solo empleado (visible por defecto) -->
            <div class="form-group campo-telefono">
                <label>Teléfono</label>
                <input type="text" name="telefono" placeholder="0291-1234567"
                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
            </div>

            <!-- Solo chofer (oculto por defecto) -->
            <div class="form-group campo-licencia">
                <label>Categoría de licencia *</label>
                <select name="id_licencia">
                    <option value="">— Seleccioná una categoría —</option>
                    <?php foreach ($licencias as $l): ?>
                    <option value="<?= $l['id_licencia'] ?>"
                        <?= (isset($_POST['id_licencia']) && $_POST['id_licencia'] == $l['id_licencia']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($l['codigo'] . ' — ' . $l['descripcion']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lt-section-label">Acceso al sistema</div>
            <div class="form-group">
                <label>Nombre de usuario *</label>
                <input type="text" name="username" placeholder="juan.perez"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contraseña *</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="form-group">
                    <label>Repetir *</label>
                    <input type="password" name="password2" placeholder="••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-primary">Crear usuario</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
