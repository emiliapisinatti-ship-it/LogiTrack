<?php
// models/Paquete.php — Modelo de acceso a datos de paquetes

class Paquete {

    private PDO $pdo;

    public function __construct(PDO $conexion) {
        $this->pdo = $conexion;
    }

    // ─── CONSULTAS ────────────────────────────────────────────────

    public function obtenerPorTracking(string $nro_tracking): ?array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT p.*, tc.nombre AS tipo
                 FROM   Paquete p
                 LEFT JOIN TipoContenido tc ON tc.id_tipo_cont = p.id_tipo_cont
                 WHERE  p.nro_tracking = :tracking LIMIT 1"
            );
            $stmt->execute([':tracking' => $nro_tracking]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Paquete::obtenerPorTracking — " . $e->getMessage());
            return null;
        }
    }

    public function obtenerTodosPorTracking(string $nro_tracking): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT p.*, tc.nombre AS tipo
                 FROM   Paquete p
                 LEFT JOIN TipoContenido tc ON tc.id_tipo_cont = p.id_tipo_cont
                 WHERE  p.nro_tracking = :tracking
                 ORDER BY p.nro_paquete ASC"
            );
            $stmt->execute([':tracking' => $nro_tracking]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Paquete::obtenerTodosPorTracking — " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTiposContenido(): array {
        try {
            return $this->pdo->query(
                "SELECT id_tipo_cont, nombre FROM TipoContenido ORDER BY nombre"
            )->fetchAll();
        } catch (PDOException $e) {
            error_log("Paquete::obtenerTiposContenido — " . $e->getMessage());
            return [];
        }
    }

    // ─── INSERTAR ─────────────────────────────────────────────────

    public function insertar(string $nro_tracking, float $peso_kg, int $alto_cm,
                             int $ancho_cm, int $largo_cm, ?string $descripcion,
                             ?int $id_tipo_cont): bool {
        try {
            $this->pdo->prepare(
                "INSERT INTO Paquete (nro_tracking, peso_kg, alto_cm, ancho_cm, largo_cm, descripcion, id_tipo_cont)
                 VALUES (:trk, :peso, :alto, :ancho, :largo, :desc, :tipo)"
            )->execute([
                ':trk'   => $nro_tracking,
                ':peso'  => $peso_kg,
                ':alto'  => $alto_cm,
                ':ancho' => $ancho_cm,
                ':largo' => $largo_cm,
                ':desc'  => $descripcion,
                ':tipo'  => $id_tipo_cont,
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Paquete::insertar — " . $e->getMessage());
            return false;
        }
    }

    // ─── LÓGICA DE NEGOCIO ────────────────────────────────────────

    public function calcularVolumen(int $alto_cm, int $ancho_cm, int $largo_cm): int {
        return $alto_cm * $ancho_cm * $largo_cm;
    }
}
