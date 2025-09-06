<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'IMS') }}</title>
    <meta name="theme-color" content="#4B5563">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('ims/ims.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="ims">
    <a href="#main" class="skip-link">Aller au contenu</a>
    <div class="ims-app">
        <header class="ims-header" role="banner">
            <button class="btn btn-outline-secondary d-lg-none" aria-label="Ouvrir le menu" data-drawer-toggle>
                ☰
            </button>
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}"
   class="brand d-flex align-items-center text-decoration-none" aria-label="Accueil IMS">
  {{-- Logo SVG réutilisable --}}
  @include('icons.ims')   {{-- fichier créé à l’étape 3 --}}
  <span class="brand-name ms-2">IMS</span>
</a>

            <div class="ms-auto d-flex align-items-center gap-2">
                {{-- Placeholder for future header actions --}}
            </div>
        </header>

        <aside class="ims-sidebar" aria-label="Navigation latérale">
            <nav>
                <ul class="nav">
                    <li class="nav-item">
                        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}"
                            class="nav-link {{ request()->routeIs('dashboard') || request()->is('/') ? 'active' : '' }}">
                            Accueil
                        </a>
                    </li>

                    <li><a href="{{ route('products.index') }}"
                            class="{{ request()->routeIs('products.*') ? 'active' : '' }}">Produits</a></li>
                    <li><a href="{{ route('inventories.index') }}"
                            class="{{ request()->routeIs('inventories.*') ? 'active' : '' }}">Inventaires</a></li>
                    <li><a href="{{ route('movements.index') }}"
                            class="{{ request()->routeIs('movements.*') ? 'active' : '' }}">Mouvements</a></li>
                    <li><a href="{{ route('alerts.index') }}"
                            class="{{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                            Alertes</a></li>

                    <li><a href="{{ route('reports.index') }}"
                            class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Graphiques</a></li>
                </ul>
            </nav>
        </aside>
        <div class="ims-overlay" aria-hidden="true"></div>

        <main id="main" class="ims-main">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (isset($slot) && trim($slot) !== '')
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </div>
        </main>
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('ims/ims.js') }}" defer></script>
</body>

</html>
