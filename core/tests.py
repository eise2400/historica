from django.test import TestCase
from django.urls import reverse

from .models import ContactMessage, MembershipApplication, SitePage


class SitePageTests(TestCase):
    def test_page_renders(self):
        SitePage.objects.create(slug="impressum", title="Impressum", content="<p>Test</p>")
        response = self.client.get(reverse("core:impressum"))
        self.assertContains(response, "Test")

    def test_missing_page_returns_404(self):
        response = self.client.get(reverse("core:satzung"))
        self.assertEqual(response.status_code, 404)


class ContactFormTests(TestCase):
    def test_submitting_contact_form_creates_message(self):
        response = self.client.post(
            reverse("core:kontakt"),
            {"name": "Maria Bauer", "email": "maria@example.com", "subject": "Frage", "message": "Hallo!"},
        )
        self.assertRedirects(response, reverse("core:kontakt"))
        self.assertEqual(ContactMessage.objects.count(), 1)


class MembershipApplicationTests(TestCase):
    def test_submitting_application_creates_record(self):
        response = self.client.post(
            reverse("core:aufnahmeantrag"),
            {
                "first_name": "Karl",
                "last_name": "Wagner",
                "street": "Dorfstraße 1",
                "postal_code": "93356",
                "city": "Teugn",
                "email": "karl@example.com",
            },
        )
        self.assertRedirects(response, reverse("core:aufnahmeantrag"))
        self.assertEqual(MembershipApplication.objects.count(), 1)
