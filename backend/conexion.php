<?php

// 1. Detección automática de la raíz del proyecto (donde está el vendor)
$encontrado = false;
$rutaBusqueda = __DIR__;
$ds = DIRECTORY_SEPARATOR;

// Buscamos hacia arriba hasta encontrar la carpeta 'vendor' o llegar a la raíz del disco
while (!file_exists($rutaBusqueda . $ds . 'vendor' . $ds . 'autoload.php')) {
    $rutaAnterior = $rutaBusqueda;
    $rutaBusqueda = dirname($rutaBusqueda);
    
    // Si llegamos a la raíz del sistema y no subimos más, detenemos para evitar bucle infinito
    if ($rutaAnterior === $rutaBusqueda) {
        die("Error Crítico: No se pudo localizar la carpeta 'vendor/autoload.php'. Asegúrate de haber ejecutado 'composer install'.");
    }
}

// 2. Definir la constante de RAÍZ DEL PROYECTO para uso global
define('PROJECT_ROOT', $rutaBusqueda);

// 3. Cargar el autoloader con la ruta absoluta correcta detectada
require_once PROJECT_ROOT . $ds . 'vendor' . $ds . 'autoload.php';

// 4. Cargar variables de entorno desde la misma raíz
$dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT);
try {
    $dotenv->load();
} catch (Exception $e) {
    // Si no hay .env, no morimos, pero quizás quieras loguearlo
    // echo "Nota: No se encontró archivo .env";
}

// 5. Configuración de CORS con Whitelist (Lista Blanca)
// Ajusta estos dominios a los de tu nuevo frontend
$allowedOrigins = [
    'https://aiday-utn-sanrafael-2025.alphadocere.cl',
    'http://localhost:5500', // VSCode Live Server
    'http://127.0.0.1:5500',
    'http://localhost'       // XAMPP default
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");
header("Vary: Origin");

// Manejo de Preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 6. Conexión a Base de Datos (Lógica original mejorada)
$environment = $_ENV['ENVIRONMENT'] ?? 'production';

if ($environment === 'production') {  
    $host = $_ENV['PROD_DB_HOST'] ?? 'localhost';
    $port = $_ENV['PROD_DB_PORT'] ?? '3306';
    $user = $_ENV['PROD_DB_USER'] ?? 'root';
    $password = $_ENV['PROD_DB_PASSWORD'] ?? '';
    $nameDb = $_ENV['PROD_DB_NAME'] ?? 'test';
} else {
    $host = $_ENV['DEV_DB_HOST'] ?? 'localhost';
    $port = $_ENV['DEV_DB_PORT'] ?? '3306';
    $user = $_ENV['DEV_DB_USER'] ?? 'root';
    $password = $_ENV['DEV_DB_PASSWORD'] ?? '';
    $nameDb = $_ENV['DEV_DB_NAME'] ?? 'test';
}

$dsn = "mysql:host=$host;port=$port;dbname=$nameDb;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $password, $options);
} catch (\PDOException $e) {
    // En producción, no hagas echo del error directamente por seguridad.
    // Usamos error_log y devolvemos un JSON genérico.
    error_log("Error de conexión BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión al servidor de base de datos.']);
    exit;
}
?>