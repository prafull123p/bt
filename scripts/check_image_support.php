<?php
/**
 * Check server capabilities for image conversion: GD WebP support and Imagick availability.
 * Usage: php scripts/check_image_support.php
 */

echo "Checking image conversion capabilities...\n";

$gd = extension_loaded('gd');
$im = class_exists('Imagick');

if ($gd) {
  echo "GD extension: available\n";
  // Check webp support by function
  $webp = function_exists('imagewebp') ? 'yes' : 'no';
  echo " - imagewebp(): $webp\n";
  // Check supported types
  if (function_exists('gd_info')) {
    $info = gd_info();
    if (isset($info['WebP Support'])) {
      echo " - GD WebP support flag: " . ($info['WebP Support'] ? 'yes' : 'no') . "\n";
    }
  }
} else {
  echo "GD extension: NOT available\n";
}

if ($im) {
  echo "Imagick: available\n";
  try {
    // call as string to avoid a direct Imagick class reference at compile time
    $imformats = call_user_func('Imagick::queryFormats');
    if (!is_array($imformats)) {
      $imformats = [];
    }
    $hasWebP = in_array('WEBP', $imformats);
    $hasAVIF = in_array('AVIF', $imformats) || in_array('AV1', $imformats);
    echo " - Imagick supports WEBP: " . ($hasWebP ? 'yes' : 'no') . "\n";
    echo " - Imagick supports AVIF (best effort): " . ($hasAVIF ? 'yes' : 'no') . "\n";
  } catch (Exception $e) {
    echo " - Imagick query failed: " . $e->getMessage() . "\n";
  }
} else {
  echo "Imagick: NOT available\n";
}

echo "Done. If both GD WebP and/or Imagick WebP/AVIF are available, on-upload conversions should succeed.\n";
