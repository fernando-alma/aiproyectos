<?php

namespace App\Backend\Models;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        // Cargar variables de entorno si no están cargadas
        // (Esto asume que el Dotenv se cargó en el index.php, pero por seguridad verificamos $_ENV)
        
        $environment = $_ENV['ENVIRONMENT'] ?? 'production';

        if ($environment === 'production') {
            $host = $_ENV['PROD_DB_HOST'] ?? 'localhost';
            $db   = $_ENV['PROD_DB_NAME'] ?? '';
            $user = $_ENV['PROD_DB_USER'] ?? '';
            $pass = $_ENV['PROD_DB_PASS'] ?? '';
            $port = $_ENV['PROD_DB_PORT'] ?? '3306';
        } else {
            $host = $_ENV['DEV_DB_HOST'] ?? 'localhost';
            $db   = $_ENV['DEV_DB_NAME'] ?? 'hackdash';
            $user = $_ENV['DEV_DB_USER'] ?? 'root';
            $pass = $_ENV['DEV_DB_PASS'] ?? '';
            $port = $_ENV['DEV_DB_PORT'] ?? '3306';
        }

        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // En producción devolvemos JSON error 500 para que el frontend no reciba HTML roto
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}