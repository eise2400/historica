from django.urls import path

from . import views

app_name = "core"

urlpatterns = [
    path("", views.home, name="home"),
    path("verein/impressum/", views.site_page, {"slug": "impressum"}, name="impressum"),
    path("verein/datenschutz/", views.site_page, {"slug": "datenschutz"}, name="datenschutz"),
    path("verein/satzung/", views.site_page, {"slug": "satzung"}, name="satzung"),
    path("verein/aufnahmeantrag/", views.aufnahmeantrag, name="aufnahmeantrag"),
    path("kontakt/", views.kontakt, name="kontakt"),
]
