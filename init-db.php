<?php
/**
 * Database initialization script
 * Automatically runs schema and seeds on first deployment
 */

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Check if tables already exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "[DB] Tables already initialized\n";
        exit(0);
    }
    
    echo "[DB] Initializing database schema...\n";
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    if (!$schema) {
        throw new Exception("schema.sql not found");
    }
    
    // Execute each statement separately to handle multiple statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "[DB] Schema created successfully\n";
    echo "[DB] Populating with sample data...\n";
    
    // Read and execute seeds
    $seeds = file_get_contents(__DIR__ . '/seeds.sql');
    if ($seeds) {
        $seedStatements = array_filter(array_map('trim', explode(';', $seeds)));
        foreach ($seedStatements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        echo "[DB] Sample data inserted successfully\n";
    }
    
    echo "[DB] ✅ Database initialization complete!\n";
    
} catch (Exception $e) {
    echo "[DB] ❌ Error: " . $e->getMessage() . "\n";
}
?>

