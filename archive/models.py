from django.conf import settings
from django.db import models
from django.urls import reverse
from django.utils.text import slugify


class Category(models.Model):
    """Kategorie für das Fotoarchiv, z. B. Ortsansichten, Vereine, Landwirtschaft."""

    name = models.CharField("Name", max_length=100, unique=True)
    slug = models.SlugField("Slug", max_length=110, unique=True, blank=True)
    description = models.TextField("Beschreibung", blank=True)
    order = models.PositiveIntegerField("Reihenfolge", default=0)

    class Meta:
        verbose_name = "Kategorie"
        verbose_name_plural = "Kategorien"
        ordering = ["order", "name"]

    def __str__(self):
        return self.name

    def save(self, *args, **kwargs):
        if not self.slug:
            self.slug = slugify(self.name)
        super().save(*args, **kwargs)

    def get_absolute_url(self):
        return reverse("archive:photo_list") + f"?kategorie={self.slug}"


class Location(models.Model):
    """Räumliche Einordnung eines Fotos (Ort, Weiler, Flurname, Gebäude, ...)."""

    name = models.CharField("Name", max_length=150, unique=True)
    slug = models.SlugField("Slug", max_length=160, unique=True, blank=True)
    description = models.TextField("Beschreibung", blank=True)
    latitude = models.DecimalField(
        "Breitengrad", max_digits=9, decimal_places=6, blank=True, null=True
    )
    longitude = models.DecimalField(
        "Längengrad", max_digits=9, decimal_places=6, blank=True, null=True
    )

    class Meta:
        verbose_name = "Ort"
        verbose_name_plural = "Orte"
        ordering = ["name"]

    def __str__(self):
        return self.name

    def save(self, *args, **kwargs):
        if not self.slug:
            self.slug = slugify(self.name)
        super().save(*args, **kwargs)

    @property
    def has_coordinates(self):
        return self.latitude is not None and self.longitude is not None


class Person(models.Model):
    """Eine auf Fotos benennbare Person."""

    first_name = models.CharField("Vorname", max_length=100, blank=True)
    last_name = models.CharField("Nachname", max_length=100)
    maiden_name = models.CharField("Geburtsname", max_length=100, blank=True)
    birth_year = models.PositiveSmallIntegerField("Geburtsjahr", blank=True, null=True)
    death_year = models.PositiveSmallIntegerField("Sterbejahr", blank=True, null=True)
    notes = models.TextField("Anmerkungen", blank=True)

    class Meta:
        verbose_name = "Person"
        verbose_name_plural = "Personen"
        ordering = ["last_name", "first_name"]

    def __str__(self):
        name = f"{self.first_name} {self.last_name}".strip()
        if self.maiden_name:
            name += f" geb. {self.maiden_name}"
        years = self.year_range
        return f"{name} ({years})" if years else name

    @property
    def year_range(self):
        if self.birth_year and self.death_year:
            return f"{self.birth_year}–{self.death_year}"
        if self.birth_year:
            return f"* {self.birth_year}"
        if self.death_year:
            return f"† {self.death_year}"
        return ""


