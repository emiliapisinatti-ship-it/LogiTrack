<?php
// controllers/ReporteController.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Auditoria.php';

class ReporteController {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function index(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            header("Location: /admin/login.php"); exit();
        }

        $por_mes = $this->pdo->query("
            SELECT DATE_FORMAT(fecha_creacion, '%Y-%m') AS mes,
                   DATE_FORMAT(MIN(fecha_creacion), '%b %Y') AS mes_label,
                   COUNT(*) AS total
            FROM envio
            WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY mes ORDER BY mes ASC
        ")->fetchAll();

        $por_estado = $this->pdo->query("
            SELECT COALESCE(es.nombre, 'Sin estado') AS estado,
                   sub.ultimo_estado AS id_estado,
                   COUNT(*) AS total
            FROM (
                SELECT e.nro_tracking,
                    (SELECT h.id_estado
                     FROM historialestado h
                     WHERE h.nro_tracking = e.nro_tracking
                     ORDER BY h.fecha_hora DESC
                     LIMIT 1) AS ultimo_estado
                FROM envio e
            ) sub
            LEFT JOIN estadoenvio es ON es.id_estado = sub.ultimo_estado
            GROUP BY sub.ultimo_estado, es.nombre
            ORDER BY total DESC
        ")->fetchAll();

        $total_clientes = (int) $this->pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
        $total_envios   = (int) $this->pdo->query("SELECT COUNT(*) FROM envio")->fetchColumn();
        $total_viajes   = (int) $this->pdo->query("SELECT COUNT(*) FROM viaje")->fetchColumn();
        $entregados     = (int) $this->pdo->query(
            "SELECT COUNT(*) FROM envio e
             WHERE (SELECT id_estado FROM historialestado WHERE nro_tracking=e.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1)
                   IN (SELECT id_estado FROM estadoenvio WHERE nombre LIKE '%ntregado%')"
        )->fetchColumn();

        $mes_labels = json_encode(array_column($por_mes,    'mes_label'));
        $mes_data   = json_encode(array_column($por_mes,    'total'));
        $est_labels = json_encode(array_column($por_estado, 'estado'));
        $est_data   = json_encode(array_column($por_estado, 'total'));

        // Tasa de entrega
        $tasa_entrega = $total_envios > 0 ? round(($entregados / $total_envios) * 100) : 0;

        // Envíos por sucursal de origen
        try {
            $por_sucursal = $this->pdo->query("
                SELECT s.id_sucursal, s.nombre AS sucursal, COUNT(*) AS total
                FROM envio e
                JOIN sucursal s ON s.id_sucursal = e.id_suc_origen
                GROUP BY s.id_sucursal, s.nombre
                ORDER BY total DESC LIMIT 8
            ")->fetchAll();
        } catch (PDOException $e) { $por_sucursal = []; }

        // Incidentes por tipo
        try {
            $por_tipo_inc = $this->pdo->query("
                SELECT COALESCE(t.nombre, 'Sin tipo') AS tipo, COUNT(*) AS total
                FROM incidente i
                LEFT JOIN tipoincidente t ON t.id_tipo_inc = i.id_tipo_inc
                GROUP BY t.id_tipo_inc, t.nombre
                ORDER BY total DESC
            ")->fetchAll();
        } catch (PDOException $e) { $por_tipo_inc = []; }

        // Top 5 choferes con más viajes
        try {
            $top_choferes = $this->pdo->query("
                SELECT v.legajo_chofer, e.nombre, e.apellido,
                       COUNT(*) AS total_viajes,
                       SUM(CASE WHEN v.fecha_llegada_real IS NOT NULL THEN 1 ELSE 0 END) AS completados
                FROM viaje v
                JOIN empleado e ON e.legajo = v.legajo_chofer
                WHERE v.cancelado = 0
                GROUP BY v.legajo_chofer, e.nombre, e.apellido
                ORDER BY total_viajes DESC
                LIMIT 5
            ")->fetchAll();
        } catch (PDOException $e) { $top_choferes = []; }

        require_once __DIR__ . '/../views/admin/reportes.php';
    }

    public function auditoria(): void {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
            header("Location: /admin/login.php"); exit();
        }

        $filtro_tabla  = trim($_GET['tabla']   ?? '');
        $filtro_accion = trim($_GET['accion']  ?? '');
        $busqueda      = trim($_GET['q']       ?? '');
        $pagina        = max(1, intval($_GET['p'] ?? 1));
        $por_pagina    = 50;

        $model  = new Auditoria($this->pdo);
        $result = $model->obtenerConFiltros($filtro_tabla, $filtro_accion, $busqueda, $pagina, $por_pagina);

        $registros  = $result['registros'];
        $total_rows = $result['total'];
        $total_pags = max(1, ceil($total_rows / $por_pagina));

        $tablas = $this->pdo->query(
            "SELECT DISTINCT tabla FROM auditoria ORDER BY tabla"
        )->fetchAll(PDO::FETCH_COLUMN);

        require_once __DIR__ . '/../views/admin/auditoria.php';
    }
}
