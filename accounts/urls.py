from django.contrib.auth import views as auth_views
from django.urls import path

from . import views
from .forms import LoginForm

app_name = "accounts"

urlpatterns = [
    path("registrieren/", views.signup, name="signup"),
    path("profil/", views.profile, name="profile"),
    path(
        "anmelden/",
        auth_views.LoginView.as_view(template_name="accounts/login.html", authentication_form=LoginForm),
        name="login",
    ),
    path("abmelden/", auth_views.LogoutView.as_view(), name="logout"),
]
