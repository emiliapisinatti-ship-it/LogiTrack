<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiTrack — Registro de Cliente</title>
    <link rel="stylesheet" href="/assets/estilo.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(145deg,#f0f0ff 0%,#e8e8ff 100%); padding:30px 16px; }
        .card { background:white; border-radius:16px; padding:44px 40px; width:100%; max-width:480px; box-shadow:0 12px 40px rgba(79,70,229,0.12); border:1px solid var(--border); }
        .reg-header { text-align:center; margin-bottom:28px; }
        .reg-header .logo { font-size:28px; color:var(--rose-dark); margin-bottom:4px; }
        .reg-header h2 { font-family:'DM Serif Display',serif; font-size:24px; color:var(--text); margin-top:8px; }
        .reg-header p { color:var(--text-soft); font-size:13px; margin-top:4px; }
        .link-login { text-align:center; margin-top:20px; font-size:13px; color:var(--text-soft); }
        .link-login a { color:var(--rose-dark); text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
<div class="card">
    <div class="reg-header">
        <div class="logo"><i class="fa-solid fa-truck-fast"></i></div>
        <h2>Crear cuenta de cliente</h2>
        <p>Registrate para hacer seguimiento de tus envíos</p>
    </div>

    <?php if ($error): ?>
        <div class="msg-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="msg-success">✅ <?= htmlspecialchars($success) ?> <a href="/cliente/login.php" style="color:#3a9060">Ir al login →</a></div>
    <?php endif; ?>

    <form method="post">
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
            <label>Usuario *</label>
            <input type="text" name="username" placeholder="mi_usuario"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="correo@ejemplo.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" placeholder="Opcional"
                   value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Localidad</label>
            <select name="id_localidad">
                <option value="">— Sin especificar —</option>
                <?php foreach ($localidades as $loc): ?>
                    <option value="<?= $loc['id_localidad'] ?>"
                        <?= (int)($_POST['id_localidad'] ?? 0) === (int)$loc['id_localidad'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Contraseña *</label>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="form-group">
                <label>Repetir contraseña *</label>
                <input type="password" name="password2" placeholder="Repetir" required>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">Crear cuenta</button>
    </form>

    <div class="link-login">
        ¿Ya tenés cuenta? <a href="/cliente/login.php">Iniciar sesión</a>
    </div>
</div>
</body>
</html>
