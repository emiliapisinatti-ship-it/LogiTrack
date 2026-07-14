<?php
// controllers/UsuarioController.php — CRUD de usuarios (admin)

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Auditoria.php';

class UsuarioController {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private function soloAdmin(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            header("Location: /admin/login.php"); exit();
        }
    }

    // ─── LISTADO ─────────────────────────────────────────────────

    public function listado(): void {
        $this->soloAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
            $toggle_id    = intval($_POST['toggle_id']);
            $nuevo_estado = intval($_POST['nuevo_estado']);
            $this->pdo->prepare("UPDATE usuario SET estado = :estado, fecha_baja = :fb WHERE id_usuario = :id")
                ->execute([':estado' => $nuevo_estado, ':fb' => $nuevo_estado ? null : date('Y-m-d H:i:s'), ':id' => $toggle_id]);
            $qs = isset($_GET['rol']) ? '&rol=' . intval($_GET['rol']) : '';
            header("Location: /admin/router.php?pagina=usuarios$qs"); exit();
        }

        $filtro = isset($_GET['rol']) ? intval($_GET['rol']) : 0;

        $sql = "SELECT u.id_usuario, u.username, u.estado, u.legajo, u.dni_cliente,
                       r.nombre AS rol,
                       COALESCE(e.nombre, c.nombre)     AS nombre,
                       COALESCE(e.apellido, c.apellido) AS apellido
                FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN empleado e ON e.legajo     = u.legajo
                LEFT JOIN cliente  c ON c.dni        = u.dni_cliente";
        if ($filtro > 0) {
            $sql .= " WHERE u.id_rol = :filtro ORDER BY apellido, nombre";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':filtro' => $filtro]);
        } else {
            $sql .= " ORDER BY u.id_rol, apellido, nombre";
            $stmt = $this->pdo->query($sql);
        }
        $usuarios = $stmt->fetchAll();

        $busqueda = strtolower(trim($_GET['q'] ?? ''));
        if ($busqueda) {
            $usuarios = array_filter($usuarios, fn($u) =>
                str_contains(strtolower($u['nombre']     ?? ''), $busqueda)
                || str_contains(strtolower($u['apellido']   ?? ''), $busqueda)
                || str_contains(strtolower($u['username']   ?? ''), $busqueda)
                || str_contains(strtolower($u['legajo']     ?? ''), $busqueda)
                || str_contains(strtolower($u['dni_cliente'] ?? ''), $busqueda)
            );
        }

        $totales_stmt = $this->pdo->query(
            "SELECT r.id_rol, r.nombre AS rol, COUNT(u.id_usuario) AS cantidad
             FROM rol r LEFT JOIN usuario u ON u.id_rol = r.id_rol GROUP BY r.id_rol, r.nombre"
        );
        $totales_raw = $totales_stmt->fetchAll();
        $totales = [];
        $totales['todos'] = 0;
        foreach ($totales_raw as $row) {
            $totales[$row['id_rol']] = $row['cantidad'];
            $totales['todos'] += $row['cantidad'];
        }

        require_once __DIR__ . '/../views/admin/usuarios/listado.php';
    }

    // ─── CREAR ───────────────────────────────────────────────────

    public function crear(): void {
        $this->soloAdmin();

        $error   = "";
        $success = "";

        $sucursales = $this->pdo->query("SELECT id_sucursal, nombre FROM sucursal WHERE activo=1 ORDER BY nombre")->fetchAll();
        $licencias  = $this->pdo->query("SELECT id_licencia, codigo, descripcion FROM tipolicencia ORDER BY codigo")->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre      = trim($_POST['nombre']      ?? '');
            $apellido    = trim($_POST['apellido']    ?? '');
            $dni         = trim($_POST['dni']         ?? '');
            $username    = trim($_POST['username']    ?? '');
            $password    = trim($_POST['password']    ?? '');
            $password2   = trim($_POST['password2']   ?? '');
            $id_rol      = intval($_POST['id_rol']    ?? 0);
            $id_sucursal = intval($_POST['id_sucursal'] ?? 0);
            $telefono    = trim($_POST['telefono']    ?? '');
            $id_licencia = intval($_POST['id_licencia'] ?? 0) ?: null;

            if ($id_rol == 2) {
                $res    = $this->pdo->query("SELECT legajo FROM empleado WHERE legajo LIKE 'EMP-%' ORDER BY legajo DESC LIMIT 1")->fetch();
                $num    = $res ? intval(substr($res['legajo'], 4)) + 1 : 1;
                $legajo = 'EMP-' . str_pad($num, 3, '0', STR_PAD_LEFT);
            } else {
                $res    = $this->pdo->query("SELECT legajo FROM empleado WHERE legajo LIKE 'CH-%' ORDER BY legajo DESC LIMIT 1")->fetch();
                $num    = $res ? intval(substr($res['legajo'], 3)) + 1 : 1;
                $legajo = 'CH-' . str_pad($num, 3, '0', STR_PAD_LEFT);
            }

            if (empty($nombre) || empty($apellido) || empty($dni) || empty($username) || empty($password) || !$id_sucursal) {
                $error = "Completá todos los campos obligatorios.";
            } elseif ($id_rol == 3 && !$id_licencia) {
                $error = "El chofer debe tener un tipo de licencia asignado.";
            } elseif ($password !== $password2) {
                $error = "Las contraseñas no coinciden.";
            } elseif (strlen($password) < 6) {
                $error = "La contraseña debe tener al menos 6 caracteres.";
            } elseif (!in_array($id_rol, [2,3])) {
                $error = "Rol inválido.";
            } else {
                $check = $this->pdo->prepare("SELECT id_usuario FROM usuario WHERE username = :u");
                $check->execute([':u' => $username]);
                $checkDni = $this->pdo->prepare("SELECT legajo FROM empleado WHERE dni = :d");
                $checkDni->execute([':d' => $dni]);
                if ($check->fetch()) {
                    $error = "Ese nombre de usuario ya está en uso.";
                } elseif ($checkDni->fetch()) {
                    $error = "Ya existe un empleado registrado con ese DNI.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    //crear empleado
                    try {
                        $this->pdo->beginTransaction();
                        $this->pdo->prepare(
                            "INSERT INTO empleado (legajo, dni, nombre, apellido, telefono, fecha_ingreso, id_sucursal, id_rol)
                             VALUES (:legajo, :dni, :nombre, :apellido, :telefono, :fecha, :suc, :rol)"
                        )->execute([
                            ':legajo' => $legajo, ':dni' => $dni, ':nombre' => $nombre,
                            ':apellido' => $apellido, ':telefono' => $telefono ?: null,
                            ':fecha' => date('Y-m-d'), ':suc' => $id_sucursal, ':rol' => $id_rol,
                        ]);
                        if ($id_rol == 3) {
                            $this->pdo->prepare("INSERT INTO datos_chofer (legajo, id_licencia) VALUES (:legajo, :licencia)")
                                ->execute([':legajo' => $legajo, ':licencia' => $id_licencia]);
                        }
                        $this->pdo->prepare(
                            "INSERT INTO usuario (username, password_hash, id_rol, legajo, estado) VALUES (:user, :hash, :rol, :legajo, 1)"
                        )->execute([':user' => $username, ':hash' => $hash, ':rol' => $id_rol, ':legajo' => $legajo]);
                        $this->pdo->commit();
                        $success = "Usuario <strong>" . htmlspecialchars($username) . "</strong> creado correctamente.";
                    } catch (PDOException $e) {
                        $this->pdo->rollBack();
                        error_log("UsuarioController::crear — " . $e->getMessage());
                        $error = "Error al crear el usuario: " . $e->getMessage();
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/admin/usuarios/crear.php';
    }

    // ─── VER / EDITAR EMPLEADO ───────────────────────────────────

    public function ver(): void {
        $this->soloAdmin();

        $id = intval($_GET['id'] ?? 0);
        if (!$id) { header("Location: /admin/router.php?pagina=usuarios"); exit(); }

        $error   = "";
        $success = "";
        $sucursales = $this->pdo->query("SELECT id_sucursal, nombre FROM sucursal WHERE activo=1 ORDER BY nombre")->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'toggle_estado') {
            $nuevo_estado = intval($_POST['nuevo_estado']);
            $this->pdo->prepare("UPDATE usuario SET estado = :estado, fecha_baja = :fb WHERE id_usuario = :id")
                ->execute([':estado' => $nuevo_estado, ':fb' => $nuevo_estado ? null : date('Y-m-d H:i:s'), ':id' => $id]);
            $success = $nuevo_estado ? "Usuario reactivado." : "Usuario desactivado.";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
            $nombre   = trim($_POST['nombre']   ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $id_suc   = intval($_POST['id_sucursal'] ?? 0) ?: null;
            $password = trim($_POST['password'] ?? '');
            $password2= trim($_POST['password2'] ?? '');

            if (empty($nombre) || empty($apellido)) {
                $error = "Nombre y apellido son obligatorios.";
            } elseif (!empty($password) && $password !== $password2) {
                $error = "Las contraseñas no coinciden.";
            } elseif (!empty($password) && strlen($password) < 6) {
                $error = "La contraseña debe tener al menos 6 caracteres.";
            } else {
                try {
                    $uData = $this->pdo->prepare("SELECT legajo FROM usuario WHERE id_usuario = :id");
                    $uData->execute([':id' => $id]);
                    $u = $uData->fetch();
                    if ($u['legajo']) {
                        $this->pdo->prepare("UPDATE empleado SET nombre = :nombre, apellido = :apellido WHERE legajo = :legajo")
                            ->execute([':nombre' => $nombre, ':apellido' => $apellido, ':legajo' => $u['legajo']]);
                    }
                    if (!empty($password)) {
                        $this->pdo->prepare("UPDATE usuario SET password_hash = :hash WHERE id_usuario = :id")
                            ->execute([':hash' => password_hash($password, PASSWORD_DEFAULT), ':id' => $id]);
                    }
                    $success = "Datos actualizados correctamente.";
                } catch (PDOException $e) {
                    $error = "Error al editar: " . $e->getMessage();
                }
            }
        }

        $stmt = $this->pdo->prepare(
            "SELECT u.*, r.nombre AS rol,
                    COALESCE(e.nombre, c.nombre)     AS nombre,
                    COALESCE(e.apellido, c.apellido) AS apellido,
                    e.id_sucursal, e.telefono, e.fecha_ingreso, e.dni AS dni_emp,
                    s.nombre AS sucursal_nombre,
                    dc.id_licencia, tl.codigo AS licencia_codigo,
                    c.email
             FROM usuario u
             JOIN rol r ON r.id_rol = u.id_rol
             LEFT JOIN empleado e ON e.legajo = u.legajo
             LEFT JOIN cliente  c ON c.dni    = u.dni_cliente
             LEFT JOIN sucursal s ON s.id_sucursal = e.id_sucursal
             LEFT JOIN datos_chofer dc ON dc.legajo = u.legajo
             LEFT JOIN tipolicencia tl ON tl.id_licencia = dc.id_licencia
             WHERE u.id_usuario = :id"
        );
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch();
        if (!$usuario) { header("Location: /admin/router.php?pagina=usuarios"); exit(); }

        require_once __DIR__ . '/../views/admin/usuarios/ver.php';
    }

    // ─── MI PERFIL (cliente) ─────────────────────────────────────

    public function perfil(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
            header("Location: /cliente/login.php"); exit();
        }

        $stmt_dni = $this->pdo->prepare("SELECT dni_cliente, username FROM usuario WHERE id_usuario = :id");
        $stmt_dni->execute([':id' => $_SESSION['id_usuario']]);
        $u_actual = $stmt_dni->fetch();
        $dni = $u_actual['dni_cliente'] ?? null;
        if (!$dni) { header("Location: /cliente/index.php"); exit(); }

        $error   = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
            $nombre   = trim($_POST['nombre']   ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $email    = trim($_POST['email']    ?? '');
            $username = trim($_POST['username'] ?? '');

            $check = $this->pdo->prepare("SELECT id_usuario FROM usuario WHERE username = :u AND id_usuario != :id");
            $check->execute([':u' => $username, ':id' => $_SESSION['id_usuario']]);

            if (empty($nombre) || empty($apellido) || empty($username)) {
                $error = "Nombre, apellido y usuario son obligatorios.";
            } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "El formato del email no es válido.";
            } elseif ($check->fetch()) {
                $error = "Ese nombre de usuario ya está en uso.";
            } else {
                $stmt_old = $this->pdo->prepare("SELECT nombre, apellido, telefono, email FROM cliente WHERE dni = :dni");
                $stmt_old->execute([':dni' => $dni]);
                $old = $stmt_old->fetch();

                $this->pdo->prepare(
                    "UPDATE cliente SET nombre=:n, apellido=:a, telefono=:t, email=:e WHERE dni=:dni"
                )->execute([':n' => $nombre, ':a' => $apellido, ':t' => $telefono ?: null, ':e' => $email ?: null, ':dni' => $dni]);

                $aud = new Auditoria($this->pdo);
                $aud->registrar('cliente', $dni, 'UPDATE', $_SESSION['id_usuario'],
                    $old,
                    ['nombre' => $nombre, 'apellido' => $apellido, 'telefono' => $telefono ?: null, 'email' => $email ?: null]);

                if ($username !== $u_actual['username']) {
                    $this->pdo->prepare("UPDATE usuario SET username=:u WHERE id_usuario=:id")
                        ->execute([':u' => $username, ':id' => $_SESSION['id_usuario']]);
                    $_SESSION['username'] = $username;
                }

                $success = "Datos actualizados correctamente.";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'baja' && isset($_POST['confirmar'])) {
            $this->pdo->prepare("UPDATE usuario SET estado = 0, fecha_baja = :fb WHERE id_usuario = :id")
                ->execute([':fb' => date('Y-m-d H:i:s'), ':id' => $_SESSION['id_usuario']]);
            session_destroy();
            header("Location: /cliente/login.php?baja=1"); exit();
        }

        $stmt = $this->pdo->prepare("SELECT * FROM cliente WHERE dni = :dni");
        $stmt->execute([':dni' => $dni]);
        $cliente = $stmt->fetch();
        $cliente['username'] = $_SESSION['username'];

        require_once __DIR__ . '/../views/cliente/perfil.php';
    }
}
