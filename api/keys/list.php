<?php
require_once '../../config/database.php';
require_once '../../core/Response.php';

try {
    $db = Database::connect();
    $stmt = $db->query("SELECT id, name, status, created_at FROM api_keys ORDER BY created_at DESC");
    $keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    Response::json([
        'success' => true,
        'data' => $keys
    ]);
} catch (Throwable $e) {
    Response::json(['error' => $e->getMessage()], 500);
}
