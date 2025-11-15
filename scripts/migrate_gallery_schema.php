<?php
/**
 * Idempotent migration for `gallery` table.
 * Usage: php scripts/migrate_gallery_schema.php
 */
require_once __DIR__ . '/..//db.php';

$table = 'gallery';

$columns = [
  'image_path'   => "VARCHAR(255) DEFAULT NULL",
  'image_small'  => "VARCHAR(255) DEFAULT NULL",
  'image_medium' => "VARCHAR(255) DEFAULT NULL",
  'image_large'  => "VARCHAR(255) DEFAULT NULL",
  'webp_path'    => "VARCHAR(255) DEFAULT NULL",
  'avif_path'    => "VARCHAR(255) DEFAULT NULL",
  'title'        => "VARCHAR(255) DEFAULT NULL",
  'description'  => "TEXT DEFAULT NULL",
  'color_tag'    => "VARCHAR(50) DEFAULT NULL",
  'display_order'=> "INT DEFAULT 9999",
  'featured'     => "TINYINT(1) DEFAULT 0",
  'effect_strength' => "INT DEFAULT 0",
  'created_at'   => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
];

echo "Checking table `$table`...\n";

$dbName = null;
$res = $conn->query("SELECT DATABASE() as db");
if ($res) {
  $row = $res->fetch_assoc();
  $dbName = $row['db'];
}
if (!$dbName) {
  echo "ERROR: Could not determine current database.\n";
  exit(2);
}

$placeholders = implode(',', array_fill(0, count($columns), '?'));

// Fetch existing columns
$sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $conn->real_escape_string($dbName) . "' AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'";
$res = $conn->query($sql);
$existing = [];
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $existing[] = $r['COLUMN_NAME'];
  }
} else {
  echo "ERROR: Could not query information_schema: " . $conn->error . "\n";
  exit(3);
}

$toAdd = [];
foreach ($columns as $col => $definition) {
  if (!in_array($col, $existing)) {
    $toAdd[$col] = $definition;
  }
}

if (empty($toAdd)) {
  echo "No missing columns. Migration not required.\n";
  exit(0);
}

echo "Missing columns detected: " . implode(', ', array_keys($toAdd)) . "\n";

$parts = [];
foreach ($toAdd as $col => $definition) {
  $parts[] = "ADD COLUMN `$col` $definition";
}

$alter = "ALTER TABLE `$table` " . implode(', ', $parts);
echo "Running: $alter\n";

if ($conn->query($alter) === TRUE) {
  echo "Migration applied successfully.\n";
  // Show final columns
  $res2 = $conn->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $conn->real_escape_string($dbName) . "' AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'");
  $cols = [];
  while ($r = $res2->fetch_assoc()) $cols[] = $r['COLUMN_NAME'];
  echo "Table now has columns: " . implode(', ', $cols) . "\n";
  exit(0);
} else {
  echo "Migration failed: " . $conn->error . "\n";
  exit(4);
}

