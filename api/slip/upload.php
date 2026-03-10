<?php
require_once __DIR__ . '/../../core/Response.php';

if (!isset($_FILES['slip'])) {
    Response::json(['error' => 'No slip uploaded'], 400);
}

$ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
$filename = uniqid('slip_') . '.' . $ext;
$uploadDir = __DIR__ . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$path = $uploadDir . $filename;
move_uploaded_file($_FILES['slip']['tmp_name'], $path);

Response::json([
    'success' => true,
    'file' => $filename
]);
