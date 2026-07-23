<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Origen Tico: café de especialidad y productos artesanales de Costa Rica.">
    <title>@yield('title', 'Inicio') | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/premium.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    @php($cartCount = app(\App\Services\CartService::class)->count())
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <div class="announcement">Envío gratis desde ₡20.000 <span aria-hidden="true">·</span> Precios justos en colones <span aria-hidden="true">·</span> Hecho en Costa Rica</div>

    {{-- Navegación compartida; adapta opciones según autenticación y rol. --}}
    <nav class="navbar navbar-expand-lg navbar-light sticky-top" aria-label="Navegación principal">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true">OT</span>
                <span class="brand-copy"><strong>Origen Tico</strong><small>Café y artesanía costarricense</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Inicio</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}" @if(request()->routeIs('products.*')) aria-current="page" @endif>Catálogo</a></li>
                    @auth
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>Mi perfil</a></li>
                        @if(auth()->user()->is_admin)
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.*')) aria-current="page" @endif>Administración</a></li>
                        @endif
                    @endauth
                </ul>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a class="btn btn-cart" href="{{ route('cart.index') }}" aria-label="Carrito con {{ $cartCount }} {{ $cartCount === 1 ? 'artículo' : 'artículos' }}">
                        Carrito <span class="badge rounded-pill">{{ $cartCount }}</span>
                    </a>
                    @guest
                        <a class="btn nav-account" href="{{ route('login') }}">Ingresar</a>
                        <a class="btn btn-brand" href="{{ route('register') }}">Crear cuenta</a>
                    @else
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn nav-account" type="submit">Salir</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <main id="contenido">
        {{-- Mensajes flash y errores de validación de la solicitud anterior. --}}
        @if (session('success'))
            <div class="container mt-4">
                <div class="alert alert-success alert-dismissible fade show" role="status" aria-live="polite">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="container mt-4">
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="container mt-4">
                <div class="alert alert-danger" role="alert">
                    <strong>Revise la información:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="footer-brand"><span class="brand-mark" aria-hidden="true">OT</span><h2>Origen Tico</h2></div>
                    <p class="footer-intro">Calidad con raíz costarricense: café, detalles artesanales y experiencias elegidas para disfrutar sin complicaciones.</p>
                </div>
                <div class="col-6 col-lg-3">
                    <h2 class="footer-title">Explorar</h2>
                    <nav class="footer-links" aria-label="Enlaces de compra">
                        <a href="{{ route('products.index') }}">Catálogo</a>
                        <a href="{{ route('cart.index') }}">Carrito</a>
                        @auth<a href="{{ route('profile.show') }}">Mi perfil</a>@endauth
                    </nav>
                </div>
                <div class="col-6 col-lg-4">
                    <h2 class="footer-title">Nuestra promesa</h2>
                    <p class="small mb-0">Precio transparente, sesiones protegidas y datos de tarjeta que nunca se almacenan.</p>
                </div>
            </div>
            <div class="footer-bottom"><span>© {{ date('Y') }} Origen Tico</span><span>Demostración educativa UTN · Costa Rica</span></div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
