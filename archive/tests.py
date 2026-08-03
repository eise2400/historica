from django.contrib.auth.models import User
from django.core.files.uploadedfile import SimpleUploadedFile
from django.test import TestCase
from django.urls import reverse

from .models import Category, Person, Photo, PhotoPersonTag

TINY_GIF = (
    b"GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xff\xff\xff!\xf9\x04"
    b"\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;"
)


def make_photo(**kwargs):
    category = kwargs.pop("category", None)
    if category is None:
        category, _ = Category.objects.get_or_create(name="Ortsansichten")
    image = SimpleUploadedFile("test.gif", TINY_GIF, content_type="image/gif")
    defaults = {"title": "Testfoto", "image": image, "category": category}
    defaults.update(kwargs)
    return Photo.objects.create(**defaults)


class PhotoModelTests(TestCase):
    def test_slug_is_generated_and_unique(self):
        photo1 = make_photo(title="Marktplatz Teugn")
        photo2 = make_photo(title="Marktplatz Teugn")
        self.assertEqual(photo1.slug, "marktplatz-teugn")
        self.assertNotEqual(photo1.slug, photo2.slug)

    def test_date_display_prefers_free_text(self):
        photo = make_photo(date_text="um 1930")
        self.assertEqual(photo.date_display, "um 1930")


class PhotoListViewTests(TestCase):
    def test_only_published_photos_are_shown(self):
        make_photo(title="Sichtbar", is_published=True)
        make_photo(title="Versteckt", is_published=False)
        response = self.client.get(reverse("archive:photo_list"))
        self.assertContains(response, "Sichtbar")
        self.assertNotContains(response, "Versteckt")

    def test_category_filter(self):
        cat_a = Category.objects.create(name="Vereine")
        cat_b = Category.objects.create(name="Landwirtschaft")
        make_photo(title="Vereinsfoto", category=cat_a)
        make_photo(title="Erntefoto", category=cat_b)
        response = self.client.get(reverse("archive:photo_list"), {"kategorie": cat_a.slug})
        self.assertContains(response, "Vereinsfoto")
        self.assertNotContains(response, "Erntefoto")


class PhotoDetailAndTaggingTests(TestCase):
    def setUp(self):
        self.photo = make_photo(title="Gruppenfoto Feuerwehr")
        self.person = Person.objects.create(first_name="Josef", last_name="Huber")
        PhotoPersonTag.objects.create(
            photo=self.photo, person=self.person, x_percent=42, y_percent=30,
            status=PhotoPersonTag.Status.APPROVED,
        )

    def test_approved_tag_is_shown(self):
        response = self.client.get(self.photo.get_absolute_url())
        self.assertContains(response, "Josef Huber")

    def test_anonymous_user_cannot_suggest_tag(self):
        response = self.client.get(self.photo.get_absolute_url())
        self.assertContains(response, "Melden Sie sich an")

    def test_logged_in_user_can_suggest_tag(self):
        User.objects.create_user(username="leser", password="testpass123")
        self.client.login(username="leser", password="testpass123")
        response = self.client.post(
            self.photo.get_absolute_url(),
            {"new_first_name": "Anna", "new_last_name": "Maier", "note": "vorne links"},
        )
        self.assertRedirects(response, self.photo.get_absolute_url())
        tag = PhotoPersonTag.objects.get(person__last_name="Maier")
        self.assertEqual(tag.status, PhotoPersonTag.Status.PENDING)
        self.assertEqual(tag.suggested_by.username, "leser")

    def test_unpublished_photo_returns_404(self):
        hidden = make_photo(title="Versteckt", is_published=False)
        response = self.client.get(hidden.get_absolute_url())
        self.assertEqual(response.status_code, 404)
