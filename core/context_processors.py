def site_settings(request):
    """Global template context, e.g. for the navigation and footer."""
    return {
        "SITE_NAME": "Historica Deing",
        "SITE_CLAIM": "Geschichts- und Heimatverein für Teugn",
    }
