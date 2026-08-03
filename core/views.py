from django.conf import settings
from django.contrib import messages
from django.core.mail import send_mail
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse

from .forms import ContactForm, MembershipApplicationForm
from .models import SitePage


def home(request):
    from archive.models import Photo

    latest_photos = (
        Photo.objects.filter(is_published=True)
        .select_related("category")
        .order_by("-created_at")[:8]
    )
    return render(
        request,
        "core/home.html",
        {"latest_photos": latest_photos},
    )


def site_page(request, slug, template_name=None):
    """Generic renderer for editable static pages (Impressum, Satzung, ...)."""
    page = get_object_or_404(SitePage, slug=slug)
    return render(
        request,
        template_name or "core/page.html",
        {"page": page},
    )


def kontakt(request):
    if request.method == "POST":
        form = ContactForm(request.POST)
        if form.is_valid():
            contact_message = form.save()
            send_mail(
                subject=f"Kontaktanfrage: {contact_message.subject or 'Ohne Betreff'}",
                message=(
                    f"Von: {contact_message.name} <{contact_message.email}>\n\n"
                    f"{contact_message.message}"
                ),
                from_email=settings.DEFAULT_FROM_EMAIL,
                recipient_list=[settings.CONTACT_RECIPIENT_EMAIL],
                fail_silently=True,
            )
            messages.success(
                request,
                "Vielen Dank für Ihre Nachricht! Wir melden uns so bald wie möglich bei Ihnen.",
            )
            return redirect(reverse("core:kontakt"))
    else:
        form = ContactForm()
    return render(request, "core/kontakt.html", {"form": form})


def aufnahmeantrag(request):
    page = SitePage.objects.filter(slug="aufnahmeantrag").first()
    if request.method == "POST":
        form = MembershipApplicationForm(request.POST)
        if form.is_valid():
            form.save()
            messages.success(
                request,
                "Vielen Dank für Ihren Aufnahmeantrag! Wir setzen uns in Kürze mit Ihnen in Verbindung.",
            )
            return redirect(reverse("core:aufnahmeantrag"))
    else:
        form = MembershipApplicationForm()
    return render(request, "core/aufnahmeantrag.html", {"form": form, "page": page})
