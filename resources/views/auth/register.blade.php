@extends('layouts.app')
@section('title', 'Crear cuenta')

@section('content')
    <div class="auth-shell">
        <div class="auth-card auth-card-wide">
            <span class="eyebrow">Su café, a un clic</span>
            <h1 class="h2">Crear una cuenta</h1>
            <p class="text-secondary">Guarde sus datos y consulte el historial de pedidos.</p>

            {{-- Laravel repite todas estas validaciones en el servidor; HTML aporta respuesta inmediata. --}}
            <form method="POST" action="{{ route('register.store') }}" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nombre completo</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name') }}" minlength="3" maxlength="120" autocomplete="name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Teléfono <span class="text-secondary">(opcional)</span></label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="email">Correo electrónico</label>
                        <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Contraseña</label>
                        <input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
                        <div class="form-text">Mínimo 8 caracteres, mayúscula, minúscula y número.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                        <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                    </div>
                </div>
                <button class="btn btn-primary btn-lg w-100 mt-4" type="submit">Crear mi cuenta</button>
            </form>

            <p class="text-center mt-4 mb-0">Ya tengo cuenta: <a href="{{ route('login') }}">iniciar sesión</a></p>
        </div>
    </div>
@endsection
