<?php
require_once '../config/database.php';
require_once '../core/ApiKey.php';
require_once '../core/PromptPay.php';
require_once '../core/Response.php';

try {
    $db = Database::connect();
    ApiKey::verify($db);

    $amount = floatval($_POST['amount'] ?? 0);
    $phone = $_POST['phone'] ?? '';

    if (empty($phone)) {
        throw new Exception('กรุณาระบุเบอร์พร้อมเพย์');
    }

    if ($amount <= 0) {
        throw new Exception('จำนวนเงินต้องมากกว่า 0');
    }

    $payload = PromptPay::generate($phone, $amount);

    Response::json([
        'success' => true,
        'data' => [
            'amount' => $amount,
            'payload' => $payload,
            'qr_image' => "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($payload)
        ]
    ]);

} catch (Throwable $e) {
    Response::json(['error' => 'Database error: ' . $e->getMessage()], 500);
}
