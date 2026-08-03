<?php

namespace Tests\Feature\Public;

use App\Models\ContactMessage;
use App\Models\MembershipApplication;
use App\Models\SitePage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_page_renders(): void
    {
        SitePage::create(['slug' => 'impressum', 'title' => 'Impressum', 'content' => '<p>Testinhalt</p>']);

        $response = $this->get(route('impressum'));

        $response->assertOk()->assertSee('Testinhalt', false);
    }

    public function test_missing_site_page_returns_404(): void
    {
        $response = $this->get(route('satzung'));

        $response->assertNotFound();
    }

    public function test_submitting_contact_form_creates_message(): void
    {
        $response = $this->post(route('kontakt.store'), [
            'name' => 'Maria Bauer',
            'email' => 'maria@example.com',
            'subject' => 'Frage',
            'message' => 'Hallo!',
        ]);

        $response->assertRedirect(route('kontakt'));
        $this->assertDatabaseCount(ContactMessage::class, 1);
    }

    public function test_submitting_membership_application_creates_record(): void
    {
        $response = $this->post(route('aufnahmeantrag.store'), [
            'first_name' => 'Karl',
            'last_name' => 'Wagner',
            'street' => 'Dorfstraße 1',
            'postal_code' => '93356',
            'city' => 'Teugn',
            'email' => 'karl@example.com',
        ]);

        $response->assertRedirect(route('aufnahmeantrag'));
        $this->assertDatabaseCount(MembershipApplication::class, 1);
    }
}
