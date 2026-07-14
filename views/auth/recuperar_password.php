<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiTrack — Recuperar contraseña</title>
    <link rel="stylesheet" href="/assets/estilo.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center;
               background:linear-gradient(145deg,#f0f0ff 0%,#e8e8ff 100%); padding:24px; }
        .wrap { display:flex; width:820px; min-height:460px; border-radius:16px; overflow:hidden;
                box-shadow:0 20px 60px rgba(79,70,229,0.18); }
        .left  { flex:1; background:linear-gradient(160deg,#1e1b4b,#4f46e5); display:flex;
                 flex-direction:column; justify-content:center; align-items:flex-start;
                 padding:52px 44px; color:white; }
        .left .brand  { font-family:'DM Serif Display',serif; font-size:36px; margin-bottom:10px; }
        .left .tag    { font-size:13px; opacity:0.85; margin-bottom:36px; line-height:1.5; }
        .step-list    { display:flex; flex-direction:column; gap:16px; }
        .step-item    { display:flex; align-items:flex-start; gap:14px; }
        .step-num     { width:26px; height:26px; border-radius:50%; display:flex; align-items:center;
                        justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; }
        .step-active  { background:#818cf8; color:#fff; }
        .step-pending { background:rgba(255,255,255,0.15); color:rgba(255,255,255,0.5); }
        .step-done    { background:#4ade80; color:#14532d; }
        .step-text    { padding-top:3px; }
        .step-text strong { display:block; font-size:13px; font-weight:600; color:#fff; }
        .step-text span   { font-size:11px; opacity:0.7; }
        .right { flex:1; background:white; display:flex; flex-direction:column;
                 justify-content:center; padding:52px 44px; }
        .right h2  { font-family:'DM Serif Display',serif; font-size:24px; color:var(--text); margin-bottom:4px; }
        .right .sub { color:var(--text-soft); font-size:13px; margin-bottom:28px; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:var(--text-soft);
                     font-size:13px; text-decoration:none; margin-top:20px; }
        .back-link:hover { color:var(--rose-dark); }
    </style>
</head>
<body>
<div class="wrap">

    <!-- Panel izquierdo -->
    <div class="left">
        <div class="brand">LogiTrack</div>
        <div class="tag">Recuperación de contraseña</div>
        <div class="step-list">
            <div class="step-item">
                <div class="step-num <?= $paso === 1 ? 'step-active' : 'step-done' ?>">
                    <?= $paso === 1 ? '1' : '✓' ?>
                </div>
                <div class="step-text">
                    <strong>Verificar identidad</strong>
                    <span>Usuario y número de DNI</span>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num <?= $paso === 2 ? 'step-active' : 'step-pending' ?>">2</div>
                <div class="step-text">
                    <strong>Nueva contraseña</strong>
                    <span>Elegí una contraseña segura</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="right">

        <?php if (($error ?? '') && isset($_GET['error']) && $_GET['error'] === 'sesion'): ?>
            <div class="msg-error">La sesión de recuperación expiró o no es válida. Empezá de nuevo.</div>
        <?php elseif (!empty($error)): ?>
            <div class="msg-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($paso === 1): ?>

            <h2>Verificá tu identidad</h2>
            <p class="sub">Ingresá tu nombre de usuario y tu DNI para continuar.</p>

            <form method="POST" action="/cliente/router.php?pagina=recuperar&paso=1">
                <div class="form-group">
                    <label>Nombre de usuario</label>
                    <input type="text" name="username" placeholder="Tu usuario de LogiTrack"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label>DNI</label>
                    <input type="text" name="dni" placeholder="Sin puntos ni espacios"
                           value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>" required>
                </div>
                <button type="submit" class="btn-primary">Verificar identidad</button>
            </form>

        <?php elseif ($paso === 2): ?>

            <h2>Nueva contraseña</h2>
            <p class="sub">Elegí una contraseña de al menos 6 caracteres.</p>

            <form method="POST" action="/cliente/router.php?pagina=recuperar&paso=2">
                <div class="form-group">
                    <label>Nueva contraseña</label>
                    <input type="password" name="nueva" placeholder="Mínimo 6 caracteres" required autofocus>
                </div>
                <div class="form-group">
                    <label>Confirmar contraseña</label>
                    <input type="password" name="confirma" placeholder="Repetí la contraseña" required>
                </div>
                <button type="submit" class="btn-primary">Guardar nueva contraseña</button>
            </form>

        <?php endif; ?>

        <a href="/cliente/login.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>

</div>
</body>
</html>
