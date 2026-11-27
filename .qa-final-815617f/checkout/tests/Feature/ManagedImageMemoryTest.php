<?php

namespace Tests\Feature;

use App\Services\ManagedImageStorage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Tests\TestCase;

class ManagedImageMemoryTest extends TestCase
{
    public function test_25_megapixel_image_is_rejected_before_decode_on_small_memory_budget(): void
    {
        $file = UploadedFile::fake()->image('synthetic.png', 8000, 3125);
        $this->assertLessThan(5242880, $file->getSize());
        $previous = ini_get('memory_limit');
        try {
            ini_set('memory_limit', '128M');
            $this->expectException(ServiceUnavailableHttpException::class);
            (new ManagedImageStorage)->prepare($file);
        } finally {
            ini_set('memory_limit', $previous);
        }
    }
}
