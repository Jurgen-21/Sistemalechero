<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    public function edit()
    {
        return view('perfil.edit');
    }

    public function update(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|min:3|max:80',
            'password_actual' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password_actual.required_with' => 'Escribe tu contraseña actual para cambiarla.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
            'password.min' => 'La contraseña nueva debe tener al menos 8 caracteres.',
        ]);
        $r->user()->forceFill(array_filter(['name' => $d['name'], 'password' => $d['password'] ?? null]))->save();
        return back()->with('ok', 'Perfil actualizado');
    }
}
