<?php
include_once __DIR__ . '/..//includes/csrf.php';
include_once __DIR__ . '/..//db.php';
header('Content-Type: application/json; charset=utf-8');

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) {
    // Fallback to form-encoded
    $data = $_POST;
}

$token = $data['_csrf'] ?? ($data['_token'] ?? '');
if (!verify_csrf($token)) {
    echo json_encode(['ok'=>false,'error'=>'Invalid CSRF']); exit;
}

$order = $data['order'] ?? null;
if (!is_array($order)) {
    echo json_encode(['ok'=>false,'error'=>'Order must be an array']); exit;
}

// Update display_order based on array position
$stmt = $conn->prepare("UPDATE gallery SET display_order = ? WHERE id = ?");
foreach ($order as $idx => $id) {
    $idInt = intval($id);
    $pos = intval($idx) + 1;
    $stmt->bind_param('ii', $pos, $idInt);
    $stmt->execute();
}
$stmt->close();

echo json_encode(['ok'=>true]);
exit;
