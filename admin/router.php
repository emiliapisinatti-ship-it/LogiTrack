<?php
// admin/router.php — Punto de entrada único para el área admin/empleado/chofer

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$pagina = $_GET['pagina'] ?? '';

switch ($pagina) {

    case 'envios':
        require_once __DIR__ . '/../controllers/EnvioController.php';
        (new EnvioController($pdo))->listado();
        break;

    case 'gestionar_envios':
        require_once __DIR__ . '/../controllers/EnvioController.php';
        (new EnvioController($pdo))->gestionar();
        break;

    case 'editar_envio':
        require_once __DIR__ . '/../controllers/EnvioController.php';
        (new EnvioController($pdo))->editar();
        break;

    case 'vehiculos':
        require_once __DIR__ . '/../controllers/VehiculoController.php';
        (new VehiculoController($pdo))->listado();
        break;

    case 'viajes':
        require_once __DIR__ . '/../controllers/ViajeController.php';
        (new ViajeController($pdo))->listado();
        break;

    case 'crear_viaje':
        require_once __DIR__ . '/../controllers/ViajeController.php';
        (new ViajeController($pdo))->crear();
        break;

    case 'ver_viaje':
        require_once __DIR__ . '/../controllers/ViajeController.php';
        (new ViajeController($pdo))->ver();
        break;

    case 'editar_viaje':
        require_once __DIR__ . '/../controllers/ViajeController.php';
        (new ViajeController($pdo))->editar();
        break;

    case 'mis_viajes':
        require_once __DIR__ . '/../controllers/ViajeController.php';
        (new ViajeController($pdo))->misViajes();
        break;

    case 'incidentes':
        require_once __DIR__ . '/../controllers/IncidenteController.php';
        (new IncidenteController($pdo))->listado();
        break;

    case 'crear_incidente':
        require_once __DIR__ . '/../controllers/IncidenteController.php';
        (new IncidenteController($pdo))->crear();
        break;

    case 'editar_incidente':
        require_once __DIR__ . '/../controllers/IncidenteController.php';
        (new IncidenteController($pdo))->editar();
        break;

    case 'usuarios':
        require_once __DIR__ . '/../controllers/UsuarioController.php';
        (new UsuarioController($pdo))->listado();
        break;

    case 'crear_usuario':
        require_once __DIR__ . '/../controllers/UsuarioController.php';
        (new UsuarioController($pdo))->crear();
        break;

    case 'ver_usuario':
        require_once __DIR__ . '/../controllers/UsuarioController.php';
        (new UsuarioController($pdo))->ver();
        break;

    case 'sucursales':
        require_once __DIR__ . '/../controllers/SucursalController.php';
        (new SucursalController($pdo))->listado();
        break;

    case 'auditoria':
        require_once __DIR__ . '/../controllers/ReporteController.php';
        (new ReporteController($pdo))->auditoria();
        break;

    case 'reportes':
        require_once __DIR__ . '/../controllers/ReporteController.php';
        (new ReporteController($pdo))->index();
        break;

    case 'despacho':
        require_once __DIR__ . '/../controllers/DespachoController.php';
        (new DespachoController($pdo))->index();
        break;

    case 'logout':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController($pdo))->logout('/admin/login.php');
        break;

    default:
        header('Location: /admin/index.php');
        exit();
}
