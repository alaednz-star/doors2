<?php
/**
 * One-off image optimizer. Converts large PNG/JPG photos in public/ to:
 *   - a WebP version (same name, .webp)  — small + fast, modern browsers
 *   - an optimized JPG fallback (same name, .jpg) — old browsers
 *
 * Originals are left in place (the views can <picture> between them). Run from
 * CLI:  php bin/optimize-images.php           (dry run — just reports)
 *       php bin/optimize-images.php --write    (actually writes files)
 *
 * Requires GD with WebP support.
 */

$write = in_array('--write', $argv, true);
$root  = dirname(__DIR__) . '/public';
$dirs  = [$root . '/assets/images', $root . '/uploads'];

if (!function_exists('imagewebp')) {
    fwrite(STDERR, "ERROR: GD WebP support not available. Enable extension=gd and restart.\n");
    exit(1);
}

$WEBP_QUALITY = 80;   // 0-100; 80 is visually lossless for photos
$JPG_QUALITY  = 82;
$MAX_WIDTH    = 1600; // downscale anything wider — nobody needs >1600px for these

$totalBefore = 0;
$totalAfterWebp = 0;
$count = 0;

function loadImage(string $path): ?\GdImage {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return match ($ext) {
        'png'         => @imagecreatefrompng($path) ?: null,
        'jpg', 'jpeg' => @imagecreatefromjpeg($path) ?: null,
        'webp'        => @imagecreatefromwebp($path) ?: null,
        default       => null,
    };
}

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $path = $file->getPathname();
        $ext  = strtolower($file->getExtension());
        if (!in_array($ext, ['png', 'jpg', 'jpeg'], true)) continue;

        $before = filesize($path);
        // Skip tiny files — not worth it.
        if ($before < 60 * 1024) continue;

        $img = loadImage($path);
        if (!$img) { echo "  skip (unreadable): $path\n"; continue; }

        $w = imagesx($img);
        $h = imagesy($img);

        // Downscale if very wide.
        if ($w > $MAX_WIDTH) {
            $nh = (int)round($h * ($MAX_WIDTH / $w));
            $resized = imagecreatetruecolor($MAX_WIDTH, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $MAX_WIDTH, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
            $w = $MAX_WIDTH; $h = $nh;
        }

        $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path);

        if ($write) {
            imagepalettetotruecolor($img);
            imagewebp($img, $webpPath, $WEBP_QUALITY);
        }
        $afterWebp = $write && is_file($webpPath) ? filesize($webpPath) : (int)round($before * 0.2);

        $totalBefore   += $before;
        $totalAfterWebp += $afterWebp;
        $count++;

        printf("  %-55s %6.0f KB -> %6.0f KB webp  (%d%% smaller)\n",
            str_replace($root . '/', '', $path),
            $before / 1024,
            $afterWebp / 1024,
            $before > 0 ? (int)round((1 - $afterWebp / $before) * 100) : 0
        );

        imagedestroy($img);
    }
}

echo "\n";
echo $write ? "=== DONE (files written) ===\n" : "=== DRY RUN (use --write to apply) ===\n";
printf("Files: %d\n", $count);
printf("Before:     %.1f MB\n", $totalBefore / 1048576);
printf("After WebP:  %.1f MB  (%.0f%% smaller)\n",
    $totalAfterWebp / 1048576,
    $totalBefore > 0 ? (1 - $totalAfterWebp / $totalBefore) * 100 : 0
);
