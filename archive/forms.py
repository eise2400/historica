from django import forms

from .models import Person, PhotoPersonTag


class PersonTagSuggestionForm(forms.Form):
    """Formular für registrierte Nutzer, um eine Person auf einem Foto zu benennen.

    Der Vorschlag wird als 'ausstehend' gespeichert und muss vom Webmaster
    im Admin freigegeben werden.
    """

    person = forms.ModelChoiceField(
        label="Bereits erfasste Person",
        queryset=Person.objects.all(),
        required=False,
        widget=forms.Select(attrs={"class": "form-select"}),
        help_text="Falls die Person schon im Archiv erfasst ist, hier auswählen.",
    )
    new_first_name = forms.CharField(
        label="Vorname (neue Person)",
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={"class": "form-control"}),
    )
    new_last_name = forms.CharField(
        label="Nachname (neue Person)",
        max_length=100,
        required=False,
        widget=forms.TextInput(attrs={"class": "form-control"}),
    )
    note = forms.CharField(
        label="Anmerkung zur Position",
        max_length=200,
        required=False,
        help_text="z. B. 'hintere Reihe, 2. von links'.",
        widget=forms.TextInput(attrs={"class": "form-control"}),
    )

    def clean(self):
        cleaned = super().clean()
        person = cleaned.get("person")
        last_name = cleaned.get("new_last_name")
        if not person and not last_name:
            raise forms.ValidationError(
                "Bitte eine bestehende Person auswählen oder mindestens einen Nachnamen angeben."
            )
        return cleaned

    def save(self, photo, user):
        person = self.cleaned_data.get("person")
        if not person:
            person = Person.objects.create(
                first_name=self.cleaned_data.get("new_first_name", ""),
                last_name=self.cleaned_data["new_last_name"],
            )
        tag, _ = PhotoPersonTag.objects.get_or_create(
            photo=photo,
            person=person,
            defaults={
                "note": self.cleaned_data.get("note", ""),
                "status": PhotoPersonTag.Status.PENDING,
                "suggested_by": user,
            },
        )
        return tag
