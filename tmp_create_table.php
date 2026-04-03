<?php
require_once __DIR__.'/backend/app/Models/Database.php';

use App\Backend\Models\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Create password_resets table
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(email)
    )";
    
    $db->exec($sql);
    echo "Table 'password_resets' created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
