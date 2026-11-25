@extends('layouts.app')
@section('titulo', 'Respaldo')
@section('contenido')
@include('partials.cabecera', ['h' => 'Respaldo', 'p' => 'Copia de seguridad de todos tus datos'])
<section class="card">
  <div class="card-h"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg></span><div><h2>Descargar respaldo (.sql)</h2><small>Incluye proveedores, entregas, pagos, ventas, gastos, distribuciones, producción y usuarios</small></div></div>
  <div class="list">
    @foreach ($tablas as $t => $n)<div class="row" style="grid-template-columns:minmax(0,1fr) auto"><strong>{{ ucfirst($t) }}</strong><span class="num">{{ $n }} registros</span></div>@endforeach
  </div>
  <div class="acts" style="justify-content:flex-start;margin-top:16px"><a class="btn alt" href="{{ route('respaldo.descargar') }}">Descargar respaldo ahora</a></div>
  <p class="aviso" style="margin-top:16px">Para restaurar: crea la base con <code>php artisan migrate</code> y luego importa el archivo desde phpMyAdmin (pestaña Importar). Guarda el archivo fuera de esta computadora (USB o correo). El respaldo contiene las contraseñas ya cifradas de los usuarios; trátalo como información privada.</p>
</section>
@endsection
