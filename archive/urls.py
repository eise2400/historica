from django.urls import path

from . import views

app_name = "archive"

urlpatterns = [
    path("", views.photo_list, name="photo_list"),
    path("person/<int:pk>/", views.person_detail, name="person_detail"),
    path("<slug:slug>/", views.photo_detail, name="photo_detail"),
]
