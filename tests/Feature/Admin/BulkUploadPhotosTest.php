<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Location;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkUploadPhotosTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_upload_photos_as_drafts(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();
        $location = Location::factory()->create(['name' => 'Teugn']);

        $response = $this->actingAs($admin)->postJson('/admin/photos/bulk-upload', [
            'category_id' => $category->id,
            'location_id' => $location->id,
            'date_text' => 'um 1965',
            'is_published' => '0',
            'images' => [
                UploadedFile::fake()->image('kirchweih-1965.jpg'),
                UploadedFile::fake()->image('umzug_1965.jpg'),
            ],
        ]);

        $response->assertOk()->assertJson(['created' => 2]);
        $this->assertSame(2, Photo::count());
        $photo = Photo::where('title', 'Kirchweih 1965')->firstOrFail();
        $this->assertSame($category->id, $photo->category_id);
        $this->assertSame($location->id, $photo->location_id);
        $this->assertSame('um 1965', $photo->date_text);
        $this->assertFalse($photo->is_published);
        $this->assertSame($admin->id, $photo->uploaded_by);
        Storage::disk('public')->assertExists($photo->image_path);
    }

    public function test_bulk_upload_keeps_same_named_files_from_different_folders_separate(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->postJson('/admin/photos/bulk-upload', [
            'category_id' => $category->id,
            'images' => [
                UploadedFile::fake()->image('IMG_0001.jpg'),
                UploadedFile::fake()->image('IMG_0001.jpg'),
            ],
        ]);

        $response->assertOk()->assertJson(['created' => 2]);
        $this->assertSame(2, Photo::count());
        $paths = Photo::pluck('image_path');
        $this->assertNotSame($paths[0], $paths[1]);
        Storage::disk('public')->assertExists($paths[0]);
        Storage::disk('public')->assertExists($paths[1]);
    }

    public function test_bulk_upload_rejects_more_than_fifteen_files_per_request(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->postJson('/admin/photos/bulk-upload', [
            'category_id' => $category->id,
            'images' => array_map(
                fn (int $i) => UploadedFile::fake()->image("foto-{$i}.jpg"),
                range(1, 16)
            ),
        ]);

        $response->assertUnprocessable();
        $this->assertSame(0, Photo::count());
    }

    public function test_bulk_upload_page_renders_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/photos/bulk-upload');

        $response->assertOk()->assertSee('Sammelupload');
    }

    public function test_regular_user_cannot_access_bulk_upload(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/photos/bulk-upload');

        $response->assertForbidden();
    }

    public function test_regular_user_cannot_submit_bulk_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_admin' => false]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->postJson('/admin/photos/bulk-upload', [
            'category_id' => $category->id,
            'images' => [UploadedFile::fake()->image('foto.jpg')],
        ]);

        $response->assertForbidden();
        $this->assertSame(0, Photo::count());
    }
}
