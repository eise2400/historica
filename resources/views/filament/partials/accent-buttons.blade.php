{{-- Filament admin pages don't load resources/css/app.css (only the public
site layouts do), so the custom `accent` Tailwind color from
tailwind.config.js never gets compiled into the panel's own stylesheet.
`bg-accent`/`text-accent`/`border-accent` classes therefore silently
resolve to nothing there - invisible in light mode. These plain CSS
classes are a pipeline-independent substitute for admin pages that need
the accent color. --}}
<style>
    .btn-accent {
        background-color: #a8652b;
        color: #fff;
    }
    .btn-accent:hover {
        background-color: #8a501f;
    }
    .btn-accent-outline {
        background-color: transparent;
        border-color: #a8652b;
        color: #a8652b;
    }
    .btn-accent-outline:hover,
    .btn-accent-outline.is-armed {
        background-color: #a8652b;
        color: #fff;
    }
</style>
