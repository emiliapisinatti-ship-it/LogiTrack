<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiTrack — Acceso Interno</title>
    <link rel="stylesheet" href="/assets/estilo.css?v=3">
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(145deg,#2e2e3a 0%,#1a1a24 100%); padding:24px; }
        .login-wrap { display:flex; width:780px; min-height:480px; border-radius:24px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.4); }
        .login-left { flex:1; background:linear-gradient(160deg,#1e1b4b,#312e81); display:flex; flex-direction:column; justify-content:center; padding:52px 44px; color:white; }
        .login-left .brand { font-family:'DM Serif Display',serif; font-size:30px; margin-bottom:6px; color:var(--rose); }
        .login-left .brand-sub { font-size:12px; opacity:0.6; text-transform:uppercase; letter-spacing:2px; margin-bottom:40px; }
        .login-left .roles { display:flex; flex-direction:column; gap:10px; }
        .role-item { display:flex; align-items:center; gap:12px; padding:10px 14px; background:rgba(255,255,255,0.07); border-radius:10px; font-size:13px; color:rgba(255,255,255,0.75); }
        .role-dot { width:8px; height:8px; border-radius:50%; background:var(--rose); flex-shrink:0; }
        .login-right { flex:1; background:#1c1c2a; display:flex; flex-direction:column; justify-content:center; padding:52px 44px; }
        .login-right h2 { font-family:'DM Serif Display',serif; font-size:24px; color:white; margin-bottom:4px; }
        .login-right .sub { color:#888; font-size:13px; margin-bottom:30px; }
        .login-right label { color:#777; }
        .login-right input[type="text"], .login-right input[type="password"] { background:#2a2a3a; border-color:#3a3a4a; color:white; }
        .login-right input::placeholder { color:#555; }
        .login-right input:focus { border-color:var(--rose); background:#2e2e40; }
        .login-right .msg-error { background:rgba(200,80,80,0.15); border-color:#c06060; color:#e08080; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-left">
        <div class="brand">LogiTrack</div>
        <div class="brand-sub">Acceso interno</div>
        <div class="roles">
            <div class="role-item"><div class="role-dot"></div> Administrador</div>
            <div class="role-item"><div class="role-dot"></div> Empleado de sucursal</div>
            <div class="role-item"><div class="role-dot"></div> Chofer</div>
        </div>
    </div>
    <div class="login-right">
        <h2>Acceso al sistema</h2>
        <p class="sub">Solo personal autorizado</p>

        <?php if ($error): ?>
        <div class="msg-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" placeholder="Tu usuario interno" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary">Ingresar</button>
        </form>
    </div>
</div>
</body>
</html>
