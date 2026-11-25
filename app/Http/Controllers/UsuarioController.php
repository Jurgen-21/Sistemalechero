<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('usuarios.index', ['usuarios' => User::orderBy('name')->get(), 'roles' => User::ROLES]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|min:3|max:80',
            'email' => 'required|email|max:120|unique:users,email',
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', Password::min(8)],
        ], [
            'name.required' => 'Escribe el nombre del usuario.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);
        User::create($d);
        return back()->with('ok', "Usuario «{$d['name']}» creado");
    }

    public function update(Request $r, User $usuario)
    {
        $d = $r->validate([
            'name' => 'required|string|min:3|max:80',
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($usuario->id)],
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', Password::min(8)],
        ]);
        if ($d['rol'] !== 'admin' && $usuario->esAdmin() && User::where('rol', 'admin')->count() <= 1) {
            return back()->withErrors('Debe quedar al menos un administrador.');
        }
        if (blank($d['password'] ?? null)) {
            unset($d['password']);
        }
        $usuario->update($d);
        return back()->with('ok', 'Usuario actualizado');
    }

    public function destroy(Request $r, User $usuario)
    {
        if ($usuario->id === $r->user()->id) {
            return back()->withErrors('No puedes eliminar tu propio usuario.');
        }
        if ($usuario->esAdmin() && User::where('rol', 'admin')->count() <= 1) {
            return back()->withErrors('Debe quedar al menos un administrador.');
        }
        $usuario->delete();
        return back()->with('ok', 'Usuario eliminado');
    }
}
