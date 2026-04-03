<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use GuzzleHttp\Client;
use App\Backend\Models\Database;
use PDOException;

class TestCase extends BaseTestCase
{
    /**
     * Cliente HTTP Guzzle para consumo de API.
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * Configuración antes de cada prueba.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Cargar .env para asegurarnos de que el JWT_SECRET de test sea el mismo de la API (Apache)
        $dotenvPath = dirname(__DIR__);
        if (class_exists(\Dotenv\Dotenv::class) && file_exists($dotenvPath . '/.env')) {
            \Dotenv\Dotenv::createImmutable($dotenvPath)->safeLoad();
        }

        // 1. Limpiar la base de datos de pruebas (aiproyectos_test)
        $this->cleanDatabase();

        // 2. Instanciar el cliente HTTP de Guzzle
        $this->setUpHttpClient();
    }

    /**
     * Limpia las tablas clave de la base de datos para evitar colisiones.
     */
    protected function cleanDatabase(): void
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Desactiva comprobaciones de llaves foráneas para poder hacer truncate de forma segura
            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            
            // Truncate en tablas clave
            $db->exec("TRUNCATE TABLE users;");
            $db->exec("TRUNCATE TABLE dashboards;");
            $db->exec("TRUNCATE TABLE projects;");
            $db->exec("TRUNCATE TABLE project_members;");
            $db->exec("TRUNCATE TABLE join_requests;");
            $db->exec("TRUNCATE TABLE tasks;");
            $db->exec("TRUNCATE TABLE password_resets;");
            
            // Reactiva comprobaciones de llaves foráneas
            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
        } catch (PDOException $e) {
            // Si la base de datos de test no existe o falla la conexión, la prueba se omitirá
            $this->markTestSkipped('Fallo al limpiar la base de datos de pruebas: ' . $e->getMessage());
        }
    }

    /**
     * Instancia el cliente HTTP en base a la variable de entorno API_BASE_URL.
     */
    protected function setUpHttpClient(): void
    {
        // La URL de la API viene del phpunit.xml
        $baseUrl = $_ENV['API_BASE_URL'] ?? 'http://localhost/aiproyectos/backend/public/';
        
        $this->http = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'X-Testing-Mode' => '1'
            ],
            // Evitar que Guzzle lance excepciones automaticamente al recibir 4xx o 5xx,
            // lo que nos permite testear esos códigos HTTP de forma manual
            'http_errors' => false 
        ]);
    }

    /**
     * Crea un usuario normal en la BD de tests y devuelve su token JWT.
     */
    protected function authenticateUser(): string
    {
        $db = Database::getInstance()->getConnection();
        $email = 'user_' . uniqid() . '@test.com';
        $db->exec("INSERT INTO users (name, email, password_hash, role, avatar_initials) VALUES ('Usuario Test', '{$email}', 'hash', 'user', 'UT')");
        $id = (int) $db->lastInsertId();
        
        return $this->generateJwt([
            'id' => $id,
            'name' => 'Usuario Test',
            'email' => $email,
            'role' => 'user'
        ]);
    }

    /**
     * Crea un admin en la BD de tests y devuelve su token JWT.
     */
    protected function authenticateAdmin(): string
    {
        $db = Database::getInstance()->getConnection();
        $email = 'admin_' . uniqid() . '@test.com';
        $db->exec("INSERT INTO users (name, email, password_hash, role, avatar_initials) VALUES ('Admin Test', '{$email}', 'hash', 'admin', 'AT')");
        $id = (int) $db->lastInsertId();
        
        return $this->generateJwt([
            'id' => $id,
            'name' => 'Admin Test',
            'email' => $email,
            'role' => 'admin'
        ]);
    }

    /**
     * Genera un token JWT simulado para el test suite.
     */
    protected function generateJwt(array $user): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'CAMBIA_ESTA_CLAVE_EN_ENV';
        
        $header  = $this->base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        
        $payload = $this->base64url(json_encode([
            'sub'   => $user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
            'role'  => $user['role'] ?? 'user',
            'iat'   => time(),
            'exp'   => time() + 86400, // 24 horas
        ]));
        
        $sig = $this->base64url(hash_hmac('sha256', "$header.$payload", $secret, true));
        return "$header.$payload.$sig";
    }

    protected function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
