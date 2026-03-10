<?php
require_once '../../config/database.php';
require_once '../../core/Response.php';

$name = $_POST['name'] ?? 'Untitled Key';

try {
    $db = Database::connect();
    
    // Generate key
    $plainKey = bin2hex(random_bytes(32));
    $hash = hash('sha256', $plainKey);

    $stmt = $db->prepare("INSERT INTO api_keys (name, api_key_hash, status) VALUES (?, ?, 'active')");
    $stmt->execute([$name, $hash]);

    Response::json([
        'success' => true,
        'data' => [
            'name' => $name,
            'key' => $plainKey // Only shown once at creation
        ]
    ]);
} catch (Throwable $e) {
    Response::json(['error' => $e->getMessage()], 500);
}
