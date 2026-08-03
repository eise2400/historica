from django.contrib.auth import login
from django.contrib.auth.decorators import login_required
from django.shortcuts import redirect, render

from .forms import SignUpForm


def signup(request):
    if request.user.is_authenticated:
        return redirect("archive:photo_list")

    if request.method == "POST":
        form = SignUpForm(request.POST)
        if form.is_valid():
            user = form.save()
            login(request, user)
            return redirect("archive:photo_list")
    else:
        form = SignUpForm()
    return render(request, "accounts/signup.html", {"form": form})


@login_required
def profile(request):
    from archive.models import PhotoPersonTag

    suggestions = (
        PhotoPersonTag.objects.filter(suggested_by=request.user)
        .select_related("photo", "person")
        .order_by("-created_at")
    )
    return render(request, "accounts/profile.html", {"suggestions": suggestions})
