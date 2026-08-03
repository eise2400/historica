from django.contrib.auth.models import User
from django.test import TestCase
from django.urls import reverse


class SignUpTests(TestCase):
    def test_signup_creates_user_and_logs_in(self):
        response = self.client.post(
            reverse("accounts:signup"),
            {
                "username": "neuesmitglied",
                "email": "neu@example.com",
                "first_name": "Lisa",
                "last_name": "Schmid",
                "password1": "einSicheresPasswort123",
                "password2": "einSicheresPasswort123",
            },
        )
        self.assertRedirects(response, reverse("archive:photo_list"))
        self.assertTrue(User.objects.filter(username="neuesmitglied").exists())

    def test_profile_requires_login(self):
        response = self.client.get(reverse("accounts:profile"))
        self.assertEqual(response.status_code, 302)
        self.assertIn(reverse("accounts:login"), response.url)
