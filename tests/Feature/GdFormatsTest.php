<?php

namespace Tests\Feature;

use App\Services\ManagedImageStorage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GdFormatsTest extends TestCase
{
    public function test_real_jpeg_png_and_webp_are_accepted_by_existing_sanitization(): void
    {
        $this->assertTrue(extension_loaded('gd'));
        foreach (['jpg' => ['imagejpeg', 'image/jpeg'], 'png' => ['imagepng', 'image/png'], 'webp' => ['imagewebp', 'image/webp']] as $extension => [$encoder, $mime]) {
            $image = imagecreatetruecolor(64, 64);
            imagefill($image, 0, 0, imagecolorallocate($image, 26, 61, 43));
            ob_start();
            $this->assertTrue($encoder($image));
            $bytes = ob_get_clean();
            imagedestroy($image);
            $file = UploadedFile::fake()->createWithContent('synthetic.'.$extension, $bytes);
            $prepared = app(ManagedImageStorage::class)->prepare($file);
            $this->assertSame($mime, $prepared['mime_type']);
            $dimensions = getimagesizefromstring($prepared['bytes']);
            $this->assertSame(64, $dimensions[0]);
            $this->assertSame(64, $dimensions[1]);
            $this->assertSame($mime, $dimensions['mime']);
            $this->assertSame(strlen($prepared['bytes']), $prepared['size_bytes']);
        }
    }
}
