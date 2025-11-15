<?php
// Simple integration test for api/gallery.php
// Run: php tests/test_gallery_api.php

echo "Running gallery API test...\n";
require __DIR__ . '/../db.php';

// Simulate GET parameters and include API
$_GET['page'] = 1;
$_GET['per_page'] = 2;

ob_start();
include __DIR__ . '/../api/gallery.php';
$out = ob_get_clean();

if (!$out) {
    echo "FAIL: No output from API\n"; exit(1);
}
$data = json_decode($out, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "FAIL: Invalid JSON returned\n"; exit(1);
}
if (!isset($data['images']) || !is_array($data['images'])) {
    echo "FAIL: Missing images array\n"; exit(1);
}
if (!isset($data['page']) || $data['page'] != 1) {
    echo "FAIL: page not 1\n"; exit(1);
}

echo "PASS: API returned " . count($data['images']) . " images.\n";
exit(0);
