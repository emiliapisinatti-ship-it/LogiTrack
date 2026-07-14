<?php
// controllers/IncidenteController.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Incidente.php';

class IncidenteController {

    private PDO $pdo;
    private Incidente $model;

    public function __construct(PDO $pdo) {
        $this->pdo   = $pdo;
        $this->model = new Incidente($pdo);
    }

    public function listado(): void {
        if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1,2,3])) {
            header("Location: /admin/login.php"); exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar'])) {
            if (!in_array($_SESSION['id_rol'], [1, 2])) {
                header("Location: /admin/router.php?pagina=incidentes"); exit();
            }
            $id  = intval($_POST['cerrar']);
            $obs = trim($_POST['obs_resolucion'] ?? '');
            $this->pdo->prepare(
                "UPDATE incidente SET estado='cerrado', fecha_resolucion=NOW(), resuelto_por=:uid,
                 descripcion = IF(:obs != '', CONCAT(descripcion, '\n\n[Resolución: ', :obs2, ']'), descripcion)
                 WHERE nro_incidente = :id"
            )->execute([':uid' => $_SESSION['id_usuario'], ':obs' => $obs, ':obs2' => $obs, ':id' => $id]);
            header("Location: /admin/router.php?pagina=incidentes"); exit();
        }

$model = $this->model;

        $filtro_estado = trim($_GET['estado'] ?? '');

        // Chofer (rol 3) solo ve sus propios incidentes
        if ($_SESSION['id_rol'] == 3) {
            $res = $this->pdo->prepare("SELECT legajo FROM usuario WHERE id_usuario = :id");
            $res->execute([':id' => $_SESSION['id_usuario']]);
            $row    = $res->fetch();
            $legajo = $row['legajo'] ?? null;
            $incidentes = $legajo ? $this->model->obtenerPorChofer($legajo) : [];
            if ($filtro_estado) {
                $incidentes = array_filter($incidentes, fn($i) => ($i['estado'] ?? '') === $filtro_estado);
            }
        } elseif ($filtro_estado === 'abierto') {
            $incidentes = $this->model->obtenerAbiertos();
        } else {
            $incidentes = $this->model->obtenerTodos();
            if ($filtro_estado) {
                $incidentes = array_filter($incidentes, fn($i) =>
                    in_array($i['estado'] ?? '', $filtro_estado === 'cerrado' ? ['cerrado','resuelto'] : [$filtro_estado])
                );
            }
        }

        $busqueda = strtolower(trim($_GET['q'] ?? ''));
        if ($busqueda) {
            $incidentes = array_filter($incidentes, fn($i) =>
                str_contains(strtolower($i['cod_viaje']   ?? ''), $busqueda)
                || str_contains(strtolower($i['descripcion'] ?? ''), $busqueda)
                || str_contains(strtolower($i['tipo']        ?? ''), $busqueda)
                || str_contains(strtolower($i['patente']     ?? ''), $busqueda)
            );
        }

        require_once __DIR__ . '/../views/admin/incidentes/listado.php';
    }

    public function editar(): void {
        if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'], [1, 2])) {
            header("Location: /admin/login.php"); exit();
        }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) { header("Location: /admin/router.php?pagina=incidentes"); exit(); }

        $error  = '';
        $tipos  = $this->model->obtenerTipos();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $descripcion = trim($_POST['descripcion'] ?? '');
            $id_tipo_inc = intval($_POST['id_tipo_inc'] ?? 0);
            $estado      = in_array($_POST['estado'] ?? '', ['abierto','cerrado']) ? $_POST['estado'] : 'abierto';

            if (strlen($descripcion) < 10) {
                $error = 'La descripción debe tener al menos 10 caracteres.';
            } elseif (!$id_tipo_inc) {
                $error = 'Seleccioná un tipo de incidente.';
            } else {
                $this->pdo->prepare(
                    "UPDATE incidente SET descripcion=:desc, id_tipo_inc=:tipo, estado=:estado WHERE nro_incidente=:id"
                )->execute([':desc' => $descripcion, ':tipo' => $id_tipo_inc, ':estado' => $estado, ':id' => $id]);
                header("Location: /admin/router.php?pagina=incidentes"); exit();
            }
        }

        $stmt = $this->pdo->prepare(
            "SELECT i.*, t.nombre AS tipo_nombre FROM incidente i
             LEFT JOIN tipoincidente t ON t.id_tipo_inc = i.id_tipo_inc
             WHERE i.nro_incidente = :id"
        );
        $stmt->execute([':id' => $id]);
        $incidente = $stmt->fetch();
        if (!$incidente) { header("Location: /admin/router.php?pagina=incidentes"); exit(); }

        require_once __DIR__ . '/../views/admin/incidentes/editar.php';
    }

    public function crear(): void {
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /admin/login.php"); exit();
        }

        $error = '';
        $model = $this->model;

        // Viajes activos según rol
        if ($_SESSION['id_rol'] == 3) {
            $res = $this->pdo->prepare("SELECT legajo FROM usuario WHERE id_usuario = :id");
            $res->execute([':id' => $_SESSION['id_usuario']]);
            $row    = $res->fetch();
            $legajo = $row['legajo'] ?? null;
            if ($legajo) {
                $v = $this->pdo->prepare(
                    "SELECT v.cod_viaje, s.nombre AS suc_origen, v.fecha_salida
                     FROM viaje v
                     LEFT JOIN sucursal s ON s.id_sucursal = v.id_suc_origen
                     WHERE v.legajo_chofer = :l AND v.fecha_llegada_real IS NULL AND v.cancelado = 0
                     ORDER BY v.fecha_salida DESC"
                );
                $v->execute([':l' => $legajo]);
                $viajes = $v->fetchAll();
            } else {
                $viajes = [];
            }
        } else {
            $viajes = $this->pdo->query(
                "SELECT v.cod_viaje, s.nombre AS suc_origen, v.fecha_salida
                 FROM viaje v
                 LEFT JOIN sucursal s ON s.id_sucursal = v.id_suc_origen
                 WHERE v.fecha_llegada_real IS NULL AND v.cancelado = 0
                 ORDER BY v.fecha_salida DESC"
            )->fetchAll();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $descripcion    = trim($_POST['descripcion']    ?? '');
            $nro_tracking   = trim($_POST['nro_tracking']   ?? '') ?: null;
            $cod_viaje      = trim($_POST['cod_viaje']      ?? '') ?: null;
            $id_tipo_inc    = intval($_POST['id_tipo_inc']   ?? 0);
            $nueva_fecha_est = trim($_POST['nueva_fecha_est'] ?? '') ?: null;

            if (!$id_tipo_inc) {
                $error = 'Seleccioná un tipo de incidente.';
            } elseif (strlen($descripcion) < 10) {
                $error = 'La descripción debe tener al menos 10 caracteres.';
            } else {
                $id = $this->model->crear($cod_viaje ?: null, $id_tipo_inc, $descripcion, $_SESSION['id_usuario']);
                if ($id) {
                    if ($cod_viaje && $nueva_fecha_est) {
                        $this->pdo->prepare("UPDATE viaje SET fecha_llegada_est = :f WHERE cod_viaje = :cod")
                            ->execute([':f' => $nueva_fecha_est, ':cod' => $cod_viaje]);
                    }
                    header("Location: /admin/router.php?pagina=incidentes"); exit();
                } else {
                    $error = 'Error al guardar el incidente.';
                }
            }
        }

        require_once __DIR__ . '/../views/admin/incidentes/crear.php';
    }
}
