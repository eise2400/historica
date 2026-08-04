<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\PhotoResource\Pages\BulkUploadPhotos;
use App\Models\Category;
use App\Models\Location;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
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

        Livewire::actingAs($admin)
            ->test(BulkUploadPhotos::class)
            ->set('data.category_id', $category->id)
            ->set('data.location_id', $location->id)
            ->set('data.date_text', 'um 1965')
            ->set('data.images', [
                UploadedFile::fake()->image('kirchweih-1965.jpg'),
                UploadedFile::fake()->image('umzug_1965.jpg'),
            ])
            ->call('upload');

        $this->assertSame(2, Photo::count());
        $photo = Photo::where('title', 'Kirchweih 1965')->firstOrFail();
        $this->assertSame($category->id, $photo->category_id);
        $this->assertSame($location->id, $photo->location_id);
        $this->assertSame('um 1965', $photo->date_text);
        $this->assertFalse($photo->is_published);
        $this->assertSame($admin->id, $photo->uploaded_by);
        Storage::disk('public')->assertExists($photo->image_path);
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
}
