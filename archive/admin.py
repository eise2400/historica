from django.contrib import admin
from django.utils.html import format_html

from .models import Category, Location, Person, Photo, PhotoPersonTag


@admin.register(Category)
class CategoryAdmin(admin.ModelAdmin):
    list_display = ("name", "order", "photo_count")
    prepopulated_fields = {"slug": ("name",)}
    search_fields = ("name", "description")

    def photo_count(self, obj):
        return obj.photos.count()

    photo_count.short_description = "Anzahl Fotos"


@admin.register(Location)
class LocationAdmin(admin.ModelAdmin):
    list_display = ("name", "latitude", "longitude", "photo_count")
    prepopulated_fields = {"slug": ("name",)}
    search_fields = ("name", "description")

    def photo_count(self, obj):
        return obj.photos.count()

    photo_count.short_description = "Anzahl Fotos"


@admin.register(Person)
class PersonAdmin(admin.ModelAdmin):
    list_display = ("last_name", "first_name", "maiden_name", "birth_year", "death_year", "photo_count")
    search_fields = ("first_name", "last_name", "maiden_name", "notes")
    list_filter = ("birth_year",)

    def photo_count(self, obj):
        return obj.photos.count()

    photo_count.short_description = "Anzahl Fotos"


class PhotoPersonTagInline(admin.TabularInline):
    model = PhotoPersonTag
    extra = 1
    autocomplete_fields = ["person"]
    fields = ("person", "x_percent", "y_percent", "note", "status", "suggested_by")
    readonly_fields = ("suggested_by",)
    classes = ["photo-tag-inline"]


@admin.register(Photo)
class PhotoAdmin(admin.ModelAdmin):
    list_display = (
        "thumbnail",
        "title",
        "category",
        "location",
        "date_display",
        "is_published",
        "tag_count",
        "uploaded_by",
        "created_at",
    )
    list_display_links = ("thumbnail", "title")
    list_filter = ("category", "location", "is_published", "created_at")
    search_fields = ("title", "description", "inventory_number")
    date_hierarchy = "created_at"
    prepopulated_fields = {"slug": ("title",)}
    autocomplete_fields = ["location"]
    readonly_fields = ("image_preview", "uploaded_by", "created_at", "updated_at")
    inlines = [PhotoPersonTagInline]
    fieldsets = (
        (None, {
            "fields": ("title", "slug", "image", "image_preview", "description"),
        }),
        ("Einordnung", {
            "fields": ("category", "location", "date_from", "date_to", "date_text"),
        }),
        ("Herkunft & Status", {
            "fields": ("source", "inventory_number", "is_published"),
        }),
        ("Verwaltung", {
            "fields": ("uploaded_by", "created_at", "updated_at"),
            "classes": ("collapse",),
        }),
    )

    class Media:
        css = {"all": ("archive/css/admin_tag.css",)}
        js = ("archive/js/tag_photo.js",)

    def thumbnail(self, obj):
        if obj.image:
            return format_html(
                '<img src="{}" style="height:40px;width:auto;border-radius:3px;" />', obj.image.url
            )
        return ""

    thumbnail.short_description = "Vorschau"

    def image_preview(self, obj):
        if not obj.image:
            return "Bitte zuerst speichern, um eine Vorschau mit Markierungsfunktion zu sehen."
        return format_html(
            '<div id="photo-preview-wrapper" style="position:relative;display:inline-block;max-width:600px;">'
            '<img id="photo-preview-img" src="{}" style="max-width:600px;width:100%;height:auto;display:block;" />'
            '<div id="photo-preview-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;"></div>'
            "</div>"
            '<p class="help">Personen unten hinzufügen, auf „Position setzen“ klicken und anschließend auf das Foto klicken, '
            "um die Position der Person zu markieren.</p>"
            , obj.image.url,
        )

    image_preview.short_description = "Bildvorschau & Markierung"

    def tag_count(self, obj):
        return obj.person_tags.count()

    tag_count.short_description = "Personen"

    def save_model(self, request, obj, form, change):
        if not obj.uploaded_by_id:
            obj.uploaded_by = request.user
        super().save_model(request, obj, form, change)

    def save_formset(self, request, form, formset, change):
        instances = formset.save(commit=False)
        for instance in instances:
            if isinstance(instance, PhotoPersonTag) and not instance.suggested_by_id:
                instance.suggested_by = request.user
            instance.save()
        formset.save_m2m()
