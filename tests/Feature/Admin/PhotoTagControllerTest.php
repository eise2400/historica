<?php

namespace Tests\Feature\Admin;

use App\Models\Person;
use App\Models\Photo;
use App\Models\PhotoPersonTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoTagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_tag_a_new_person_with_position(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $photo = Photo::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.photos.tags.store', $photo), [
            'new_first_name' => 'Anna',
            'new_last_name' => 'Maier',
            'x_percent' => 55.5,
            'y_percent' => 20,
            'status' => PhotoPersonTag::STATUS_APPROVED,
        ]);

        $response->assertRedirect(route('filament.admin.resources.photos.tags', $photo));

        $tag = PhotoPersonTag::whereHas('person', fn ($q) => $q->where('last_name', 'Maier'))->firstOrFail();
        $this->assertEquals(55.5, $tag->x_percent);
        $this->assertEquals(PhotoPersonTag::STATUS_APPROVED, $tag->status);
    }

    public function test_admin_can_approve_a_pending_suggestion(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $photo = Photo::factory()->create();
        $person = Person::factory()->create();
        $tag = PhotoPersonTag::create([
            'photo_id' => $photo->id,
            'person_id' => $person->id,
            'status' => PhotoPersonTag::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.photos.tags.update', [$photo, $tag]), [
            'status' => PhotoPersonTag::STATUS_APPROVED,
            'x_percent' => 10,
            'y_percent' => 15,
        ]);

        $response->assertRedirect(route('filament.admin.resources.photos.tags', $photo));
        $this->assertEquals(PhotoPersonTag::STATUS_APPROVED, $tag->fresh()->status);
    }

    public function test_regular_user_cannot_manage_tags(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $photo = Photo::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.photos.tags.store', $photo), [
            'new_last_name' => 'Maier',
        ]);

        $response->assertForbidden();
    }
}
