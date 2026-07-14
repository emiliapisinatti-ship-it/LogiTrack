<?php
// controllers/VehiculoController.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Vehiculo.php';
require_once __DIR__ . '/../models/Auditoria.php';

class VehiculoController {

    private PDO $pdo;
    private Vehiculo $model;

    public function __construct(PDO $pdo) {
        $this->pdo   = $pdo;
        $this->model = new Vehiculo($pdo);
    }

    public function listado(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            header("Location: /admin/login.php"); exit();
        }

        $error   = '';
        $success = '';
        $edit    = null;

        $tipos     = $this->model->obtenerTipos();
        $sucursales = $this->pdo->query(
            "SELECT id_sucursal, nombre FROM sucursal WHERE activo = 1 ORDER BY nombre"
        )->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
            $accion = $_POST['accion'];

            // ── CREAR ───────────────────────────────────────────────
            if ($accion === 'crear') {
                $patente     = strtoupper(trim($_POST['patente']    ?? ''));
                $modelo      = trim($_POST['modelo']      ?? '');
                $id_tipo     = intval($_POST['id_tipo_veh'] ?? 0);
                $id_sucursal = intval($_POST['id_sucursal'] ?? 0) ?: null;

                if (!$patente || !$modelo || !$id_tipo) {
                    $error = 'Patente, modelo y tipo de vehículo son obligatorios.';
                } elseif ($this->model->obtenerPorPatente($patente)) {
                    $error = "Ya existe un vehículo con la patente <strong>$patente</strong>.";
                } else {
                    $this->pdo->prepare(
                        "INSERT INTO vehiculo (patente, modelo, id_tipo_veh, estado, id_sucursal)
                         VALUES (:pat, :mod, :tipo, 'Activo', :suc)"
                    )->execute([':pat' => $patente, ':mod' => $modelo, ':tipo' => $id_tipo, ':suc' => $id_sucursal]);
                    $aud = new Auditoria($this->pdo);
                    $aud->registrar('vehiculo', $patente, 'INSERT', $_SESSION['id_usuario'], null,
                        ['patente' => $patente, 'modelo' => $modelo, 'id_tipo_veh' => $id_tipo]);
                    $success = "Vehículo <strong>" . htmlspecialchars($patente) . "</strong> creado.";
                }

            // ── EDITAR ───────────────────────────────────────────────
            } elseif ($accion === 'editar') {
                $patente     = strtoupper(trim($_POST['patente']     ?? ''));
                $modelo      = trim($_POST['modelo']       ?? '');
                $id_tipo     = intval($_POST['id_tipo_veh']  ?? 0);
                $estado      = $_POST['estado'] ?? 'Activo';
                $id_sucursal = intval($_POST['id_sucursal']  ?? 0) ?: null;

                if (!$patente || !$modelo || !$id_tipo) {
                    $error = 'Todos los campos son obligatorios.';
                } else {
                    $viejo = $this->model->obtenerPorPatente($patente);
                    $this->pdo->prepare(
                        "UPDATE vehiculo SET modelo=:mod, id_tipo_veh=:tipo, estado=:est, id_sucursal=:suc
                         WHERE patente=:pat"
                    )->execute([':mod' => $modelo, ':tipo' => $id_tipo, ':est' => $estado,
                                ':suc' => $id_sucursal, ':pat' => $patente]);
                    $aud = new Auditoria($this->pdo);
                    $aud->registrar('vehiculo', $patente, 'UPDATE', $_SESSION['id_usuario'], $viejo ?: null,
                        ['modelo' => $modelo, 'id_tipo_veh' => $id_tipo, 'estado' => $estado]);
                    $success = "Vehículo <strong>" . htmlspecialchars($patente) . "</strong> actualizado.";
                }

            // ── BAJA ─────────────────────────────────────────────────
            } elseif ($accion === 'baja') {
                $patente = strtoupper(trim($_POST['patente'] ?? ''));
                if ($this->model->tieneViajeActivo($patente)) {
                    $error = 'No se puede dar de baja: el vehículo tiene un viaje activo.';
                } else {
                    $viejo = $this->model->obtenerPorPatente($patente);
                    $this->pdo->prepare(
                        "UPDATE vehiculo SET estado = 'Inactivo' WHERE patente = :p"
                    )->execute([':p' => $patente]);
                    $aud = new Auditoria($this->pdo);
                    $aud->registrar('vehiculo', $patente, 'UPDATE', $_SESSION['id_usuario'],
                        $viejo ?: null, ['estado' => 'Inactivo']);
                    $success = "Vehículo dado de baja.";
                }

            // ── REACTIVAR ────────────────────────────────────────────
            } elseif ($accion === 'activar') {
                $patente = strtoupper(trim($_POST['patente'] ?? ''));
                $this->pdo->prepare(
                    "UPDATE vehiculo SET estado = 'Activo' WHERE patente = :p"
                )->execute([':p' => $patente]);
                $success = "Vehículo reactivado.";

            // ── CARGAR FORM EDITAR ───────────────────────────────────
            } elseif ($accion === 'editar_form') {
                $edit = $this->model->obtenerPorPatente(strtoupper(trim($_POST['patente'] ?? '')));
            }
        }

        if (!$edit && isset($_GET['editar'])) {
            $edit = $this->model->obtenerPorPatente(strtoupper(trim($_GET['editar'])));
        }

        $filtro_sucursal = intval($_GET['sucursal'] ?? 0);
        $filtro_estado   = trim($_GET['estado'] ?? '');
        $filtro_tipo     = intval($_GET['tipo'] ?? 0);

        $vehiculos = $this->model->obtenerTodos($filtro_sucursal, $filtro_estado, $filtro_tipo);

        require_once __DIR__ . '/../views/admin/vehiculos/listado.php';
    }
}
