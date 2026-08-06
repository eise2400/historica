# Deployment über Plesk Git (ohne SSH/Composer/Artisan auf dem Server)

Dieser Branch (`deploy`) enthält zusätzlich zum Quellcode bereits den fertig
gebauten `vendor/`-Ordner, die kompilierten Frontend-Assets (`public/build/`)
und den Paket-Discovery-Cache (`bootstrap/cache/packages.php`,
`services.php`). Damit reicht ein `git pull` – Composer, npm und `artisan`
müssen auf dem Webhoster nie ausgeführt werden.

## Einmalige Einrichtung

1. **Plesk Git verbinden**: Websites & Domains → [Domain] → Git → Repository
   hinzufügen, als Quelle dieses GitHub-Repository eintragen, Branch
   `deploy` wählen. Als Ziel-Verzeichnis das Domain-Stammverzeichnis wählen.
2. **Document Root** der Domain auf den Unterordner `public/` setzen
   (Hosting-Einstellungen der Domain).
3. **Datenbank anlegen** (MySQL/MariaDB) und `historica-datenbank-import.sql`
   über phpMyAdmin importieren (enthält Schema + Startdaten: Kategorien,
   Vereinsseiten-Texte, Webmaster-Konto `webmaster@historica-deing.de` /
   `historica-webmaster` – Passwort nach dem ersten Login ändern!).
4. **`.env` anlegen**: `.env.example` als Vorlage per Datei-Manager zu `.env`
   kopieren, `APP_KEY` setzen (einmalig lokal generieren und eintragen, z. B.
   mit `php artisan key:generate --show` auf einem beliebigen Rechner mit
   PHP), `APP_URL` setzen. **Wichtig:** `.env.example` ist standardmäßig auf
   SQLite eingestellt (für lokale Entwicklung) – für den Server unbedingt
   `DB_CONNECTION=mysql` setzen (die Zeile ist als Kommentar bereits
   vorhanden, nur einkommentieren) und `DB_HOST`/`DB_DATABASE`/`DB_USERNAME`/
   `DB_PASSWORD` mit den Zugangsdaten aus Schritt 3 eintragen.
   `SESSION_DRIVER`/`CACHE_STORE`/`QUEUE_CONNECTION` bitte auf `file`/`file`/
   `sync` **belassen** – `historica-datenbank-import.sql` enthält bewusst
   keine `sessions`-/`cache`-/`jobs`-Tabellen.
   `.env` ist in `.gitignore` und wird von `git pull` **nie** überschrieben.
   **`APP_URL` muss exakt mit `https://` beginnen** (z. B.
   `APP_URL=https://www.historica-deing.de`), sofern die Seite über HTTPS
   aufgerufen wird (Normalfall). Alle Foto- und Bild-URLs werden direkt aus
   `APP_URL` gebildet (`config/filesystems.php`, `public`-Disk) – steht dort
   versehentlich `http://`, während die Seite selbst über `https://`
   aufgerufen wird, blockiert der Browser das Nachladen der Bilder als
   „mixed content“ (Firefox: „blocked loading mixed active content“), und
   z. B. im Foto-Bearbeiten-Formular wird das Bild nicht angezeigt.
5. **Schreibrechte** setzen (falls nötig, je nach Hoster oft schon korrekt):
   `storage/`, `storage/app/private/`, `storage/framework/*`, `storage/logs/`,
   `bootstrap/cache/`, `public/storage/`. **Wichtig:** `storage/app/private/`
   wird gerne vergessen, ist aber Pflicht – dort legt Livewire beim
   Einzel-Foto-Upload (Formular „Foto bearbeiten“/„Foto anlegen“) temporäre
   Dateien ab, *bevor* das Formular abgeschickt wird. Fehlen dort die
   Schreibrechte, hängt der Upload lautlos fest und der „Speichern“-Button
   scheint nicht zu reagieren, obwohl technisch alles korrekt eingerichtet
   ist. Der **Sammelupload** verwendet dagegen kein Livewire-Temp-Verzeichnis
   mehr (klassisches Formular, siehe `public/.user.ini`-Hinweis unten) und
   ist von dieser Einschränkung nicht betroffen.
6. **`public/.user.ini`** wird automatisch mit ausgeliefert und muss nicht
   manuell angepasst werden, sofern der Hoster `.user.ini`-Dateien
   respektiert (bei Plesk/PHP-FPM normalerweise der Fall, siehe Kommentar in
   der Datei). Sie hebt `upload_max_filesize`/`post_max_size`/
   `max_file_uploads`/`memory_limit`/`max_execution_time`/`max_input_time`
   über die PHP-Standardwerte an – nötig, weil der Sammelupload Fotos in
   Gruppen von 15 Dateien pro Anfrage sendet (`post_max_size = 400M`,
   `max_file_uploads = 20`). Falls der Sammelupload bei sehr großen oder
   sehr vielen Dateien dennoch mit einem Server-Fehler abbricht, sind das
   die ersten Werte, die geprüft/erhöht werden sollten (Wirksamkeit oft erst
   nach `user_ini.cache_ttl`, üblicherweise bis zu 5 Minuten, oder einem
   PHP-FPM-Neustart durch den Hoster).

## Künftige Updates

Sobald eine neue Version fertig ist, wird sie auf den `deploy`-Branch
gepusht. Danach reicht in Plesk unter Git ein Klick auf **"Pull Updates"** –
`.env` und die Datenbank bleiben unangetastet, nur der Code (inkl. `vendor/`
und kompilierter Assets) wird aktualisiert.

Falls eine Änderung neue Migrationen enthält, wird zusätzlich ein neues
SQL-Update-Skript bereitgestellt, das einmalig über phpMyAdmin eingespielt
werden muss (da `artisan migrate` auf dem Server nicht laufen kann).

## Warum ein eigener Branch?

Der normale Entwicklungs-Branch (`claude/historica-deing-website-b8nwpl` /
`main`) ignoriert `vendor/` und `public/build/` bewusst (Standard für
Laravel-Projekte, da diese Ordner aus `composer.json`/`package.json`
reproduzierbar sind). Für Hosting ohne Build-Möglichkeit auf dem Server
werden sie auf diesem Branch stattdessen mit eingecheckt.
