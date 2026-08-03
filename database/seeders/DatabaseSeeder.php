<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        collect([
            ['name' => 'Ortsansichten', 'order' => 1, 'description' => 'Historische Ansichten von Teugn und seinen Ortsteilen.'],
            ['name' => 'Vereine', 'order' => 2, 'description' => 'Fotos rund um das Vereinsleben in Teugn.'],
            ['name' => 'Landwirtschaft', 'order' => 3, 'description' => 'Landwirtschaft und dörfliches Arbeitsleben.'],
            ['name' => 'Personen & Familien', 'order' => 4, 'description' => 'Portraits und Familienfotos.'],
            ['name' => 'Feste & Feiern', 'order' => 5, 'description' => 'Kirchweih, Umzüge und andere Feierlichkeiten.'],
        ])->each(fn (array $attrs) => Category::firstOrCreate(['name' => $attrs['name']], $attrs));

        collect([
            [
                'slug' => 'impressum',
                'title' => 'Impressum',
                'content' => '<p>Historica Deing e.V.<br>Vorsitzende(r): [Name einfügen]<br>Teugn</p>'
                    .'<p>E-Mail: info@historica-deing.de</p>'
                    .'<p>Vertreten durch den Vorstand gemäß § 26 BGB. Vereinsregister: [Registergericht, Registernummer einfügen].</p>'
                    .'<p><em>Dieser Text kann im Verwaltungsbereich unter „Seiten“ bearbeitet werden.</em></p>',
            ],
            [
                'slug' => 'datenschutz',
                'title' => 'Datenschutz',
                'content' => '<p>Der Schutz Ihrer personenbezogenen Daten ist uns wichtig. Informationen zur Verarbeitung Ihrer '
                    .'Daten (z. B. bei Nutzung des Kontaktformulars oder bei der Registrierung) finden Sie hier.</p>'
                    .'<p><em>Dieser Text kann im Verwaltungsbereich unter „Seiten“ bearbeitet werden.</em></p>',
            ],
            [
                'slug' => 'satzung',
                'title' => 'Satzung',
                'content' => '<p>Die Satzung des Historica Deing e.V. regelt Zweck, Organisation und Mitgliedschaft des Vereins.</p>'
                    .'<p><em>Bitte laden Sie im Verwaltungsbereich die aktuelle Satzung als PDF hoch, damit sie hier zum '
                    .'Download angeboten wird.</em></p>',
            ],
            [
                'slug' => 'aufnahmeantrag',
                'title' => 'Aufnahmeantrag',
                'content' => '<p>Wir freuen uns über Ihr Interesse an einer Mitgliedschaft bei Historica Deing e.V. Sie '
                    .'können den Aufnahmeantrag online ausfüllen oder als PDF herunterladen und postalisch einreichen.</p>',
            ],
        ])->each(fn (array $attrs) => SitePage::firstOrCreate(['slug' => $attrs['slug']], $attrs));

        User::firstOrCreate(
            ['email' => 'webmaster@historica-deing.de'],
            [
                'name' => 'Webmaster',
                'password' => Hash::make('historica-webmaster'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
