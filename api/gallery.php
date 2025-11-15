<?php
header('Content-Type: application/json; charset=utf-8');
include_once __DIR__ . '/..//db.php';

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? intval($_GET['per_page']) : 12;
$offset = ($page - 1) * $per_page;

$resp = ['page'=>$page, 'per_page'=>$per_page, 'images'=>[], 'has_more'=>false];

$stmt = $conn->prepare("SELECT id, image_path, image_small, image_medium, image_large, webp_path, avif_path, title, description, color_tag, display_order, featured, effect_strength FROM gallery ORDER BY COALESCE(display_order,9999) ASC, id DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $per_page, $offset);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $resp['images'][] = $row;
}
$stmt->close();

// check if there are more
$stmt2 = $conn->prepare("SELECT COUNT(*) as cnt FROM gallery");
$stmt2->execute();
$cres = $stmt2->get_result();
$total = 0;
if ($r = $cres->fetch_assoc()) $total = intval($r['cnt']);
$stmt2->close();
$resp['has_more'] = ($page * $per_page) < $total;

echo json_encode($resp);
exit;
