<?php
// vars: $cliente, $error, $success
$page_subtitle = 'Mi perfil';
$nav_links = [];
$extra_css = '
body { background: #f1f5f9; }
.lt-container { max-width: 100%; margin: 0; padding: 0; }
.panel-wrap { display: flex; min-height: calc(100vh - 56px); }

.p-sidebar {
    width: 220px; flex-shrink: 0;
    background: #1e1b4b; padding: 28px 0 20px;
    display: flex; flex-direction: column; gap: 2px;
    position: sticky; top: 56px;
    height: calc(100vh - 56px); overflow-y: auto;
}
.p-sidebar-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.2px; color: #6366f1; padding: 0 20px 8px; margin-top: 4px;
}
.p-sidebar a {
    display: flex; align-items: center; gap: 11px;
    padding: 10px 20px; color: #c7d2fe; text-decoration: none;
    font-size: 14px; font-weight: 500;
    border-left: 3px solid transparent;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.p-sidebar a:hover, .p-sidebar a.active { background: rgba(99,102,241,0.18); color: #fff; border-left-color: #818cf8; }
.p-sidebar a i { width: 17px; text-align: center; font-size: 14px; opacity: 0.85; }

.p-main { flex: 1; padding: 32px 36px; min-width: 0; max-width: 640px; }
.p-greeting { margin-bottom: 28px; }
.p-greeting h1 { font-size: 24px; font-weight: 700; color: #1e1b3a; margin-bottom: 4px; font-family: "DM Serif Display", serif; }
.p-greeting p { font-size: 14px; color: #64748b; }

.card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:20px; }
.card-title { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:#475569; margin-bottom:16px; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase;
                    letter-spacing: 0.5px; color: #64748b; margin-bottom: 5px; }
.form-group input { width: 100%; padding: 9px 11px; border: 1.5px solid #e2e8f0;
    border-radius: 9px; font-size: 14px; background: #fff; color: #1e1b3a;
    box-sizing: border-box; transition: border-color 0.2s; font-family: inherit; }
.form-group input:focus { outline: none; border-color: #6366f1; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media (max-width:560px) { .form-row { grid-template-columns:1fr; } }
.btn-submit { padding: 10px 22px; background: #4f46e5; color: #fff;
              border: none; border-radius: 9px; font-size: 14px; font-weight: 700;
              cursor: pointer; transition: opacity 0.2s; }
.btn-submit:hover { opacity: 0.88; }
.btn-danger { padding: 9px 18px; background:#fff; color:#b03030; border:1.5px solid #f5a0a0;
              border-radius:9px; font-size:13px; font-weight:700; cursor:pointer; }
.btn-danger:hover { background:#b03030; color:#fff; }
.btn-cancel { padding: 9px 18px; background:#fff; color:#64748b; border:1.5px solid #e2e8f0;
              border-radius:9px; font-size:13px; font-weight:600; text-decoration:none; display:inline-block; }
.alert-error { background: #fde8e8; border: 1.5px solid #f5a0a0; color: #b03030;
               border-radius: 9px; padding: 9px 13px; margin-bottom: 14px; font-size: 13px; }
.alert-ok    { background: #e8f8ee; border: 1.5px solid #a0d0b0; color: #3a6050;
               border-radius: 9px; padding: 9px 13px; margin-bottom: 14px; font-size: 13px; }
.danger-zone { border-color:#f5a0a0; }
.danger-zone .card-title { color:#b03030; }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
<div class="panel-wrap">

    <aside class="p-sidebar">
        <div class="p-sidebar-label">Mi cuenta</div>
        <a href="/cliente/router.php?pagina=enviar"><i class="fa-solid fa-box-open"></i> Enviar paquete</a>
        <a href="/cliente/router.php?pagina=rastrear"><i class="fa-solid fa-magnifying-glass"></i> Rastrear pedido</a>
        <a href="/cliente/router.php?pagina=mis_envios"><i class="fa-solid fa-list"></i> Mis envíos</a>
        <a href="/cliente/router.php?pagina=perfil" class="active"><i class="fa-solid fa-user"></i> Mi perfil</a>
    </aside>

    <main class="p-main">
        <div class="p-greeting">
            <h1>Mi perfil</h1>
            <p>Tus datos personales</p>
        </div>

        <?php if ($error):   ?><div class="alert-error">&#9888; <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert-ok">&#10003; <?= htmlspecialchars($success) ?></div><?php endif; ?>

        <div class="card">
            <div class="card-title">Editar datos</div>
            <form method="POST">
                <input type="hidden" name="accion" value="editar">
                <div class="form-row">
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" value="<?= htmlspecialchars($cliente['dni']) ?>" disabled style="background:#f1f5f9;color:#94a3b8;">
                    </div>
                    <div class="form-group">
                        <label>Usuario *</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? $cliente['username']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido *</label>
                        <input type="text" name="apellido" value="<?= htmlspecialchars($cliente['apellido']) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn-submit">Guardar cambios</button>
            </form>
        </div>

        <div class="card danger-zone">
            <div class="card-title">Zona de peligro</div>
            <?php if (!isset($_GET['confirmar_baja'])): ?>
                <p style="font-size:13px;color:#64748b;margin-bottom:14px;">
                    Si te das de baja, tu cuenta quedará desactivada y no vas a poder iniciar sesión.
                    Tu historial de envíos no se elimina.
                </p>
                <a href="/cliente/router.php?pagina=perfil&confirmar_baja=1" class="btn-danger" style="text-decoration:none;display:inline-block;">Dar de baja mi cuenta</a>
            <?php else: ?>
                <p style="font-size:13px;color:#b03030;font-weight:600;margin-bottom:14px;">
                    &#9888; ¿Confirmás que querés dar de baja tu cuenta? Vas a cerrar sesión y no vas a poder volver a entrar hasta que un administrador la reactive.
                </p>
                <form method="POST" style="display:flex;gap:10px;">
                    <input type="hidden" name="accion" value="baja">
                    <input type="hidden" name="confirmar" value="1">
                    <button type="submit" class="btn-danger">Sí, dar de baja mi cuenta</button>
                    <a href="/cliente/router.php?pagina=perfil" class="btn-cancel">Cancelar</a>
                </form>
            <?php endif; ?>
        </div>
    </main>

</div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
