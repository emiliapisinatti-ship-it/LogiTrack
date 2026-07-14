<?php
// models/Viaje.php — Modelo de acceso a datos de viajes

class Viaje {

    private PDO $pdo;

    public function __construct(PDO $conexion) {
        $this->pdo = $conexion;
    }

    public function obtenerPorChofer(string $legajo_chofer): array {
        try {
            $sql = "SELECT v.cod_viaje,
                           v.fecha_salida, v.fecha_llegada_est, v.fecha_llegada_real,
                           v.patente,
                           s.nombre AS suc_origen,
                           COUNT(ve.nro_tracking) AS total_envios
                    FROM   viaje v
                    LEFT JOIN sucursal    s  ON s.id_sucursal  = v.id_suc_origen
                    LEFT JOIN viaje_envio ve ON ve.cod_viaje   = v.cod_viaje
                    WHERE  v.legajo_chofer = :legajo AND v.cancelado = 0
                    GROUP BY v.cod_viaje, v.fecha_salida, v.fecha_llegada_est,
                             v.fecha_llegada_real, v.patente, s.nombre
                    ORDER BY v.fecha_salida DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':legajo' => $legajo_chofer]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Viaje::obtenerPorChofer — " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetalle(string $cod_viaje): ?array {
        try {
            $sql = "SELECT v.*,
                           s.nombre   AS suc_origen,
                           sd.nombre  AS suc_destino,
                           e.nombre   AS nombre_chofer,
                           e.apellido AS apellido_chofer
                    FROM   viaje v
                    LEFT JOIN sucursal s  ON s.id_sucursal  = v.id_suc_origen
                    LEFT JOIN sucursal sd ON sd.id_sucursal = v.id_suc_destino
                    LEFT JOIN empleado e  ON e.legajo       = v.legajo_chofer
                    WHERE  v.cod_viaje = :cod LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':cod' => $cod_viaje]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Viaje::obtenerDetalle — " . $e->getMessage());
            return null;
        }
    }

    public function obtenerEnvios(string $cod_viaje): array {
        try {
            $sql = "SELECT e.nro_tracking,
                           e.direccion_entrega,
                           sd.nombre   AS suc_destino,
                           cr.nombre   AS nombre_remitente,
                           cr.apellido AS apellido_remitente,
                           cd.nombre   AS nombre_dest,
                           cd.apellido AS apellido_dest,
                           es.nombre   AS estado_actual
                    FROM   viaje_envio ve
                    JOIN   envio e ON e.nro_tracking = ve.nro_tracking
                    LEFT JOIN sucursal sd ON sd.id_sucursal = e.id_suc_destino
                    LEFT JOIN cliente  cr ON cr.dni = e.dni_remitente
                    LEFT JOIN cliente  cd ON cd.dni = e.dni_destinatario
                    LEFT JOIN historialestado h ON h.id_hist = (SELECT id_hist FROM historialestado WHERE nro_tracking = e.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1)
                    LEFT JOIN estadoenvio es ON es.id_estado = h.id_estado
                    WHERE  ve.cod_viaje = :cod ORDER BY e.nro_tracking";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':cod' => $cod_viaje]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Viaje::obtenerEnvios — " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTodos(): array {
        try {
            return $this->pdo->query(
                "SELECT * FROM vista_viajes_completos WHERE cancelado = 0 ORDER BY fecha_salida DESC"
            )->fetchAll();
        } catch (PDOException $e) {
            error_log("Viaje::obtenerTodos — " . $e->getMessage());
            return [];
        }
    }

    public function completar(string $cod_viaje): bool {
        try {
            // Sucursal destino del viaje (con fallback a origen para viajes sin destino definido)
            $suc_stmt = $this->pdo->prepare("SELECT COALESCE(id_suc_destino, id_suc_origen) FROM viaje WHERE cod_viaje = :cod");
            $suc_stmt->execute([':cod' => $cod_viaje]);
            $id_suc_destino = $suc_stmt->fetchColumn() ?: null;

            $uid = $_SESSION['id_usuario'] ?? null;

            // Fix 2: paquetes que aún estén en "En Viaje" pasan a "En Sucursal Destino"
            $trks = $this->pdo->prepare("
                SELECT ve.nro_tracking FROM viaje_envio ve
                WHERE ve.cod_viaje = :cod
                  AND (SELECT id_estado FROM historialestado WHERE nro_tracking = ve.nro_tracking ORDER BY fecha_hora DESC, id_hist DESC LIMIT 1) = 2
            ");
            $trks->execute([':cod' => $cod_viaje]);
            $pendientes = $trks->fetchAll(PDO::FETCH_COLUMN);

            $this->pdo->beginTransaction();

            $this->pdo->prepare("UPDATE viaje SET fecha_llegada_real = NOW() WHERE cod_viaje = :cod")
                ->execute([':cod' => $cod_viaje]);

            foreach ($pendientes as $trk) {
                $this->pdo->prepare("
                    INSERT INTO historialestado (nro_tracking, id_estado, id_sucursal, id_usuario, observacion, fecha_hora)
                    VALUES (:trk, 3, :suc, :uid, 'Recibido en sucursal destino al completar viaje', NOW())
                ")->execute([':trk' => $trk, ':suc' => $id_suc_destino, ':uid' => $uid]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Viaje::completar — " . $e->getMessage());
            return false;
        }
    }

    public function estadoViaje(array $viaje): string {
        $ahora = time();
        $salida = strtotime($viaje['fecha_salida']);
        $llegada_real = $viaje['fecha_llegada_real']
            ? strtotime($viaje['fecha_llegada_real']) : null;
        if ($llegada_real) return 'Completado';
        if ($ahora >= $salida) return 'En curso';
        return 'Pendiente';
    }

    public function contarEnCurso(): int {
        try {
            return (int) $this->pdo->query(
                "SELECT COUNT(*) FROM viaje WHERE fecha_llegada_real IS NULL AND fecha_salida <= NOW() AND cancelado = 0"
            )->fetchColumn();
        } catch (PDOException $e) { return 0; }
    }
}
