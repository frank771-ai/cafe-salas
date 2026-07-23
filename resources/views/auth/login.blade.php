@extends('layouts.app')
@section('title', 'Iniciar sesión')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <span class="eyebrow">Bienvenido de nuevo</span>
            <h1 class="h2">Iniciar sesión</h1>
            <p class="text-secondary">Acceda a su perfil, pedidos y facturas.</p>

            {{-- El token CSRF y el límite de intentos de la ruta protegen el inicio de sesión. --}}
            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                    <label class="form-check-label" for="remember">Mantener mi sesión</label>
                </div>
                <button class="btn btn-primary btn-lg w-100" type="submit">Ingresar</button>
            </form>

            <p class="text-center mt-4 mb-0">¿No tiene una cuenta? <a href="{{ route('register') }}">Regístrese</a></p>
        </div>
    </div>
@endsection
