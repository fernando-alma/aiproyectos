<?php
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Backend\Models\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Simular entorno de test
$_SERVER['HTTP_X_TESTING_MODE'] = '1';

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(email)
    )";
    
    $db->exec($sql);
    echo "¡Tabla 'password_resets' asegurada en la base de datos de TEST!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
