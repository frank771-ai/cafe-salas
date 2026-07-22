<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/** Implementa registro, inicio y cierre de sesión con las protecciones de Laravel. */
class AuthController extends Controller
{
    /** Muestra el formulario de registro para visitantes. */
    public function registerForm()
    {
        return view('auth.register');
    }

    /** Valida, crea y autentica al nuevo usuario en una sola operación. */
    public function register(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(trim((string) $request->input('email'))),
            'phone' => $request->filled('phone') ? trim((string) $request->input('phone')) : null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+()\-\s]{8,30}$/'],
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $user = User::create($data);
        Auth::login($user);
        // Regenerar el identificador evita fijación de sesión después de autenticarse.
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', '¡Cuenta creada! Ya puede comenzar a comprar.');
    }

    /** Muestra el formulario de inicio de sesión. */
    public function loginForm()
    {
        return view('auth.login');
    }

    /** Comprueba credenciales y conserva la URL originalmente solicitada. */
    public function login(Request $request)
    {
        // Los correos se comparan con una representación canónica y predecible.
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $credentials = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Las credenciales no son válidas.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', '¡Bienvenido de nuevo!');
    }

    /** Destruye la sesión actual y renueva el token CSRF. */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'La sesión se cerró correctamente.');
    }
}
