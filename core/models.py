from django.db import models


class SitePage(models.Model):
    """Editable content for the association's static pages.

    The webmaster can edit texts like Impressum, Satzung or Kontakt-Intro
    directly in the admin without needing a code deployment.
    """

    slug = models.SlugField(
        max_length=50,
        unique=True,
        help_text="Interner Bezeichner, z. B. 'impressum', 'satzung', 'kontakt'.",
    )
    title = models.CharField("Titel", max_length=200)
    content = models.TextField(
        "Inhalt",
        help_text="HTML ist erlaubt (z. B. <p>, <h2>, <ul>, <a>).",
        blank=True,
    )
    document = models.FileField(
        "Dokument (PDF)",
        upload_to="documents/",
        blank=True,
        null=True,
        help_text="Optionales Dokument zum Download, z. B. Satzung oder Aufnahmeantrag als PDF.",
    )
    updated_at = models.DateTimeField("Zuletzt geändert", auto_now=True)

    class Meta:
        verbose_name = "Seite"
        verbose_name_plural = "Seiten"
        ordering = ["slug"]

    def __str__(self):
        return self.title


class ContactMessage(models.Model):
    name = models.CharField("Name", max_length=150)
    email = models.EmailField("E-Mail")
    subject = models.CharField("Betreff", max_length=200, blank=True)
    message = models.TextField("Nachricht")
    created_at = models.DateTimeField("Eingegangen am", auto_now_add=True)
    handled = models.BooleanField("Erledigt", default=False)

    class Meta:
        verbose_name = "Kontaktanfrage"
        verbose_name_plural = "Kontaktanfragen"
        ordering = ["-created_at"]

    def __str__(self):
        return f"{self.name} ({self.created_at:%d.%m.%Y})"


class MembershipApplication(models.Model):
    """Online-Einreichung des Aufnahmeantrags (zusätzlich zum PDF-Download)."""

    first_name = models.CharField("Vorname", max_length=100)
    last_name = models.CharField("Nachname", max_length=100)
    street = models.CharField("Straße, Hausnummer", max_length=200)
    postal_code = models.CharField("PLZ", max_length=10)
    city = models.CharField("Ort", max_length=100)
    email = models.EmailField("E-Mail")
    phone = models.CharField("Telefon", max_length=50, blank=True)
    birth_date = models.DateField("Geburtsdatum", blank=True, null=True)
    message = models.TextField("Anmerkungen", blank=True)
    created_at = models.DateTimeField("Eingegangen am", auto_now_add=True)
    handled = models.BooleanField("Erledigt", default=False)

    class Meta:
        verbose_name = "Aufnahmeantrag"
        verbose_name_plural = "Aufnahmeanträge"
        ordering = ["-created_at"]

    def __str__(self):
        return f"{self.first_name} {self.last_name}"
