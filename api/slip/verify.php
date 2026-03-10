<?php
require_once __DIR__ . '/../../core/SlipDecoder.php';
require_once __DIR__ . '/../../core/SlipValidator.php';
require_once __DIR__ . '/../../core/Response.php';

$file = __DIR__ . '/uploads/' . $_POST['file'];
$orderAmount = floatval($_POST['amount'] ?? 0);
$promptpayId = $_POST['phone'] ?? '';

try {
    if (!file_exists($file)) {
        throw new Exception('File not found');
    }

    $hash = hash_file('sha256', $file);

    // TODO: Check database if hash exists
    // $stmt = $db->prepare("SELECT id FROM slips WHERE hash = ?");
    // $stmt->execute([$hash]);
    // if ($stmt->rowCount() > 0) throw new Exception('Duplicate slip');

    // Verify using SlipService
    require_once 'SlipService.php'; // Ensure SlipService is loaded

    $result = SlipService::verify($file, [
        'amount' => $orderAmount,
        'promptpay' => $promptpayId
    ]);
    
    // If we get here, verification passed
    Response::json([
        'success' => true,
        'status' => 'paid',
        'data' => $result
    ]);

} catch (Throwable $e) {
    Response::json(['error' => $e->getMessage()], 400);
}
