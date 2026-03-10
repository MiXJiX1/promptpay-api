<?php
// Mocking headers for generate_qr test
$_POST['phone'] = '0931898053';
$_POST['amount'] = '100.00';
$_SERVER['HTTP_X_API_KEY'] = '46c2a9e395a8dd714d2032715c3caf78fd26b1e3044ec37581a5c54120957a87';

echo "Testing api/generate_qr.php...\n";
chdir('api');
ob_start();
include 'generate_qr.php';
$output = ob_get_clean();
chdir('..');
echo $output . "\n\n";

// Testing api/slip/verify.php
echo "Testing api/slip/verify.php...\n";
chdir('api/slip');
// We need a dummy file
$dummyFile = 'uploads/test_slip.png';
if (!is_dir(dirname($dummyFile))) mkdir(dirname($dummyFile), 0755, true);
file_put_contents($dummyFile, 'dummy content');

$_POST['file'] = 'test_slip.png';
$_POST['amount'] = '100.00';
$_POST['phone'] = '0931898053';

ob_start();
try {
    include 'verify.php';
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
$output = ob_get_clean();
chdir('../..');
echo $output . "\n";
