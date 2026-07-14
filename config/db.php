<?php
// config/db.php — Conexión PDO a MySQL

date_default_timezone_set('America/Argentina/Buenos_Aires');

define('DB_HOST', 'localhost');
define('DB_NAME', 'logitrack');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Error de conexión PDO: " . $e->getMessage());
    die("Error de conexión con la base de datos.");
}
