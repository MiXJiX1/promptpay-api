<?php
require_once 'config/database.php';
require_once 'core/Response.php';

header('Content-Type: text/plain');

try {
    $db = Database::connect();
    echo "✅ Database connection: OK\n";

    // 1. Check api_keys table
    $stmt = $db->query("SHOW TABLES LIKE 'api_keys'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Table 'api_keys': NOT FOUND\n";
        echo "Attempting to create table...\n";
        $sql = "CREATE TABLE api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            api_key_hash VARCHAR(64) NOT NULL UNIQUE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
        echo "✅ Table 'api_keys': CREATED\n";
    } else {
        echo "✅ Table 'api_keys': EXISTS\n";
    }

    // 2. Check if the default master key exists in the DB
    $masterKey = '46c2a9e395a8dd714d2032715c3caf78fd26b1e3044ec37581a5c54120957a87';
    $hash = hash('sha256', $masterKey);
    
    $stmt = $db->prepare("SELECT id FROM api_keys WHERE api_key_hash = ?");
    $stmt->execute([$hash]);
    
    if ($stmt->rowCount() === 0) {
        echo "⚠️ Master Key: NOT IN DATABASE\n";
        echo "Attempting to add Master Key...\n";
        $stmt = $db->prepare("INSERT INTO api_keys (name, api_key_hash, status) VALUES ('Master Key', ?, 'active')");
        $stmt->execute([$hash]);
        echo "✅ Master Key: ADDED\n";
    } else {
        echo "✅ Master Key: OK\n";
    }

    // 3. Check slips table
    $stmt = $db->query("SHOW TABLES LIKE 'slips'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Table 'slips': NOT FOUND\n";
        echo "Attempting to create table...\n";
        $sql = "CREATE TABLE slips (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hash VARCHAR(64) NOT NULL UNIQUE,
            amount DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
        echo "✅ Table 'slips': CREATED\n";
    } else {
        echo "✅ Table 'slips': EXISTS\n";
    }

    echo "\nAll checks completed. Please try your API again.";

} catch (Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
