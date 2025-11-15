<?php
// CLI script to batch-convert gallery images to WebP/AVIF (best-effort)
// Usage: php scripts/convert_images.php

require __DIR__ . '/../db.php';

$dir = __DIR__ . '/../uploads/gallery';
if (!is_dir($dir)) { echo "No uploads/gallery directory found.\n"; exit(1); }

$files = glob($dir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
if (empty($files)) { echo "No images found.\n"; exit(0); }

foreach ($files as $file) {
    echo "Processing: $file\n";
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    // create webp
    if (function_exists('imagewebp') && $ext !== 'gif') {
        $im = null;
        if (in_array($ext, ['jpg','jpeg'])) $im = @imagecreatefromjpeg($file);
        elseif ($ext === 'png') $im = @imagecreatefrompng($file);
        if ($im) {
            $webp = preg_replace('/\.[^.]+$/', '.webp', $file);
            if (imagewebp($im, $webp, 84)) echo "  -> webp created: $webp\n";
            imagedestroy($im);
        }
    }
    // try avif via imagick
    if (class_exists('Imagick')) {
        try {
            $im = new Imagick($file);
            $im->setImageFormat('avif');
            $avif = preg_replace('/\.[^.]+$/', '.avif', $file);
            if ($im->writeImage($avif)) echo "  -> avif created: $avif\n";
            $im->clear(); $im->destroy();
        } catch (Exception $e) { echo "  -> avif failed\n"; }
    }
}

echo "Done. Consider running a DB update to record generated filenames if needed.\n";
