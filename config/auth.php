<?php
// config/auth.php — Verificación de sesión y timeout (unifica los dos auth_check anteriores)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timeout de sesión: 30 minutos de inactividad
$TIMEOUT = 30 * 60;
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $TIMEOUT) {
    $rol_expirado = $_SESSION['id_rol'] ?? 0;
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    $redirect = in_array($rol_expirado, [1, 2, 3]) ? '/admin/login.php' : '/cliente/login.php';
    header("Location: $redirect");
    exit();
}
$_SESSION['ultimo_acceso'] = time();

/**
 * Verifica que el usuario tenga sesión activa.
 * $roles: array de roles permitidos (vacío = cualquier rol logueado).
 * $redirect: URL a la que redirigir si falla la verificación.
 */
function requireAuth(array $roles = [], string $redirect = '/cliente/login.php'): void
{
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: $redirect");
        exit();
    }
    if (!empty($roles) && !in_array($_SESSION['id_rol'], $roles)) {
        header("Location: $redirect");
        exit();
    }
}