class Photo(models.Model):
    """Ein Foto im Archiv mit zeitlicher und räumlicher Einordnung."""

    title = models.CharField("Titel", max_length=200)
    slug = models.SlugField("Slug", max_length=220, unique=True, blank=True)
    image = models.ImageField("Bild", upload_to="photos/%Y/%m/")
    description = models.TextField("Beschreibung", blank=True)

    category = models.ForeignKey(
        Category,
        verbose_name="Kategorie",
        related_name="photos",
        on_delete=models.PROTECT,
    )
    location = models.ForeignKey(
        Location,
        verbose_name="Ort",
        related_name="photos",
        on_delete=models.SET_NULL,
        blank=True,
        null=True,
    )

    # Zeitliche Einordnung: entweder ein Zeitraum (von/bis) und/oder ein
    # frei formulierter Text, da bei historischen Fotos oft nur eine grobe
    # Einordnung ("um 1930er Jahre") möglich ist.
    date_from = models.DateField("Datum von", blank=True, null=True)
    date_to = models.DateField("Datum bis", blank=True, null=True)
    date_text = models.CharField(
        "Datierung (Text)",
        max_length=100,
        blank=True,
        help_text="Frei formulierte Datierung, z. B. 'um 1930' oder '1950er Jahre'.",
    )

    source = models.CharField(
        "Quelle / Bildrechte", max_length=255, blank=True,
        help_text="z. B. Fotograf, Nachlass, Privatbesitz.",
    )
    inventory_number = models.CharField("Inventarnummer", max_length=50, blank=True)

    is_published = models.BooleanField(
        "Veröffentlicht", default=True,
        help_text="Nur veröffentlichte Fotos sind auf der öffentlichen Seite sichtbar.",
    )

    uploaded_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        verbose_name="Hochgeladen von",
        related_name="uploaded_photos",
        on_delete=models.SET_NULL,
        blank=True,
        null=True,
    )
    created_at = models.DateTimeField("Hochgeladen am", auto_now_add=True)
    updated_at = models.DateTimeField("Zuletzt geändert", auto_now=True)

    persons = models.ManyToManyField(
        Person,
        verbose_name="Personen",
        through="PhotoPersonTag",
        related_name="photos",
        blank=True,
    )

    class Meta:
        verbose_name = "Foto"
        verbose_name_plural = "Fotos"
        ordering = ["-created_at"]

    def __str__(self):
        return self.title

    def save(self, *args, **kwargs):
        if not self.slug:
            base_slug = slugify(self.title) or "foto"
            slug = base_slug
            i = 1
            while Photo.objects.filter(slug=slug).exclude(pk=self.pk).exists():
                i += 1
                slug = f"{base_slug}-{i}"
            self.slug = slug
        super().save(*args, **kwargs)

    def get_absolute_url(self):
        return reverse("archive:photo_detail", kwargs={"slug": self.slug})

    @property
    def date_display(self):
        if self.date_text:
            return self.date_text
        if self.date_from and self.date_to and self.date_from != self.date_to:
            return f"{self.date_from:%d.%m.%Y} – {self.date_to:%d.%m.%Y}"
        if self.date_from:
            return f"{self.date_from:%d.%m.%Y}"
        return ""

    @property
    def approved_tags(self):
        return self.person_tags.filter(status=PhotoPersonTag.Status.APPROVED).select_related("person")


class PhotoPersonTag(models.Model):
    """Verknüpfung zwischen einem Foto und einer darauf abgebildeten Person.

    Für Gruppenfotos kann die Position der Person auf dem Bild als
    prozentualer x/y-Wert hinterlegt werden, damit sie im Frontend markiert
    werden kann. Vorschläge von registrierten Nutzern werden bis zur
    Freigabe durch den Webmaster als 'ausstehend' geführt.
    """

    class Status(models.TextChoices):
        APPROVED = "approved", "Freigegeben"
        PENDING = "pending", "Ausstehend"
        REJECTED = "rejected", "Abgelehnt"

    photo = models.ForeignKey(Photo, related_name="person_tags", on_delete=models.CASCADE)
    person = models.ForeignKey(Person, related_name="tags", on_delete=models.CASCADE)

    x_percent = models.DecimalField(
        "Position X (%)", max_digits=5, decimal_places=2, blank=True, null=True,
        help_text="Horizontale Position auf dem Bild in Prozent (0-100), optional.",
    )
    y_percent = models.DecimalField(
        "Position Y (%)", max_digits=5, decimal_places=2, blank=True, null=True,
        help_text="Vertikale Position auf dem Bild in Prozent (0-100), optional.",
    )
    note = models.CharField(
        "Anmerkung", max_length=200, blank=True,
        help_text="z. B. 'hintere Reihe, 3. von links'.",
    )

    status = models.CharField(
        "Status", max_length=10, choices=Status.choices, default=Status.APPROVED
    )
    suggested_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        verbose_name="Vorgeschlagen von",
        related_name="suggested_tags",
        on_delete=models.SET_NULL,
        blank=True,
        null=True,
    )
    reviewed_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        verbose_name="Geprüft von",
        related_name="reviewed_tags",
        on_delete=models.SET_NULL,
        blank=True,
        null=True,
    )
    created_at = models.DateTimeField("Erstellt am", auto_now_add=True)

    class Meta:
        verbose_name = "Personen-Markierung"
        verbose_name_plural = "Personen-Markierungen"
        ordering = ["created_at"]
        constraints = [
            models.UniqueConstraint(
                fields=["photo", "person"], name="unique_person_per_photo"
            )
        ]

    def __str__(self):
        return f"{self.person} auf {self.photo}"
