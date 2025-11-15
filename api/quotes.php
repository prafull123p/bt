<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 6;
$offset = ($page - 1) * $per_page;

$out = ['quotes'=>[], 'page'=>$page, 'per_page'=>$per_page, 'has_more'=>false, 'next_page'=>null];

if (isset($conn)) {
    // count total
    $totalRes = $conn->query("SELECT COUNT(*) as cnt FROM quotes");
    $total = 0;
    if ($totalRes) {
        $r = $totalRes->fetch_assoc(); $total = intval($r['cnt'] ?? 0);
    }

    $stmt = $conn->prepare("SELECT id, quote, author FROM quotes ORDER BY id DESC LIMIT ? OFFSET ?");
    if ($stmt) {
        $stmt->bind_param('ii', $per_page, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $out['quotes'][] = ['id'=> (int)$row['id'], 'quote'=> $row['quote'], 'author'=> $row['author']];
        }
        $stmt->close();
    }

    $out['has_more'] = ($offset + count($out['quotes'])) < $total;
    $out['next_page'] = $out['has_more'] ? ($page + 1) : null;
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
