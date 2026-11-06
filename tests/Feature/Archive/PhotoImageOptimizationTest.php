<?php

namespace Tests\Feature\Archive;

use App\Models\Category;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private function fakeJpegBytes(int $width, int $height, bool $noisy = false): string
    {
        $image = imagecreatetruecolor($width, $height);

        if ($noisy) {
            for ($x = 0; $x < $width; $x += 4) {
                for ($y = 0; $y < $height; $y += 4) {
                    $color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
                    imagefilledrectangle($image, $x, $y, $x + 3, $y + 3, $color);
                }
            }
        } else {
            imagefill($image, 0, 0, imagecolorallocate($image, 120, 80, 40));
        }

        ob_start();
        imagejpeg($image, null, 95);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    public function test_oversized_photo_is_scaled_down_to_the_maximum_dimension(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/big.png', $this->fakeJpegBytes(4000, 3000));

        $photo = Photo::create([
            'title' => 'Riesiges Foto',
            'image_path' => 'photos/big.png',
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertStringEndsWith('.jpg', $photo->image_path);
        Storage::disk('public')->assertMissing('photos/big.png');
        Storage::disk('public')->assertExists($photo->image_path);

        [$width, $height] = getimagesize(Storage::disk('public')->path($photo->image_path));
        $this->assertLessThanOrEqual(Photo::MAX_IMAGE_DIMENSION, $width);
        $this->assertLessThanOrEqual(Photo::MAX_IMAGE_DIMENSION, $height);
        // 4000x3000 scaled down keeping the 4:3 ratio with the longest edge capped.
        $this->assertSame(Photo::MAX_IMAGE_DIMENSION, $width);
        $this->assertSame(1500, $height);
    }

    public function test_small_photo_is_not_upscaled(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/small.jpg', $this->fakeJpegBytes(800, 600));

        $photo = Photo::create([
            'title' => 'Kleines Foto',
            'image_path' => 'photos/small.jpg',
            'category_id' => Category::factory()->create()->id,
        ]);

        [$width, $height] = getimagesize(Storage::disk('public')->path($photo->image_path));
        $this->assertSame(800, $width);
        $this->assertSame(600, $height);
    }

    public function test_large_detailed_photo_stays_under_the_maximum_byte_size(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/noisy.jpg', $this->fakeJpegBytes(1600, 1600, noisy: true));

        $photo = Photo::create([
            'title' => 'Verrauschtes Foto',
            'image_path' => 'photos/noisy.jpg',
            'category_id' => Category::factory()->create()->id,
        ]);

        $size = Storage::disk('public')->size($photo->image_path);
        // Generous tolerance over the exact cap - the quality-reduction loop
        // stops at a floor rather than guaranteeing it hits the target exactly.
        $this->assertLessThanOrEqual(Photo::MAX_IMAGE_BYTES * 1.1, $size);
    }

    public function test_replacing_a_photos_image_deletes_the_previous_optimized_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/first.jpg', $this->fakeJpegBytes(1000, 800));

        $photo = Photo::create([
            'title' => 'Foto',
            'image_path' => 'photos/first.jpg',
            'category_id' => Category::factory()->create()->id,
        ]);
        $firstOptimizedPath = $photo->image_path;

        Storage::disk('public')->put('photos/second.jpg', $this->fakeJpegBytes(1000, 800));
        $photo->update(['image_path' => 'photos/second.jpg']);

        Storage::disk('public')->assertMissing($firstOptimizedPath);
        Storage::disk('public')->assertExists($photo->image_path);
    }

    public function test_a_broken_image_leaves_the_original_image_path_untouched(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('photos/not-an-image.jpg', 'this is not image data');

        $photo = Photo::create([
            'title' => 'Kaputtes Foto',
            'image_path' => 'photos/not-an-image.jpg',
            'category_id' => Category::factory()->create()->id,
        ]);

        $this->assertSame('photos/not-an-image.jpg', $photo->image_path);
        Storage::disk('public')->assertExists('photos/not-an-image.jpg');
    }
}
