@extends('layouts.app')
@section('titulo', 'Usuarios')
@section('contenido')
@include('partials.cabecera', ['h' => 'Usuarios', 'p' => 'Quién puede entrar al sistema y qué puede hacer', 'chip' => $usuarios->count() . ' usuarios'])

<section class="card">
  <div class="card-h"><span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span><div><h2>Nuevo usuario</h2><small>Administrador: todo. Operador: registra y edita, pero no borra ni administra usuarios.</small></div></div>
  <form method="POST" action="{{ route('usuarios.store') }}">
    @csrf
    <div class="form">
      <label>Nombre<input class="in" name="name" value="{{ old('name') }}" required></label>
      <label>Correo<input class="in" type="email" name="email" value="{{ old('email') }}" autocomplete="off" required></label>
      <label>Rol<select class="in" name="rol">@foreach ($roles as $k => $l)<option value="{{ $k }}" @selected(old('rol', 'operador') === $k)>{{ $l }}</option>@endforeach</select></label>
      <label>Contraseña (mín. 8)<input class="in" type="password" name="password" autocomplete="new-password" required></label>
      <button class="btn alt">Crear usuario</button>
    </div>
  </form>
</section>

<section class="card">
  <div class="card-h"><div><h2>Usuarios registrados</h2></div></div>
  <div class="list">
    @foreach ($usuarios as $x)
      <div class="row">
        <span style="display:flex;gap:12px;align-items:center"><span class="av" style="background:var(--butter)">{{ mb_strtoupper(mb_substr($x->name, 0, 1)) }}</span><span><strong>{{ $x->name }}</strong><small>{{ $x->email }}</small></span></span>
        <span class="tag {{ $x->esAdmin() ? '' : 'y' }}">{{ $x->rol_etiqueta }}</span>
        <span class="acts">
          <button type="button" class="linkx ed" data-edit="{{ json_encode(['action' => route('usuarios.update', $x), 'titulo' => 'Editar usuario', 'vals' => ['name' => $x->name, 'email' => $x->email, 'rol' => $x->rol, 'password' => '']], JSON_UNESCAPED_UNICODE) }}">Editar</button>
          @if ($x->id !== auth()->id())
            <form method="POST" action="{{ route('usuarios.destroy', $x) }}" data-confirmar="¿Eliminar a {{ $x->name }}?" data-detalle="Perderá el acceso al sistema. Esta acción no se puede deshacer.">@csrf @method('DELETE')<button class="linkx">Quitar</button></form>
          @endif
        </span>
      </div>
    @endforeach
  </div>
</section>

@include('partials.editar', ['campos' => [['name', 'Nombre', 'text'], ['email', 'Correo', 'email'], ['rol', 'Rol', 'select', array_keys($roles)], ['password', 'Nueva contraseña (opcional)', 'password', null, 'opcional wide']]])
@endsection
