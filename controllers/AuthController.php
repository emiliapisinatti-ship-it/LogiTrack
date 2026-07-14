<?php
// controllers/AuthController.php — Autenticación (login/logout/registro)

require_once __DIR__ . '/../config/db.php';

class AuthController {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ─── LOGIN CLIENTE (rol 4) ────────────────────────────────────

    public function loginCliente(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['id_usuario'])) {
            header("Location: /cliente/index.php"); exit();
        }

        $error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = "Completá todos los campos.";
            } else {
                $stmt = $this->pdo->prepare(
                    "SELECT id_usuario, username, password_hash, id_rol
                     FROM Usuario WHERE username = :u AND estado = 1"
                );
                $stmt->execute([':u' => $username]);
                $usuario = $stmt->fetch();

                if ($usuario && password_verify($password, $usuario['password_hash'])) {
                    if ($usuario['id_rol'] != 4) {
                        $error = "Este acceso es solo para clientes.";
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['id_usuario']    = $usuario['id_usuario'];
                        $_SESSION['username']      = $usuario['username'];
                        $_SESSION['id_rol']        = $usuario['id_rol'];
                        $_SESSION['ultimo_acceso'] = time();
                        header("Location: /cliente/index.php"); exit();
                    }
                } else {
                    $error = "Usuario o contraseña incorrectos.";
                }
            }
        }

        require_once __DIR__ . '/../views/auth/login_cliente.php';
    }

    // ─── LOGIN INTERNO (roles 1,2,3) ─────────────────────────────

    public function loginAdmin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['id_usuario']) && in_array($_SESSION['id_rol'], [1,2,3])) {
            header("Location: /admin/index.php"); exit();
        }

        $error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $error = "Completá todos los campos.";
            } else {
                $stmt = $this->pdo->prepare(
                    "SELECT id_usuario, username, password_hash, id_rol
                     FROM Usuario WHERE username = :u AND estado = 1"
                );
                $stmt->execute([':u' => $username]);
                $usuario = $stmt->fetch();

                if (!$usuario) {
                    $error = "Usuario no encontrado o inactivo.";
                } elseif (!in_array($usuario['id_rol'], [1,2,3])) {
                    $error = "Este acceso es solo para personal interno.";
                } elseif (password_verify($password, $usuario['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['id_usuario']    = $usuario['id_usuario'];
                    $_SESSION['username']      = $usuario['username'];
                    $_SESSION['id_rol']        = $usuario['id_rol'];
                    $_SESSION['ultimo_acceso'] = time();
                    header("Location: /admin/index.php"); exit();
                } else {
                    $error = "Contraseña incorrecta.";
                }
            }
        }

        require_once __DIR__ . '/../views/auth/login_admin.php';
    }

    // ─── RECUPERAR CONTRASEÑA (cliente) ─────────────────────────

    public function recuperarPassword(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['id_usuario'])) {
            header("Location: /cliente/index.php"); exit();
        }

        $error   = '';
        $success = '';
        $paso    = intval($_GET['paso'] ?? 1);

        // Paso 1: verificar identidad (usuario + DNI)
        if ($paso === 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = trim($_POST['username'] ?? '');
                $dni      = trim($_POST['dni']      ?? '');

                if (!$username || !$dni) {
                    $error = 'Completá todos los campos.';
                } else {
                    $stmt = $this->pdo->prepare(
                        "SELECT u.id_usuario FROM usuario u
                         JOIN cliente c ON c.dni = u.dni_cliente
                         WHERE u.username = :user AND c.dni = :dni
                           AND u.id_rol = 4 AND u.estado = 1"
                    );
                    $stmt->execute([':user' => $username, ':dni' => $dni]);
                    $row = $stmt->fetch();

                    if ($row) {
                        $_SESSION['recuperar_uid']  = $row['id_usuario'];
                        $_SESSION['recuperar_time'] = time();
                        header("Location: /cliente/router.php?pagina=recuperar&paso=2");
                        exit();
                    } else {
                        $error = 'No encontramos una cuenta con ese usuario y DNI.';
                    }
                }
            }
        }

        // Paso 2: ingresar nueva contraseña
        if ($paso === 2) {
            // Verificar que venga del paso 1 y que no haya pasado más de 15 min
            if (empty($_SESSION['recuperar_uid']) || (time() - ($_SESSION['recuperar_time'] ?? 0)) > 900) {
                unset($_SESSION['recuperar_uid'], $_SESSION['recuperar_time']);
                header("Location: /cliente/router.php?pagina=recuperar&error=sesion");
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nueva    = trim($_POST['nueva']    ?? '');
                $confirma = trim($_POST['confirma'] ?? '');

                if (strlen($nueva) < 6) {
                    $error = 'La contraseña debe tener al menos 6 caracteres.';
                } elseif ($nueva !== $confirma) {
                    $error = 'Las contraseñas no coinciden.';
                } else {
                    $hash = password_hash($nueva, PASSWORD_DEFAULT);
                    $this->pdo->prepare("UPDATE usuario SET password_hash = :h WHERE id_usuario = :id")
                              ->execute([':h' => $hash, ':id' => $_SESSION['recuperar_uid']]);

                    unset($_SESSION['recuperar_uid'], $_SESSION['recuperar_time']);
                    header("Location: /cliente/login.php?recuperado=1");
                    exit();
                }
            }
        }

        require_once __DIR__ . '/../views/auth/recuperar_password.php';
    }

    // ─── LOGOUT ──────────────────────────────────────────────────

    public function logout(string $redirect = '/cliente/login.php'): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Location: $redirect");
        exit();
    }

    // ─── REGISTRO CLIENTE ─────────────────────────────────────────

    public function registro(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['id_usuario'])) {
            header("Location: /cliente/index.php"); exit();
        }

        $error      = "";
        $success    = "";
        $localidades = $this->pdo->query("SELECT id_localidad, nombre, id_provincia FROM Localidad ORDER BY id_provincia, nombre")->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username    = trim($_POST['username']    ?? '');
            $password    = trim($_POST['password']    ?? '');
            $password2   = trim($_POST['password2']   ?? '');
            $nombre      = trim($_POST['nombre']      ?? '');
            $apellido    = trim($_POST['apellido']    ?? '');
            $dni         = trim($_POST['dni']         ?? '');
            $email       = trim($_POST['email']       ?? '');
            $telefono    = trim($_POST['telefono']    ?? '');
            $id_localidad = intval($_POST['id_localidad'] ?? 0) ?: null;

            if (empty($username) || empty($password) || empty($nombre) || empty($apellido) || empty($dni)) {
                $error = "Completá todos los campos obligatorios.";
            } elseif (!preg_match('/^\d{7,10}$/', $dni)) {
                $error = "El DNI debe contener solo números (7-10 dígitos).";
            } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "El formato del email no es válido.";
            } elseif ($password !== $password2) {
                $error = "Las contraseñas no coinciden.";
            } elseif (strlen($password) < 6) {
                $error = "La contraseña debe tener al menos 6 caracteres.";
            } else {
                $check = $this->pdo->prepare("SELECT id_usuario FROM Usuario WHERE username = :u");
                $check->execute([':u' => $username]);
                if ($check->fetch()) {
                    $error = "Ese nombre de usuario ya está en uso.";
                } else {
                    $checkDni = $this->pdo->prepare("SELECT id_usuario FROM Usuario WHERE dni_cliente = :dni");
                    $checkDni->execute([':dni' => $dni]);
                    if ($checkDni->fetch()) {
                        $error = "Ya existe una cuenta con ese DNI.";
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        //crea-actualiza cliente 
                        try {
                            $this->pdo->beginTransaction();

                            $existeCliente = $this->pdo->prepare("SELECT dni FROM Cliente WHERE dni = :dni");
                            $existeCliente->execute([':dni' => $dni]);

                            if (!$existeCliente->fetch()) {
                                $this->pdo->prepare(
                                    "INSERT INTO Cliente (dni, nombre, apellido, email, telefono, id_localidad)
                                     VALUES (:dni, :nombre, :apellido, :email, :telefono, :localidad)"
                                )->execute([
                                    ':dni' => $dni, ':nombre' => $nombre, ':apellido' => $apellido,
                                    ':email' => $email ?: null, ':telefono' => $telefono ?: null,
                                    ':localidad' => $id_localidad,
                                ]);
                            } elseif (!empty($email) || !empty($telefono) || $id_localidad) {
                                $this->pdo->prepare(
                                    "UPDATE Cliente SET
                                     email        = COALESCE(NULLIF(:email,''), email),
                                     telefono     = COALESCE(NULLIF(:telefono,''), telefono),
                                     id_localidad = COALESCE(:localidad, id_localidad)
                                     WHERE dni = :dni"
                                )->execute([':email' => $email, ':telefono' => $telefono, ':localidad' => $id_localidad, ':dni' => $dni]);
                            }

                            $this->pdo->prepare(
                                "INSERT INTO Usuario (username, password_hash, id_rol, dni_cliente, estado)
                                 VALUES (:user, :hash, 4, :dni, 1)"
                            )->execute([':user' => $username, ':hash' => $hash, ':dni' => $dni]);

                            $this->pdo->commit();
                            $success = "¡Cuenta creada! Ya podés iniciar sesión.";
                        } catch (PDOException $e) {
                            $this->pdo->rollBack();
                            error_log("AuthController::registro — " . $e->getMessage());
                            $error = "Error al crear la cuenta. Intentá de nuevo.";
                        }
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/auth/registro.php';
    }
}
