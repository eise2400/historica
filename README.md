# Historica Deing e.V. – Vereinswebsite

Website für den Geschichts- und Heimatverein **Historica Deing e.V.** (Teugn),
gebaut mit **Laravel** (PHP) und **Filament** als Admin-Oberfläche. Neben den
klassischen Vereinsseiten (Impressum, Datenschutz, Satzung, Aufnahmeantrag,
Kontakt) steht ein **Fotoarchiv** im Mittelpunkt, in dem der Webmaster
historische Fotos hochladen, kategorisieren, zeitlich und räumlich einordnen
sowie mit Personen verknüpfen kann. Das Archiv ist auf mehrere tausend Fotos
ausgelegt: automatisch erzeugte Miniaturbilder halten Listenseiten schnell,
und ein Sammelupload legt viele Fotos in einem Durchgang an.

## Warum Laravel + Filament?

- **Filament** liefert eine fertige, gut administrierbare Oberfläche für den
  Webmaster (vergleichbar mit Django Admin) – CRUD, Bildupload, Filter,
  Relationen – ohne ein separates CMS entwickeln zu müssen. Für das Markieren
  von Personen auf Gruppenfotos wurde eine **Klick-zum-Markieren-Funktion**
  ergänzt (siehe unten).
- **Laravel Breeze** stellt die öffentliche Registrierung/Anmeldung bereit,
  damit künftig auch registrierte Besucher Personen auf Fotos benennen
  können (als Vorschlag, der vom Webmaster freigegeben wird).
- **Eloquent & Migrations**: saubere, versionierte Datenbankstruktur.
- Sehr weit verbreitetes PHP-Framework, dadurch günstiges Standard-Hosting
  möglich und langfristig gut wartbar.

## Projektstruktur

```
app/Models/                 Category, Location, Person, Photo, PhotoPersonTag,
                             SitePage, ContactMessage, MembershipApplication, User
app/Http/Controllers/Public/ Startseite, Verein-Seiten, Kontakt, Aufnahmeantrag
app/Http/Controllers/Archive/ Fotoarchiv (Liste, Detail, Personen)
app/Http/Controllers/Admin/ Personen-Markierung (Klick-zum-Markieren-Endpunkte)
app/Filament/Resources/     Admin-Oberfläche (Kategorien, Orte, Personen, Fotos,
                             Seiten, Kontaktanfragen, Aufnahmeanträge)
resources/views/            Blade-Templates (public/, archive/, layouts/, filament/)
database/migrations/        Datenbankschema
database/seeders/           Beispiel-Kategorien, Vereinsseiten, Webmaster-Konto
```

### Datenmodell des Fotoarchivs

- **Category** (Kategorie): z. B. Ortsansichten, Vereine, Landwirtschaft –
  frei im Admin erweiterbar.
- **Location** (Ort): räumliche Einordnung, optional mit Koordinaten.
- **Person**: Vor-/Nachname, Geburtsname, Geburts-/Sterbejahr, Anmerkungen.
- **Photo**: Bild, Titel, Beschreibung, Kategorie, Ort, zeitliche Einordnung
  (`date_from`/`date_to` und/oder freier Text wie „um 1930“), Quelle/
  Bildrechte, Inventarnummer, Veröffentlichungsstatus.
- **PhotoPersonTag**: verknüpft eine Person mit einem Foto, optional mit
  prozentualer Position (`x_percent`/`y_percent`) für Gruppenfotos, sowie
  einem Status (freigegeben/ausstehend/abgelehnt) – so lassen sich
  Vorschläge registrierter Nutzer moderieren.

