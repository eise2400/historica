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

    public function test_previous_and_next_links_follow_the_archive_order(): void
    {
        $oldest = Photo::factory()->create(['title' => 'Ältestes', 'created_at' => now()->subDays(3)]);
        $middle = Photo::factory()->create(['title' => 'Mittleres', 'created_at' => now()->subDays(2)]);
        $newest = Photo::factory()->create(['title' => 'Neuestes', 'created_at' => now()->subDay()]);

        $response = $this->get($middle->url);

        $response->assertOk()
            ->assertSee(route('archive.show', $newest->slug), false)
            ->assertSee(route('archive.show', $oldest->slug), false);
    }

    public function test_newest_photo_has_no_previous_link(): void
    {
        Photo::factory()->create(['created_at' => now()->subDays(2)]);
        $newest = Photo::factory()->create(['created_at' => now()->subDay()]);

        $response = $this->get($newest->url);

        $response->assertOk()->assertDontSee('Vorheriges Foto');
    }

    public function test_oldest_photo_has_no_next_link(): void
    {
        $oldest = Photo::factory()->create(['created_at' => now()->subDays(2)]);
        Photo::factory()->create(['created_at' => now()->subDay()]);

        $response = $this->get($oldest->url);

        $response->assertOk()->assertDontSee('Nächstes Foto');
    }

    public function test_previous_and_next_stay_within_the_active_category_filter(): void
    {
        $catA = Category::factory()->create();
        $catB = Category::factory()->create();

        $a1 = Photo::factory()->create(['category_id' => $catA->id, 'created_at' => now()->subDays(4)]);
        Photo::factory()->create(['category_id' => $catB->id, 'created_at' => now()->subDays(3)]);
        $a2 = Photo::factory()->create(['category_id' => $catA->id, 'created_at' => now()->subDays(2)]);
        Photo::factory()->create(['category_id' => $catB->id, 'created_at' => now()->subDay()]);

        $response = $this->get($a2->url.'?kategorie='.$catA->slug);

        $response->assertOk()
            ->assertSee(route('archive.show', $a1->slug), false)
            ->assertSee('Nächstes Foto')
            ->assertDontSee('Vorheriges Foto');
    }
}
