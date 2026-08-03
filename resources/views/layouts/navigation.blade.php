<nav x-data="{ open: false }" class="bg-brand-dark text-brand-light sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="text-lg font-semibold text-white tracking-wide">
                        Historica Deing
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    <a href="{{ route('home') }}" class="text-sm font-medium hover:text-white {{ request()->routeIs('home') ? 'text-white' : 'text-brand-light/80' }}">Startseite</a>
                    <a href="{{ route('archive.index') }}" class="text-sm font-medium hover:text-white {{ request()->routeIs('archive.*') ? 'text-white' : 'text-brand-light/80' }}">Fotoarchiv</a>

                    <div class="relative" x-data="{ vereinOpen: false }" @click.outside="vereinOpen = false">
                        <button @click="vereinOpen = ! vereinOpen" class="text-sm font-medium text-brand-light/80 hover:text-white flex items-center gap-1">
                            Verein
                            <svg class="h-3 w-3 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                        <div x-show="vereinOpen" x-cloak class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 text-gray-800">
                            <a href="{{ route('satzung') }}" class="block px-4 py-2 text-sm hover:bg-brand-light">Satzung</a>
                            <a href="{{ route('aufnahmeantrag') }}" class="block px-4 py-2 text-sm hover:bg-brand-light">Aufnahmeantrag</a>
                            <a href="{{ route('impressum') }}" class="block px-4 py-2 text-sm hover:bg-brand-light">Impressum</a>
                            <a href="{{ route('datenschutz') }}" class="block px-4 py-2 text-sm hover:bg-brand-light">Datenschutz</a>
                        </div>
                    </div>

                    <a href="{{ route('kontakt') }}" class="text-sm font-medium hover:text-white {{ request()->routeIs('kontakt') ? 'text-white' : 'text-brand-light/80' }}">Kontakt</a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="text-sm font-medium text-brand-light/80 hover:text-white">Verwaltung</a>
                    @endif
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center text-sm font-medium text-brand-light/80 hover:text-white">
                                {{ auth()->user()->name }}
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('suggestions.index')">Meine Vorschläge</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Abmelden
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-brand-light/80 hover:text-white">Anmelden</a>
                    <a href="{{ route('register') }}" class="text-sm font-medium text-brand-light/80 hover:text-white">Registrieren</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-brand-light hover:text-white focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-brand-dark border-t border-white/10">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Startseite</a>
            <a href="{{ route('archive.index') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Fotoarchiv</a>
            <a href="{{ route('satzung') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Satzung</a>
            <a href="{{ route('aufnahmeantrag') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Aufnahmeantrag</a>
            <a href="{{ route('impressum') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Impressum</a>
            <a href="{{ route('datenschutz') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Datenschutz</a>
            <a href="{{ route('kontakt') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Kontakt</a>
        </div>
        <div class="pt-4 pb-3 border-t border-white/10">
            @auth
                <div class="px-4 text-sm text-white">{{ auth()->user()->name }}</div>
                <div class="mt-2 space-y-1">
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Verwaltung</a>
                    @endif
                    <a href="{{ route('suggestions.index') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Meine Vorschläge</a>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Abmelden</a>
                    </form>
                </div>
            @else
                <div class="space-y-1">
                    <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Anmelden</a>
                    <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-brand-light hover:text-white">Registrieren</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
