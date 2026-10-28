<?php

namespace Tests\Feature;

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    private ImageService $images;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->images = new ImageService;
    }

    public function test_store_saves_image_in_products_folder(): void
    {
        $path = $this->images->store(UploadedFile::fake()->create('tai-nghe.jpg', 100, 'image/jpeg'));

        $this->assertStringStartsWith('products/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_replace_deletes_old_image(): void
    {
        $old = $this->images->store(UploadedFile::fake()->create('cu.jpg', 100, 'image/jpeg'));

        $new = $this->images->replace($old, UploadedFile::fake()->create('moi.png', 100, 'image/png'));

        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);
    }

    public function test_replace_works_when_product_had_no_image(): void
    {
        $new = $this->images->replace(null, UploadedFile::fake()->create('moi.png', 100, 'image/png'));

        Storage::disk('public')->assertExists($new);
    }

    public function test_delete_ignores_missing_file(): void
    {
        $this->images->delete('products/khong-ton-tai.jpg');
        $this->images->delete(null);

        $this->assertTrue(true);
    }
}
