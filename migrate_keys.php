<?php
require_once 'config/database.php';
$db = Database::connect();
$sql = file_get_contents('sql/api_keys.sql');
try {
    $db->exec($sql);
    echo "Table api_keys created or already exists.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
