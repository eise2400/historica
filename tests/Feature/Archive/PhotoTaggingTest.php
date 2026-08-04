<?php

namespace Tests\Feature\Archive;

use App\Models\Person;
use App\Models\Photo;
use App\Models\PhotoPersonTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoTaggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_tag_is_shown_on_photo(): void
    {
        $photo = Photo::factory()->create(['title' => 'Gruppenfoto Feuerwehr']);
        $person = Person::factory()->create(['first_name' => 'Josef', 'last_name' => 'Huber']);
        PhotoPersonTag::create([
            'photo_id' => $photo->id,
            'person_id' => $person->id,
            'x_percent' => 42,
            'y_percent' => 30,
            'status' => PhotoPersonTag::STATUS_APPROVED,
        ]);

        $response = $this->get($photo->url);

        $response->assertOk()->assertSee('Josef Huber');
        // Markers are hidden by default (only revealed via hover/toggle) so a
        // photo full of tagged people never gets buried under visible dots.
        $response->assertSee('person-marker', false);
        $response->assertSee('data-person-id="'.$person->id.'"', false);
        $response->assertSee('Markierungen auf dem Foto anzeigen');
    }

    public function test_marker_toggle_is_not_shown_without_positioned_tags(): void
    {
        $photo = Photo::factory()->create();
        $person = Person::factory()->create();
        PhotoPersonTag::create([
            'photo_id' => $photo->id,
            'person_id' => $person->id,
            'status' => PhotoPersonTag::STATUS_APPROVED,
        ]);

        $response = $this->get($photo->url);

        $response->assertOk()->assertDontSee('Markierungen auf dem Foto anzeigen');
    }

    public function test_anonymous_user_cannot_suggest_tag(): void
    {
        $photo = Photo::factory()->create();

        $response = $this->get($photo->url);

        $response->assertOk()->assertSee('Melden Sie sich an');
    }

    public function test_logged_in_user_can_suggest_tag(): void
    {
        $photo = Photo::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('archive.suggest-tag', $photo), [
            'new_first_name' => 'Anna',
            'new_last_name' => 'Maier',
            'note' => 'vorne links',
        ]);

        $response->assertRedirect($photo->url);

        $tag = PhotoPersonTag::whereHas('person', fn ($q) => $q->where('last_name', 'Maier'))->firstOrFail();
        $this->assertSame(PhotoPersonTag::STATUS_PENDING, $tag->status);
        $this->assertSame($user->id, $tag->suggested_by);
    }

    public function test_suggestion_requires_person_or_new_last_name(): void
    {
        $photo = Photo::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('archive.suggest-tag', $photo), []);

        $response->assertSessionHasErrors('new_last_name');
        $this->assertDatabaseCount(PhotoPersonTag::class, 0);
    }
}
