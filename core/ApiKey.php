<?php
class ApiKey {

    public static function generate() {
        return bin2hex(random_bytes(32));
    }

    public static function hash($key) {
        return hash('sha256', $key);
    }

    public static function verify(PDO $db) {

    $key = '';

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['X-API-KEY'])) {
            $key = $headers['X-API-KEY'];
        } elseif (isset($headers['x-api-key'])) {
            $key = $headers['x-api-key'];
        }
    }

    if (!$key && isset($_SERVER['HTTP_X_API_KEY'])) {
        $key = $_SERVER['HTTP_X_API_KEY'];
    }

    if (!$key) {
        http_response_code(401);
        echo json_encode(['error' => 'API key required']);
        exit;
    }

    $hash = hash('sha256', $key);

    $stmt = $db->prepare("
        SELECT id FROM api_keys
        WHERE api_key_hash = ? AND status = 'active'
    ");
    $stmt->execute([$hash]);

    if ($stmt->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
}


    private static function error($code, $msg) {
        http_response_code($code);
        echo json_encode(['error' => $msg]);
        exit;
    }
}