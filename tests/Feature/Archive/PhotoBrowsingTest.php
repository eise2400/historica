<?php

namespace Tests\Feature\Archive;

use App\Models\Category;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_photos_are_shown(): void
    {
        Photo::factory()->create(['title' => 'Sichtbar', 'is_published' => true]);
        Photo::factory()->create(['title' => 'Versteckt', 'is_published' => false]);

        $response = $this->get(route('archive.index'));

        $response->assertOk()->assertSee('Sichtbar')->assertDontSee('Versteckt');
    }

    public function test_category_filter(): void
    {
        $catA = Category::factory()->create(['name' => 'Vereine']);
        $catB = Category::factory()->create(['name' => 'Landwirtschaft']);
        Photo::factory()->create(['title' => 'Vereinsfoto', 'category_id' => $catA->id]);
        Photo::factory()->create(['title' => 'Erntefoto', 'category_id' => $catB->id]);

        $response = $this->get(route('archive.index', ['kategorie' => $catA->slug]));

        $response->assertOk()->assertSee('Vereinsfoto')->assertDontSee('Erntefoto');
    }

    public function test_unpublished_photo_detail_returns_404(): void
    {
        $photo = Photo::factory()->create(['is_published' => false]);

        $response = $this->get($photo->url);

        $response->assertNotFound();
    }
}
