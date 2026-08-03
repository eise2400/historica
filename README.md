# Historica Deing e.V. – Vereinswebsite

Website für den Geschichts- und Heimatverein **Historica Deing e.V.** (Teugn),
gebaut mit **Django** (Python). Neben den klassischen Vereinsseiten
(Impressum, Datenschutz, Satzung, Aufnahmeantrag, Kontakt) steht ein
**Fotoarchiv** im Mittelpunkt, in dem der Webmaster historische Fotos
hochladen, kategorisieren, zeitlich und räumlich einordnen sowie mit
Personen verknüpfen kann.

## Warum Django?

Django wurde gewählt, weil es "batteries included" ist und genau die hier
benötigten Bausteine mitbringt:

- **Django Admin**: eine fertige, gut administrierbare Oberfläche für den
  Webmaster – kein separates CMS nötig. Wurde um eine
  **Klick-zum-Markieren-Funktion** erweitert (siehe unten).
- **Auth-System**: Benutzerregistrierung/-anmeldung ist bereits vorbereitet,
  damit künftig auch registrierte Besucher Personen auf Fotos benennen
  können (als Vorschlag, der vom Webmaster freigegeben wird).
- **ORM & Migrations**: saubere, versionierte Datenbankstruktur.
- Große Verbreitung, viel Dokumentation, langfristig gut wartbar.

## Projektstruktur

```
historica/          Projekteinstellungen (settings.py, urls.py)
core/                Startseite, Impressum, Datenschutz, Satzung,
                     Aufnahmeantrag, Kontaktformular
archive/             Fotoarchiv: Kategorien, Orte, Personen, Fotos,
                     Personen-Markierungen
accounts/            Registrierung/Login, Profil mit eigenen Vorschlägen
templates/, static/  projektweite Templates & Styles (Bootstrap 5)
```

### Datenmodell des Fotoarchivs (`archive/models.py`)

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

## Lokale Einrichtung

Voraussetzung: Python 3.11+

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt

cp .env.example .env        # Werte bei Bedarf anpassen

python manage.py migrate
python manage.py loaddata initial_categories initial_pages   # Beispiel-Kategorien & Seiten
python manage.py createsuperuser                              # Webmaster-Konto anlegen

python manage.py runserver
```

Anschließend ist die Seite unter <http://127.0.0.1:8000/> erreichbar, die
Verwaltung unter <http://127.0.0.1:8000/admin/>.

## Bedienung für den Webmaster (Admin)

1. Im Admin unter **Fotoarchiv → Kategorien / Orte / Personen** die
   Stammdaten pflegen (einige Kategorien sind bereits per Fixture
   angelegt).
2. Unter **Fotoarchiv → Fotos → Foto hinzufügen** ein Bild hochladen,
   Kategorie, Ort und Datierung angeben.
3. Nach dem Speichern erscheint eine **Bildvorschau**. Für Gruppenfotos:
   bei der gewünschten Person in der Personenliste auf **„Position
   setzen“** klicken und anschließend auf die Stelle im Foto klicken – die
   Koordinaten werden automatisch übernommen und als Markierung
   angezeigt.
4. Vorschläge registrierter Nutzer erscheinen als Personen-Markierung mit
   Status „Ausstehend“ und können im Admin geprüft und auf „Freigegeben“
   gesetzt werden.

## Tests

```bash
python manage.py test
```

## Deployment (Kurzfassung)

- `DJANGO_DEBUG=False`, `DJANGO_SECRET_KEY`, `DJANGO_ALLOWED_HOSTS` und
  `DJANGO_CSRF_TRUSTED_ORIGINS` setzen.
- Für eine robustere Datenbank `DB_ENGINE=postgresql` samt Zugangsdaten
  setzen (SQLite reicht für kleine Vereinsseiten, PostgreSQL empfiehlt
  sich für den Produktivbetrieb).
- `python manage.py collectstatic` ausführen (Static-Files werden über
  [WhiteNoise](https://whitenoise.readthedocs.io/) ausgeliefert).
- Als WSGI-Server dient `gunicorn historica.wsgi:application` (siehe
  `Procfile` für z. B. Heroku/Render-artige Plattformen).
- Für Uploads (`media/`) und die Datenbank sollte bei Plattformen mit
  vergänglichem Dateisystem persistenter Speicher (Volume) bzw. eine
  externe Datenbank eingerichtet werden.
