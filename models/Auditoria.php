<?php
// models/Auditoria.php — Modelo de registro de auditoría

class Auditoria {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function registrar(string $tabla, string $id_registro, string $accion,
                              ?int $id_usuario, $datos_viejos = null, $datos_nuevos = null): void {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO auditoria (tabla, id_registro, accion, id_usuario, fecha_hora, datos_viejos, datos_nuevos)
                 VALUES (:tabla, :id_reg, :accion, :uid, NOW(), :viejos, :nuevos)"
            );
            $stmt->execute([
                ':tabla'  => $tabla,
                ':id_reg' => (string) $id_registro,
                ':accion' => strtoupper($accion),
                ':uid'    => $id_usuario,
                ':viejos' => $datos_viejos ? json_encode($datos_viejos, JSON_UNESCAPED_UNICODE) : null,
                ':nuevos' => $datos_nuevos ? json_encode($datos_nuevos, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (PDOException $e) {
            error_log("Auditoria::registrar — " . $e->getMessage());
        }
    }

    public function obtenerConFiltros(string $tabla = '', string $accion = '',
                                     string $busqueda = '',
                                     int $pagina = 1, int $por_pagina = 50): array {
        try {
            $where = "WHERE 1=1";
            $params = [];
            if ($tabla)    { $where .= " AND a.tabla = :tabla";          $params[':tabla']  = $tabla; }
            if ($accion)   { $where .= " AND a.accion = :accion";        $params[':accion'] = $accion; }
            if ($busqueda) {
                $where .= " AND (a.id_registro LIKE :q1 OR a.tabla LIKE :q2 OR u.username LIKE :q3)";
                $params[':q1'] = "%$busqueda%";
                $params[':q2'] = "%$busqueda%";
                $params[':q3'] = "%$busqueda%";
            }

            $offset = ($pagina - 1) * $por_pagina;
            $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) FROM auditoria a LEFT JOIN usuario u ON u.id_usuario = a.id_usuario $where");
            $stmtTotal->execute($params);
            $total = (int) $stmtTotal->fetchColumn();

            $stmtData = $this->pdo->prepare(
                "SELECT a.*, u.username
                 FROM auditoria a LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
                 $where ORDER BY a.fecha_hora DESC LIMIT $por_pagina OFFSET $offset"
            );
            $stmtData->execute($params);
            $registros = $stmtData->fetchAll();

            return ['total' => $total, 'registros' => $registros];
        } catch (PDOException $e) {
            error_log("Auditoria::obtenerConFiltros — " . $e->getMessage());
            return ['total' => 0, 'registros' => []];
        }
    }
}