Beim Speichern eines Fotos wird automatisch ein 480×360-Miniaturbild erzeugt
(`thumbnail_path`, via [Intervention Image](https://image.intervention.io/)).
Listenseiten (Startseite, Fotoarchiv, Personenseiten, Admin-Tabelle) zeigen
ausschließlich diese Miniaturbilder – erst die Detailansicht eines Fotos lädt
das Original. Das hält die Seite auch bei mehreren tausend Fotos schnell,
ohne bei jedem Seitenaufruf große Originaldateien auszuliefern.

**Personen-Markierungen** werden auf der Fotoseite standardmäßig *nicht*
sichtbar über das Bild gelegt – bei Gruppenfotos mit vielen Personen würde
das Foto sonst unter lauter Kreisen verschwinden. Stattdessen: ein Klick auf
„Markierungen anzeigen“ blendet dezente Ringe ein, und das Überfahren eines
Namens in der Liste rechts hebt die passende Markierung im Bild hervor (und
umgekehrt).

## Lokale Einrichtung

Voraussetzung: PHP 8.2+, Composer, Node.js

```bash
composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

touch database/database.sqlite   # oder DB_CONNECTION=mysql/postgresql in .env setzen
php artisan migrate --seed       # legt Kategorien, Vereinsseiten & Webmaster-Konto an

php artisan serve
```

Kein `artisan storage:link` nötig: der `public`-Filesystem-Disk zeigt direkt
auf `public/storage/`, Uploads landen also ohne Symlink im Webroot – wichtig
für Hosting ohne SSH-Zugriff, wo dieser Befehl nie ausgeführt werden könnte.

Die Beispiel-Daten legen ein Webmaster-Konto an:
**webmaster@historica-deing.de** / **historica-webmaster** (Passwort nach dem
ersten Login ändern!). Anschließend ist die Seite unter
<http://127.0.0.1:8000/> erreichbar, die Verwaltung unter
<http://127.0.0.1:8000/admin/>.

## Bedienung für den Webmaster (Admin)

1. Im Admin unter **Fotoarchiv → Kategorien / Orte / Personen** die
   Stammdaten pflegen (einige Kategorien sind bereits über den Seeder
   angelegt).
2. Unter **Fotoarchiv → Fotos → Neu** ein einzelnes Bild hochladen, Kategorie,
   Ort und Datierung angeben. Für viele Fotos auf einmal steht daneben
   **„Sammelupload“** bereit: Kategorie/Ort/Datierung gelten dort für den
   ganzen Stapel (bis zu 300 Dateien), die Fotos werden als unveröffentlichter
   Entwurf angelegt (Titel = Dateiname) und danach einzeln geprüft, ergänzt
   und veröffentlicht. Bei sehr großen Mengen empfiehlt sich ein Vorgehen in
   Gruppen von 50–100 Dateien.
3. In der Fotoliste über die Aktion **„Personen markieren“** gelangt man zur
   Markierungsseite: bei der gewünschten Person auf **„Position setzen“**
   klicken und anschließend auf die Stelle im Foto klicken – die Koordinaten
   werden automatisch übernommen. Danach bei der Person auf „Speichern“
   klicken.
4. Vorschläge registrierter Nutzer erscheinen auf derselben Seite mit Status
   „Ausstehend“ und können dort geprüft und auf „Freigegeben“ gesetzt werden.
5. Unter **Verein → Seiten** lassen sich Impressum, Datenschutz, Satzung und
   Aufnahmeantrag-Text bearbeiten sowie PDF-Dokumente zum Download hinterlegen.
   Unter **Verein → Kontaktanfragen / Aufnahmeanträge** landen die
   eingereichten Formulare.

## Tests

```bash
php artisan test
```

## Deployment (Kurzfassung)

**Mit SSH/CLI-Zugriff** auf den Server:

- `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` generieren,
  `APP_URL` setzen, `DB_CONNECTION=mysql` (oder `pgsql`) samt Zugangsdaten.
- `composer install --no-dev --optimize-autoloader`,
  `npm run build`, `php artisan migrate --force`,
  `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
- Klassisches Shared-/Managed-PHP-Hosting (Apache/Nginx + PHP-FPM) reicht für
  diese Anwendung aus; ein Queue-Worker wird aktuell nicht benötigt.

**Ohne SSH-Zugriff** (z. B. viele Plesk-Shared-Hosting-Pakete): siehe
[`DEPLOY.md`](DEPLOY.md) auf dem `deploy`-Branch. Composer läuft dort über
Plesks eigenen Composer-Button (kein SSH nötig); der Branch checkt deshalb
nur noch die kompilierten Frontend-Assets (`public/build/`) mit ein, nicht
mehr `vendor/` – Node/npm sind auf dem Zielhost weiterhin nicht bestätigt
verfügbar. Der Branch lässt sich über Plesk Git per „Pull Updates“
ausrollen; `artisan migrate` läuft weiterhin nicht auf dem Server, daher
liegt dort zusätzlich ein SQL-Importskript für die Datenbank bei.

`public/.user.ini` erhöht Upload- und Speicherlimits (wichtig für den
Sammelupload) auf Hosts, die `.user.ini`-Overrides zulassen – kein
SSH-Zugriff nötig, wird von PHP-FPM automatisch eingelesen.
