from django.conf import settings
from django.conf.urls.static import static
from django.contrib import admin
from django.urls import include, path

admin.site.site_header = "Historica Deing e.V. – Verwaltung"
admin.site.site_title = "Historica Deing Verwaltung"
admin.site.index_title = "Fotoarchiv & Vereinsverwaltung"

urlpatterns = [
    path("admin/", admin.site.urls),
    path("konto/", include("accounts.urls")),
    path("archiv/", include("archive.urls")),
    path("", include("core.urls")),
]

if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
