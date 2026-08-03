from django.contrib import admin

from .models import ContactMessage, MembershipApplication, SitePage


@admin.register(SitePage)
class SitePageAdmin(admin.ModelAdmin):
    list_display = ("title", "slug", "updated_at")
    prepopulated_fields = {"slug": ("title",)}
    search_fields = ("title", "slug", "content")


@admin.register(ContactMessage)
class ContactMessageAdmin(admin.ModelAdmin):
    list_display = ("name", "email", "subject", "created_at", "handled")
    list_filter = ("handled", "created_at")
    search_fields = ("name", "email", "subject", "message")
    readonly_fields = ("name", "email", "subject", "message", "created_at")


@admin.register(MembershipApplication)
class MembershipApplicationAdmin(admin.ModelAdmin):
    list_display = ("last_name", "first_name", "city", "email", "created_at", "handled")
    list_filter = ("handled", "created_at")
    search_fields = ("first_name", "last_name", "email", "city")
