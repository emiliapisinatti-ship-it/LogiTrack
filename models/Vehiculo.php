<?php
// models/Vehiculo.php

class Vehiculo {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerTodos(int $id_sucursal = 0, string $estado = '', int $id_tipo = 0): array {
        $sql = "
            SELECT v.patente, v.modelo, v.estado, v.id_sucursal,
                   tv.nombre AS tipo, tv.capacidad_kg_max,
                   s.nombre  AS sucursal
            FROM   vehiculo v
            JOIN   tipovehiculo tv ON tv.id_tipo_veh = v.id_tipo_veh
            LEFT JOIN sucursal s   ON s.id_sucursal  = v.id_sucursal
            WHERE  1=1
        ";
        $params = [];
        if ($id_sucursal > 0) { $sql .= " AND v.id_sucursal = :suc";    $params[':suc']  = $id_sucursal; }
        if ($estado !== '')   { $sql .= " AND v.estado = :est";        $params[':est']  = $estado; }
        if ($id_tipo > 0)     { $sql .= " AND v.id_tipo_veh = :tipo";  $params[':tipo'] = $id_tipo; }
        $sql .= " ORDER BY v.patente";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorPatente(string $patente): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculo WHERE patente = :p");
        $stmt->execute([':p' => $patente]);
        return $stmt->fetch();
    }

    public function obtenerTipos(): array {
        return $this->pdo->query(
            "SELECT id_tipo_veh, nombre, capacidad_kg_max FROM tipovehiculo ORDER BY nombre"
        )->fetchAll();
    }

    public function tieneViajeActivo(string $patente): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM viaje
             WHERE patente = :p AND fecha_llegada_real IS NULL AND cancelado = 0"
        );
        $stmt->execute([':p' => $patente]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
