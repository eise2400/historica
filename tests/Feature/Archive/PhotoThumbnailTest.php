<?php

namespace Tests\Feature\Archive;

use App\Models\Category;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoThumbnailTest extends TestCase
{
    use RefreshDatabase;

    private function fakeJpegBytes(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 120, 80, 40));
        ob_start();
        imagejpeg($image, null, 90);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    public function test_thumbnail_is_generated_when_a_photo_is_created(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/big.jpg', $this->fakeJpegBytes(3000, 2000));

        $photo = Photo::create([
            'title' => 'Großes Foto',
            'image_path' => 'photos/big.jpg',
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertNotNull($photo->thumbnail_path);
        Storage::disk('public')->assertExists($photo->thumbnail_path);

        [$width, $height] = getimagesize(Storage::disk('public')->path($photo->thumbnail_path));
        $this->assertSame(Photo::THUMBNAIL_WIDTH, $width);
        $this->assertSame(Photo::THUMBNAIL_HEIGHT, $height);
    }

    public function test_thumbnail_url_falls_back_to_original_image_without_a_thumbnail(): void
    {
        $photo = Photo::factory()->create(['image_path' => 'photos/missing.jpg']);

        $this->assertNull($photo->thumbnail_path);
        $this->assertSame($photo->image_url, $photo->thumbnail_url);
    }

    public function test_a_broken_image_does_not_prevent_saving_the_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/not-an-image.jpg', 'this is not image data');

        $photo = Photo::create([
            'title' => 'Kaputtes Foto',
            'image_path' => 'photos/not-an-image.jpg',
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertNotNull($photo->id);
        $this->assertNull($photo->thumbnail_path);
    }
}
