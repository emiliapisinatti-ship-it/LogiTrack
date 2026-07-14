<?php
// controllers/SucursalController.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Auditoria.php';

class SucursalController {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listado(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            header("Location: /admin/login.php"); exit();
        }

        $error   = '';
        $success = '';
        $edit    = null;

        // Detectar columnas de la tabla
        $cols = array_column($this->pdo->query("SHOW COLUMNS FROM sucursal")->fetchAll(), 'Field');
        $tiene_direccion = in_array('direccion', $cols);

        $localidades = $this->pdo->query(
            "SELECT l.id_localidad, l.nombre, p.nombre AS provincia
             FROM localidad l LEFT JOIN provincia p ON p.id_provincia = l.id_provincia
             ORDER BY p.nombre, l.nombre"
        )->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
            $accion = $_POST['accion'];

            if ($accion === 'crear') {
                $nombre       = trim($_POST['nombre']    ?? '');
                $direccion    = $tiene_direccion ? (trim($_POST['direccion'] ?? '') ?: null) : null;
                $telefono     = trim($_POST['telefono'] ?? '') ?: null;
                $id_localidad = intval($_POST['id_localidad'] ?? 0) ?: null;
                if (!$nombre) {
                    $error = 'El nombre es obligatorio.';
                } else {
                    $chk = $this->pdo->prepare("SELECT 1 FROM sucursal WHERE nombre = :n");
                    $chk->execute([':n' => $nombre]);
                    if ($chk->fetch()) {
                        $error = 'Ya existe una sucursal con ese nombre.';
                    }
                }
                if (!$error) {
                    $sql = $tiene_direccion
                        ? "INSERT INTO sucursal (nombre, direccion, telefono, id_localidad) VALUES (:n, :d, :t, :l)"
                        : "INSERT INTO sucursal (nombre, telefono, id_localidad) VALUES (:n, :t, :l)";
                    $params = $tiene_direccion
                        ? [':n' => $nombre, ':d' => $direccion, ':t' => $telefono, ':l' => $id_localidad]
                        : [':n' => $nombre, ':t' => $telefono, ':l' => $id_localidad];
                    $this->pdo->prepare($sql)->execute($params);
                    $nuevo_id = $this->pdo->lastInsertId();
                    $aud = new Auditoria($this->pdo);
                    $aud->registrar('sucursal', $nuevo_id, 'INSERT', $_SESSION['id_usuario'], null,
                        ['nombre' => $nombre, 'direccion' => $direccion, 'telefono' => $telefono, 'id_localidad' => $id_localidad]);
                    $success = "Sucursal <strong>" . htmlspecialchars($nombre) . "</strong> creada.";
                }

            } elseif ($accion === 'editar') {
                $id           = intval($_POST['id_sucursal']);
                $nombre       = trim($_POST['nombre']    ?? '');
                $direccion    = $tiene_direccion ? (trim($_POST['direccion'] ?? '') ?: null) : null;
                $telefono     = trim($_POST['telefono'] ?? '') ?: null;
                $id_localidad = intval($_POST['id_localidad'] ?? 0) ?: null;
                if (!$nombre) {
                    $error = 'El nombre es obligatorio.';
                } else {
                    $chk = $this->pdo->prepare("SELECT 1 FROM sucursal WHERE nombre = :n AND id_sucursal != :id");
                    $chk->execute([':n' => $nombre, ':id' => $id]);
                    if ($chk->fetch()) {
                        $error = 'Ya existe una sucursal con ese nombre.';
                    }
                }
                if (!$error) {
                    $sql = $tiene_direccion
                        ? "UPDATE sucursal SET nombre=:n, direccion=:d, telefono=:t, id_localidad=:l WHERE id_sucursal=:id"
                        : "UPDATE sucursal SET nombre=:n, telefono=:t, id_localidad=:l WHERE id_sucursal=:id";
                    $params = $tiene_direccion
                        ? [':n' => $nombre, ':d' => $direccion, ':t' => $telefono, ':l' => $id_localidad, ':id' => $id]
                        : [':n' => $nombre, ':t' => $telefono, ':l' => $id_localidad, ':id' => $id];
                    $this->pdo->prepare($sql)->execute($params);
                    $aud = new Auditoria($this->pdo);
                    $aud->registrar('sucursal', $id, 'UPDATE', $_SESSION['id_usuario'], null,
                        ['nombre' => $nombre, 'direccion' => $direccion, 'telefono' => $telefono, 'id_localidad' => $id_localidad]);
                    $success = "Sucursal actualizada.";
                }

            } elseif ($accion === 'baja') {
                $id  = intval($_POST['id_sucursal']);
                $uso = $this->pdo->prepare("SELECT COUNT(*) FROM envio WHERE id_suc_origen=:id OR id_suc_destino=:id2");
                $uso->execute([':id' => $id, ':id2' => $id]);
                if ($uso->fetchColumn() > 0) {
                    $error = 'No se puede dar de baja: la sucursal tiene envíos asociados.';
                } else {
                    $this->pdo->prepare("UPDATE sucursal SET activo = 0 WHERE id_sucursal = :id")->execute([':id' => $id]);
                    $aud = new Auditoria($this->pdo);
                    $aud->registrar('sucursal', $id, 'UPDATE', $_SESSION['id_usuario'], ['activo' => 1], ['activo' => 0]);
                    $success = "Sucursal dada de baja correctamente.";
                }

            } elseif ($accion === 'activar') {
                $id = intval($_POST['id_sucursal']);
                $this->pdo->prepare("UPDATE sucursal SET activo = 1 WHERE id_sucursal = :id")->execute([':id' => $id]);
                $aud = new Auditoria($this->pdo);
                $aud->registrar('sucursal', $id, 'UPDATE', $_SESSION['id_usuario'], ['activo' => 0], ['activo' => 1]);
                $success = "Sucursal reactivada correctamente.";

            } elseif ($accion === 'editar_form') {
                $id   = intval($_POST['id_sucursal']);
                $stmt = $this->pdo->prepare("SELECT * FROM sucursal WHERE id_sucursal = :id");
                $stmt->execute([':id' => $id]);
                $edit = $stmt->fetch();
            }
        }

        // Cargar sucursal a editar via GET
        if (!$edit && isset($_GET['editar'])) {
            $id   = intval($_GET['editar']);
            $stmt = $this->pdo->prepare("SELECT * FROM sucursal WHERE id_sucursal = :id");
            $stmt->execute([':id' => $id]);
            $edit = $stmt->fetch() ?: null;
        }

        $sucursales = $this->pdo->query("SELECT * FROM sucursal ORDER BY activo DESC, nombre")->fetchAll();

        require_once __DIR__ . '/../views/admin/sucursales/listado.php';
    }
}
