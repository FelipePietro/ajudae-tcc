<?php

// Standalone: validates the actual PHP binary, without booting Laravel or its DB.
try {
    if (in_array('--require-php84', $argv, true) && (PHP_MAJOR_VERSION !== 8 || PHP_MINOR_VERSION !== 4)) {
        throw new RuntimeException('PHP 8.4 is required for this deployment.');
    }
    if (! extension_loaded('gd') || ! function_exists('imagecreatefromstring')) {
        throw new RuntimeException('GD is unavailable.');
    }
    if (! extension_loaded('exif') || ! function_exists('exif_read_data')) {
        throw new RuntimeException('EXIF is required by the existing JPEG sanitizer.');
    }

    $info = gd_info();
    foreach (['JPEG' => ['JPEG Support', 'imagejpeg', 'image/jpeg'], 'PNG' => ['PNG Support', 'imagepng', 'image/png'], 'WebP' => ['WebP Support', 'imagewebp', 'image/webp']] as $format => [$support, $encoder, $mime]) {
        if (empty($info[$support]) || ! function_exists($encoder)) {
            throw new RuntimeException($format.' support is unavailable.');
        }
        $source = imagecreatetruecolor(64, 64);
        imagefill($source, 0, 0, imagecolorallocate($source, 26, 61, 43));
        ob_start();
        $encoded = $encoder($source);
        $bytes = ob_get_clean();
        imagedestroy($source);
        $dimensions = getimagesizefromstring($bytes);
        $decoded = imagecreatefromstring($bytes);
        if (! $encoded || ! $decoded || $dimensions[0] !== 64 || $dimensions[1] !== 64 || $dimensions['mime'] !== $mime) {
            throw new RuntimeException($format.' round trip failed.');
        }
        imagedestroy($decoded);
    }
    echo json_encode(['php' => PHP_VERSION, 'gd' => $info['GD Version'], 'exif' => true, 'formats' => ['JPEG', 'PNG', 'WebP'], 'round_trip' => 'passed'], JSON_THROW_ON_ERROR).PHP_EOL;
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'GD preflight failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}
