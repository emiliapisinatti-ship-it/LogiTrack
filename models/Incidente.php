<?php
// models/Incidente.php — Modelo de acceso a datos de incidentes

class Incidente {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private string $cols = "
        SELECT i.*, t.nombre AS tipo, v.patente, v.legajo_chofer,
               u.username AS username_resolucion
        FROM incidente i
        LEFT JOIN tipoincidente t ON t.id_tipo_inc = i.id_tipo_inc
        LEFT JOIN viaje v         ON v.cod_viaje   = i.cod_viaje
        LEFT JOIN usuario u       ON u.id_usuario  = i.resuelto_por";

    public function obtenerTodos(): array {
        try {
            return $this->pdo->query($this->cols . " ORDER BY i.fecha_hora DESC")->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function obtenerAbiertos(): array {
        try {
            return $this->pdo->query($this->cols . " WHERE i.estado = 'abierto' ORDER BY i.fecha_hora DESC")->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function obtenerPorChofer(string $legajo): array {
        try {
            $stmt = $this->pdo->prepare($this->cols . " WHERE v.legajo_chofer = :legajo ORDER BY i.fecha_hora DESC");
            $stmt->execute([':legajo' => $legajo]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function obtenerPorViaje(string $cod_viaje): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT i.*, t.nombre AS tipo
                FROM incidente i
                LEFT JOIN tipoincidente t ON t.id_tipo_inc = i.id_tipo_inc
                WHERE i.cod_viaje = :cod ORDER BY i.fecha_hora DESC
            ");
            $stmt->execute([':cod' => $cod_viaje]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function crear(?string $cod_viaje, int $id_tipo_inc, string $descripcion, int $id_usuario): ?int {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO incidente (cod_viaje, id_tipo_inc, fecha_hora, descripcion)
                 VALUES (:cod, :tipo, NOW(), :desc)"
            );
            $stmt->execute([':cod' => $cod_viaje ?: null, ':tipo' => $id_tipo_inc, ':desc' => $descripcion]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Incidente::crear — " . $e->getMessage());
            return null;
        }
    }

    public function obtenerTipos(): array {
        try {
            return $this->pdo->query("SELECT * FROM tipoincidente ORDER BY nombre")->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function contarAbiertos(): int {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM incidente WHERE estado = 'abierto'")->fetchColumn();
        } catch (PDOException $e) { return 0; }
    }
}
