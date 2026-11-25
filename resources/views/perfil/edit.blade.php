@extends('layouts.app')
@section('titulo', 'Mi perfil')
@section('contenido')
@include('partials.cabecera', ['h' => 'Mi perfil', 'p' => auth()->user()->email . ' · ' . auth()->user()->rol_etiqueta])
<section class="card">
  <form method="POST" action="{{ route('perfil.update') }}">
    @csrf @method('PUT')
    <div class="form">
      <label class="wide" style="grid-column:1/-1">Nombre<input class="in" name="name" value="{{ old('name', auth()->user()->name) }}" required></label>
      <label>Contraseña actual<input class="in" type="password" name="password_actual" autocomplete="current-password"></label>
      <label>Nueva contraseña<input class="in" type="password" name="password" autocomplete="new-password"></label>
      <label>Repetir nueva contraseña<input class="in" type="password" name="password_confirmation" autocomplete="new-password"></label>
    </div>
    <p style="color:var(--mut);font-size:14px">Deja las contraseñas vacías si solo quieres cambiar el nombre.</p>
    <button class="btn alt">Guardar cambios</button>
  </form>
</section>
@endsection
