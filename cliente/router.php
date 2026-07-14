<?php
// cliente/router.php — Punto de entrada único para el área de clientes

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pagina = $_GET['pagina'] ?? '';

switch ($pagina) {

    case 'mis_envios':
        require_once __DIR__ . '/../controllers/EnvioController.php';
        (new EnvioController($pdo))->misEnvios();
        break;

    case 'rastrear':
        require_once __DIR__ . '/../controllers/EnvioController.php';
        (new EnvioController($pdo))->rastrear();
        break;

    case 'enviar':
        require_once __DIR__ . '/../controllers/EnvioController.php';
        (new EnvioController($pdo))->enviarPaquete();
        break;

    case 'perfil':
        require_once __DIR__ . '/../controllers/UsuarioController.php';
        (new UsuarioController($pdo))->perfil();
        break;

    case 'recuperar':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController($pdo))->recuperarPassword();
        break;

    case 'logout':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController($pdo))->logout('/cliente/login.php');
        break;

    default:
        header('Location: /cliente/index.php');
        exit();
}
