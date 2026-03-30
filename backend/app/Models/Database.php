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
        // Leemos las variables del .env de forma directa y limpia
        // Si no existen, usamos valores por defecto seguros para local
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        
        // Verificamos por la variable de entorno o por el Header que Guzzle inyecta
        $isTesting = ($_ENV['APP_ENV'] ?? '') === 'testing' || ($_SERVER['HTTP_X_TESTING_MODE'] ?? '0') === '1';

        if ($isTesting) {
            $db = 'aiproyectos_test';
        } else {
            $db = $_ENV['DB_NAME'] ?? 'aiproyectos';
        }
        
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '3306';
        
        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Aseguramos que la conexión use el charset correcto
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Si la conexión falla, respondemos con JSON para no romper el Frontend
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false, 
                'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Retorna la instancia única de la clase (Pattern Singleton)
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna el objeto de conexión PDO
     */
    public function getConnection(): PDO
    {
        return $this->conn;
    }
}