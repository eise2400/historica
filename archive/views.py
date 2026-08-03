from django.contrib import messages
from django.core.paginator import Paginator
from django.shortcuts import get_object_or_404, redirect, render

from .forms import PersonTagSuggestionForm
from .models import Category, Location, Person, Photo


def photo_list(request):
    photos = Photo.objects.filter(is_published=True).select_related("category", "location")

    category_slug = request.GET.get("kategorie", "")
    location_slug = request.GET.get("ort", "")
    year = request.GET.get("jahr", "")
    query = request.GET.get("q", "")

    if category_slug:
        photos = photos.filter(category__slug=category_slug)
    if location_slug:
        photos = photos.filter(location__slug=location_slug)
    if year:
        photos = photos.filter(date_from__year=year) | photos.filter(date_to__year=year)
    if query:
        photos = (photos.filter(title__icontains=query)
                  | photos.filter(description__icontains=query)
                  | photos.filter(persons__last_name__icontains=query)
                  | photos.filter(persons__first_name__icontains=query))
    photos = photos.distinct()

    paginator = Paginator(photos, 24)
    page_obj = paginator.get_page(request.GET.get("seite"))

    context = {
        "page_obj": page_obj,
        "categories": Category.objects.all(),
        "locations": Location.objects.filter(photos__is_published=True).distinct(),
        "selected_category": category_slug,
        "selected_location": location_slug,
        "selected_year": year,
        "query": query,
    }
    return render(request, "archive/photo_list.html", context)


def photo_detail(request, slug):
    photo = get_object_or_404(
        Photo.objects.select_related("category", "location"), slug=slug, is_published=True
    )
    tags = photo.person_tags.filter(status="approved").select_related("person")

    suggestion_form = None
    if request.user.is_authenticated:
        if request.method == "POST":
            suggestion_form = PersonTagSuggestionForm(request.POST)
            if suggestion_form.is_valid():
                suggestion_form.save(photo=photo, user=request.user)
                messages.success(
                    request,
                    "Vielen Dank! Ihr Vorschlag wird nach Prüfung durch den Webmaster freigeschaltet.",
                )
                return redirect(photo.get_absolute_url())
        else:
            suggestion_form = PersonTagSuggestionForm()

    return render(
        request,
        "archive/photo_detail.html",
        {"photo": photo, "tags": tags, "suggestion_form": suggestion_form},
    )


def person_detail(request, pk):
    person = get_object_or_404(Person, pk=pk)
    photos = Photo.objects.filter(
        persons=person, is_published=True, person_tags__status="approved"
    ).distinct()
    return render(request, "archive/person_detail.html", {"person": person, "photos": photos})
