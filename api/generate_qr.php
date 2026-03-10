<?php
require_once '../config/database.php';
require_once '../core/ApiKey.php';
require_once '../core/PromptPay.php';
require_once '../core/Response.php';

$db = Database::connect();
ApiKey::verify($db);

$amount = floatval($_POST['amount'] ?? 0);
$promptpayId = $_POST['phone'] ?? '0931898053';

if (empty($_POST['phone'])) {
    Response::json(['error' => 'Phone number is required']);
}

if ($amount <= 0) {
    Response::json(['error' => 'Invalid amount']);
}

$payload = PromptPay::generate($promptpayId, $amount);

Response::json([
    'success' => true,
    'data' => [
        'amount' => $amount,
        'payload' => $payload,
        'qr_image' =>
            "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data="
            . urlencode($payload)
    ]
]);
