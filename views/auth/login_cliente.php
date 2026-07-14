<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiTrack — Iniciar sesión</title>
    <link rel="stylesheet" href="/assets/estilo.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(145deg,#f0f0ff 0%,#e8e8ff 100%); padding:24px; }
        .login-wrap { display:flex; width:820px; min-height:520px; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(79,70,229,0.18); }
        .login-left { flex:1; background:linear-gradient(160deg,#1e1b4b,#4f46e5); display:flex; flex-direction:column; justify-content:center; align-items:flex-start; padding:52px 44px; color:white; }
        .login-left .brand-name { font-family:'DM Serif Display',serif; font-size:36px; margin-bottom:10px; line-height:1; }
        .login-left .brand-tag { font-size:13px; opacity:0.85; margin-bottom:40px; line-height:1.5; }
        .login-left .features { display:flex; flex-direction:column; gap:14px; }
        .feature-item { display:flex; align-items:center; gap:12px; background:rgba(255,255,255,0.12); border-radius:8px; padding:10px 14px; font-size:13px; }
        .feature-item i { font-size:15px; opacity:0.9; }
        .login-right { flex:1; background:white; display:flex; flex-direction:column; justify-content:center; padding:52px 44px; }
        .login-right h2 { font-family:'DM Serif Display',serif; font-size:26px; color:var(--text); margin-bottom:4px; }
        .login-right .sub { color:var(--text-soft); font-size:13px; margin-bottom:30px; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-left">
        <div class="brand-name">LogiTrack</div>
        <div class="brand-tag">Sistema de gestión de envíos<br>y logística integral</div>
        <div class="features">
            <div class="feature-item"><i class="fa-solid fa-box"></i> Creá y rastreá tus envíos</div>
            <div class="feature-item"><i class="fa-solid fa-truck"></i> Gestión de rutas y choferes</div>
            <div class="feature-item"><i class="fa-solid fa-shield-halved"></i> Acceso seguro por rol</div>
        </div>
    </div>
    <div class="login-right">
        <h2>Iniciar sesión</h2>
        <p class="sub">Ingresá tus datos para acceder al sistema</p>

        <?php if (!empty($_GET['recuperado'])): ?>
        <div class="msg-success">✓ Contraseña actualizada. Ya podés iniciar sesión.</div>
        <?php endif; ?>

        <?php if (!empty($_GET['baja'])): ?>
        <div class="msg-success">Tu cuenta fue dada de baja. Si querés reactivarla, contactá a un administrador.</div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="msg-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" placeholder="Tu nombre de usuario" required>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary">Ingresar al sistema</button>
        </form>

        <div style="text-align:right;margin-top:8px;margin-bottom:4px;">
            <a href="/cliente/router.php?pagina=recuperar" style="font-size:12px;color:var(--text-soft);text-decoration:none;">¿Olvidaste tu contraseña?</a>
        </div>

        <div class="divider"><hr><span>¿Sos cliente nuevo/a?</span><hr></div>
        <a href="/cliente/registro.php" class="btn-secondary">Crear cuenta de cliente</a>
    </div>
</div>
</body>
</html>
