<?php
/**
 * views/layouts/header.php — Layout compartido para todas las áreas
 *
 * Variables esperadas:
 *   $page_subtitle  (string)
 *   $nav_links      (array)  — [['href' => '...', 'label' => '...']]
 *   $extra_css      (string) — CSS adicional (opcional)
 *   $logout_url     (string) — URL de cierre de sesión (opcional)
 *   $logout_label   (string) — Texto del botón de cierre de sesión (opcional)
 */
$_is_cliente = str_starts_with($_SERVER['REQUEST_URI'], '/cliente/');
$_logout_url   = $logout_url   ?? ($_is_cliente ? '/cliente/router.php?pagina=logout' : '/admin/router.php?pagina=logout');
$_logout_label = $logout_label ?? ($_is_cliente ? 'Salir' : 'Cerrar sesión');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiTrack — <?= htmlspecialchars($page_subtitle ?? 'Sistema') ?></title>
    <link rel="stylesheet" href="/assets/estilo.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php if (!empty($extra_css)): ?><style><?= $extra_css ?></style><?php endif; ?>
</head>
<body>
<header class="lt-header">
    <div class="brand">LogiTrack <span>/ <?= htmlspecialchars($page_subtitle ?? '') ?></span></div>
    <nav class="lt-nav">
        <?php foreach ($nav_links ?? [] as $link): ?>
            <a href="<?= htmlspecialchars($link['href']) ?>" class="<?= $link['class'] ?? '' ?>">
                <?= $link['label'] ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= $_logout_url ?>" class="btn-logout"><?= $_logout_label ?></a>
    </nav>
</header>
