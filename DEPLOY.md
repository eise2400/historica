# Deployment über Plesk Git (Composer läuft auf dem Server, kein SSH nötig)

Composer läuft mittlerweile über den Composer-Button in Plesk (das
`putenv`-Problem war eine gesperrte PHP-Funktion, die inzwischen behoben
ist). Dieser Branch (`deploy`) enthält deshalb **keinen** `vendor/`-Ordner
mehr – der wird beim Einrichten/bei Updates einmalig über Plesks
Composer-Button erzeugt. Node/npm sind auf dem Webhoster weiterhin nicht
bestätigt verfügbar, deshalb bleiben die kompilierten Frontend-Assets
(`public/build/`) wie bisher im Branch eingecheckt.

## Schritt 0 – Composer sicher testen, bevor irgendetwas umgestellt wird

**Wichtig:** Bevor du auf diesem Branch zum ersten Mal „Pull Updates“
klickst, einmal testen, ob Composer auf dem Server wirklich fehlerfrei
läuft – idealerweise auf dem *aktuellen*, noch laufenden Deployment-Stand
(dort ist `vendor/` noch vorhanden, ein fehlschlagender Composer-Aufruf
kann dort nichts kaputt machen, weil der frühere `putenv`-Fehler ganz am
Anfang auftrat, bevor Composer überhaupt etwas in `vendor/` anfasst):

1. In Plesk unter Websites & Domains → [Domain] → **Composer** einmal auf
   **„Install“** oder **„Update“** klicken.
2. Läuft das ohne Fehler durch → weiter mit der Einrichtung unten.
3. Kommt wieder ein Fehler (z. B. der alte `putenv`-Fehler oder ein
   anderer) → **nicht** „Pull Updates“ auf diesem Branch klicken, sondern
   kurz Bescheid geben, dann liefere ich wieder die Variante mit fertig
   mitgeliefertem `vendor/`.

## Einmalige Einrichtung (sobald Schritt 0 erfolgreich war)

1. **Plesk Git verbinden**: Websites & Domains → [Domain] → Git → Repository
   hinzufügen, als Quelle dieses GitHub-Repository eintragen, Branch
   `deploy` wählen. Als Ziel-Verzeichnis das Domain-Stammverzeichnis wählen.
2. **Document Root** der Domain auf den Unterordner `public/` setzen
   (Hosting-Einstellungen der Domain).
3. **Composer-Button** in Plesk auf „Install“ klicken. Das erzeugt
   `vendor/` und – über die in `composer.json` hinterlegten
   Post-Install-Hooks – automatisch auch
   `bootstrap/cache/packages.php`/`services.php`
   (führt intern `artisan package:discover` und `artisan filament:upgrade`
   aus).
4. **Datenbank anlegen** (MySQL/MariaDB) und `historica-datenbank-import.sql`
   über phpMyAdmin importieren (enthält Schema + Startdaten: Kategorien,
   Vereinsseiten-Texte, Webmaster-Konto `webmaster@historica-deing.de` /
   `historica-webmaster` – Passwort nach dem ersten Login ändern!).
5. **`.env` anlegen**: `.env.example` als Vorlage per Datei-Manager zu `.env`
   kopieren, `APP_KEY` setzen (einmalig lokal generieren und eintragen, z. B.
   mit `php artisan key:generate --show` auf einem beliebigen Rechner mit
   PHP), `APP_URL` und die Datenbank-Zugangsdaten eintragen.
   `.env` ist in `.gitignore` und wird von `git pull` **nie** überschrieben.
6. **Schreibrechte** setzen (falls nötig, je nach Hoster oft schon korrekt):
   `storage/`, `storage/framework/*`, `storage/logs/`, `bootstrap/cache/`,
   `public/storage/`.

`artisan migrate` läuft weiterhin nicht auf dem Server (kein CLI-Zugriff),
deshalb bleibt der Weg über die SQL-Importdatei bestehen, auch wenn
Composer jetzt funktioniert.

## Künftige Updates

Sobald eine neue Version fertig ist, wird sie auf den `deploy`-Branch
gepusht. Danach in Plesk unter Git auf **„Pull Updates“** klicken, und
**nur falls sich `composer.json`/`composer.lock` geändert haben**
zusätzlich noch einmal den Composer-Button. `.env` und Datenbank bleiben
dabei unangetastet.

Falls eine Änderung neue Migrationen enthält, wird zusätzlich ein neues
SQL-Update-Skript bereitgestellt, das einmalig über phpMyAdmin eingespielt
werden muss.

## Warum trotzdem noch ein eigener Branch?

Der normale Entwicklungs-Branch (`claude/historica-deing-website-b8nwpl` /
`main`) ignoriert auch `public/build/` (Standard für Laravel-Projekte, da
der Ordner aus `package.json` reproduzierbar ist). Solange Node/npm auf dem
Webhoster nicht verfügbar sind, bleibt `public/build/` auf diesem Branch
zusätzlich eingecheckt – `vendor/` dagegen nicht mehr, das übernimmt jetzt
Composer auf dem Server selbst.
