<?php
// models/Envio.php — Modelo de acceso a datos de envíos

class Envio {

    private PDO $pdo;

    public function __construct(PDO $conexion) {
        $this->pdo = $conexion;
    }

    // ─── LISTADOS ────────────────────────────────────────────────

    public function obtenerTodos(): array {
        try {
            return $this->pdo->query(
                "SELECT * FROM vista_envios_completos ORDER BY fecha_creacion DESC"
            )->fetchAll();
        } catch (PDOException $e) {
            error_log("Envio::obtenerTodos — " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorCliente(string $dni): array {
        try {
            $sql = "SELECT e.nro_tracking,
                           e.fecha_creacion,
                           e.direccion_entrega,
                           e.dni_remitente,
                           e.dni_destinatario,
                           so.nombre   AS suc_origen,
                           sd.nombre   AS suc_destino,
                           cr.nombre   AS nombre_remit,
                           cr.apellido AS apellido_remit,
                           cd.nombre   AS nombre_dest,
                           cd.apellido AS apellido_dest,
                           h.id_estado AS id_estado_actual,
                           es.nombre   AS estado_actual,
                           (SELECT COUNT(*) FROM Paquete p WHERE p.nro_tracking = e.nro_tracking) AS cant_paquetes
                    FROM   Envio e
                    LEFT JOIN Sucursal    so ON so.id_sucursal = e.id_suc_origen
                    LEFT JOIN Sucursal    sd ON sd.id_sucursal = e.id_suc_destino
                    LEFT JOIN Cliente     cr ON cr.dni = e.dni_remitente
                    LEFT JOIN Cliente     cd ON cd.dni = e.dni_destinatario
                    LEFT JOIN HistorialEstado h ON h.id_hist = (SELECT id_hist FROM HistorialEstado WHERE nro_tracking = e.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1)
                    LEFT JOIN EstadoEnvio es ON es.id_estado = h.id_estado
                    WHERE  e.dni_remitente = :dni1 OR e.dni_destinatario = :dni2
                    ORDER BY e.fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':dni1' => $dni, ':dni2' => $dni]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Envio::obtenerPorCliente — " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetalle(string $nro_tracking): ?array {
        try {
            $sql = "SELECT e.*,
                           so.nombre   AS suc_origen,
                           sd.nombre   AS suc_destino,
                           cr.nombre   AS nombre_remitente,
                           cr.apellido AS apellido_remitente,
                           cd.nombre   AS nombre_dest,
                           cd.apellido AS apellido_dest
                    FROM   Envio e
                    LEFT JOIN Sucursal so ON so.id_sucursal = e.id_suc_origen
                    LEFT JOIN Sucursal sd ON sd.id_sucursal = e.id_suc_destino
                    LEFT JOIN Cliente  cr ON cr.dni = e.dni_remitente
                    LEFT JOIN Cliente  cd ON cd.dni = e.dni_destinatario
                    WHERE  e.nro_tracking = :tracking LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':tracking' => $nro_tracking]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Envio::obtenerDetalle — " . $e->getMessage());
            return null;
        }
    }

    public function obtenerHistorialEstados(string $nro_tracking): array {
        try {
            $sql = "SELECT h.fecha_hora, h.observacion, es.nombre AS estado
                    FROM   HistorialEstado h
                    JOIN   EstadoEnvio     es ON es.id_estado = h.id_estado
                    WHERE  h.nro_tracking = :tracking
                    ORDER BY h.fecha_hora DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':tracking' => $nro_tracking]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Envio::obtenerHistorialEstados — " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorSucursal(int $id_sucursal, int $id_estado = 0): array {
        try {
            $sql = "SELECT * FROM vista_envios_completos
                    WHERE (id_suc_origen = :suc OR id_suc_destino = :suc2)";
            $params = [':suc' => $id_sucursal, ':suc2' => $id_sucursal];
            if ($id_estado > 0) {
                $sql .= " AND id_estado_hist = :estado";
                $params[':estado'] = $id_estado;
            }
            $sql .= " ORDER BY fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Envio::obtenerPorSucursal — " . $e->getMessage());
            return [];
        }
    }

    // ─── CONTEOS ─────────────────────────────────────────────────

    public function contarTodos(): int {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM Envio")->fetchColumn();
        } catch (PDOException $e) { return 0; }
    }

    public function contarPorCliente(string $dni): int {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM Envio WHERE dni_remitente = :dni1 OR dni_destinatario = :dni2"
            );
            $stmt->execute([':dni1' => $dni, ':dni2' => $dni]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) { return 0; }
    }
}
