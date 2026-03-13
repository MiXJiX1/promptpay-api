<?php
require_once __DIR__ . '/../../core/Response.php';

if (!isset($_FILES['slip'])) {
    Response::json(['error' => 'No slip uploaded'], 400);
}

$allowedExtensions = ['jpg', 'jpeg', 'png'];
$allowedMimes = ['image/jpeg', 'image/png'];

$ext = strtolower(pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION));
$mime = mime_content_type($_FILES['slip']['tmp_name']);

if (!in_array($ext, $allowedExtensions) || !in_array($mime, $allowedMimes)) {
    Response::json(['error' => 'รูปแบบไฟล์ไม่ถูกต้อง อนุญาตเฉพาะ JPG และ PNG เท่านั้น'], 400);
}

$filename = uniqid('slip_') . '.' . $ext;
$uploadDir = __DIR__ . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$path = $uploadDir . $filename;
if (!move_uploaded_file($_FILES['slip']['tmp_name'], $path)) {
    Response::json(['error' => 'ไม่สามารถบันทึกไฟล์ได้ กรุณาลองใหม่'], 500);
}

Response::json([
    'success' => true,
    'file' => $filename
]);
