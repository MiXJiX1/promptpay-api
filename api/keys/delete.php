<?php
require_once '../../config/database.php';
require_once '../../core/Response.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    Response::json(['error' => 'Missing key ID'], 400);
}

try {
    $db = Database::connect();
    $stmt = $db->prepare("DELETE FROM api_keys WHERE id = ?");
    $stmt->execute([$id]);

    Response::json([
        'success' => true,
        'message' => 'Key deleted successfully'
    ]);
} catch (Throwable $e) {
    Response::json(['error' => $e->getMessage()], 500);
}
