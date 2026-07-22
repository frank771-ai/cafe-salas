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
    @stack('styles')
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <div class="announcement">Envío gratis en compras superiores a ₡30.000 · Pago de demostración protegido</div>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top" aria-label="Navegación principal">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true">OT</span>
                <span>Origen <strong>Tico</strong></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Catálogo</a></li>
                    @auth
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.show') }}">Mi perfil</a></li>
                        @if(auth()->user()->is_admin)
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Administración</a></li>
                        @endif
                    @endauth
                </ul>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a class="btn btn-cart" href="{{ route('cart.index') }}" aria-label="Carrito con {{ app(\App\Services\CartService::class)->count() }} artículos">
                        Carrito <span class="badge rounded-pill">{{ app(\App\Services\CartService::class)->count() }}</span>
                    </a>
                    @guest
                        <a class="btn btn-outline-light" href="{{ route('login') }}">Ingresar</a>
                        <a class="btn btn-cream" href="{{ route('register') }}">Crear cuenta</a>
                    @else
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-light" type="submit">Salir</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <main id="contenido">
        @if(session('success'))
            <div class="container mt-4"><div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div></div>
        @endif
        @if(session('error'))
            <div class="container mt-4"><div class="alert alert-danger" role="alert">{{ session('error') }}</div></div>
        @endif
        @if($errors->any())
            <div class="container mt-4">
                <div class="alert alert-danger" role="alert">
                    <strong>Revise la información:</strong>
                    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer mt-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-md-5"><h2 class="h4">Origen Tico</h2><p class="mb-0">Proyecto académico de comercio electrónico inspirado en productores y caficultores costarricenses.</p></div>
                <div class="col-md-3"><h2 class="h6 text-uppercase">Comprar</h2><a href="{{ route('products.index') }}">Catálogo</a><br><a href="{{ route('cart.index') }}">Carrito</a></div>
                <div class="col-md-4"><h2 class="h6 text-uppercase">Compra segura</h2><p class="small mb-0">Contraseñas cifradas, sesiones protegidas y datos de tarjeta no almacenados.</p></div>
            </div>
            <hr><p class="small mb-0">© {{ date('Y') }} Origen Tico · Demostración educativa UTN</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
