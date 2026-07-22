<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Permite al cliente mantener sus datos y consultar su historial de pedidos. */
class ProfileController extends Controller
{
    /** Carga el historial paginado junto con líneas y pagos para evitar consultas repetidas. */
    public function show(Request $request)
    {
        $orders = $request->user()->orders()->with('items', 'payment')->latest('purchased_at')->paginate(8);

        return view('profile.show', compact('orders'));
    }

    /** Valida y actualiza únicamente los campos editables del perfil. */
    public function update(Request $request)
    {
        $user = $request->user();
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(trim((string) $request->input('email'))),
            'phone' => $request->filled('phone') ? trim((string) $request->input('phone')) : null,
            'address' => $request->filled('address') ? trim((string) $request->input('address')) : null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+()\-\s]{8,30}$/'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
        $user->update($data);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}
