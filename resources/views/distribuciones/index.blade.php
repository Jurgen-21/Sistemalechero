@extends('layouts.app')
@section('titulo', 'Distribuciones')
@section('contenido')
@php
  $zonas = ['Zurite', 'Cusco']; $unidades = ['litros', 'kg', 'unidades', 'cajas']; $estados = ['Pendiente', 'En ruta', 'Entregado', 'Cancelado'];
  $tono = ['Pendiente' => 'y', 'En ruta' => 'y', 'Entregado' => '', 'Cancelado' => 'off'];
@endphp
@include('partials.cabecera', ['h' => 'Distribuciones', 'p' => 'Seguimiento de despachos hacia Zurite y Cusco'])
<div class="grid g4">
  <div class="kpi"><small>Registros encontrados</small><b>{{ $resumen['total'] }}</b></div>
  <div class="kpi"><small>En proceso</small><b>{{ $resumen['pendientes'] }}</b></div>
  <div class="kpi"><small>Entregas completadas</small><b>{{ $resumen['entregadas'] }}</b></div>
  <div class="kpi hero"><small>Importe registrado</small><b>S/ {{ number_format($resumen['monto'], 2) }}</b></div>
</div>
<section class="card" style="margin-top:16px">
  <div class="card-h"><span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span><div><h2>Registrar distribución</h2></div></div>
  <form method="POST" action="{{ route('distribuciones.store') }}">
    @csrf
    <div class="form">
      <label>Fecha<input class="in" type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required></label>
      <label>Zona de destino<select class="in" name="zona" required>@foreach($zonas as $z)<option @selected(old('zona')===$z)>{{ $z }}</option>@endforeach</select></label>
      <label>Destinatario / Cliente<input class="in" name="destinatario" value="{{ old('destinatario') }}" maxlength="120" required></label>
      <label>Producto<input class="in" name="producto" value="{{ old('producto') }}" placeholder="Leche, queso, yogur..." required></label>
      <label>Cantidad<input class="in" type="number" name="cantidad" step="0.01" min="0.01" inputmode="decimal" value="{{ old('cantidad') }}" required></label>
      <label>Unidad<select class="in" name="unidad" required>@foreach($unidades as $u)<option>{{ $u }}</option>@endforeach</select></label>
      <label>Importe (S/)<input class="in" type="number" name="monto" step="0.01" min="0" inputmode="decimal" value="{{ old('monto', '0.00') }}" required></label>
      <label>Estado<select class="in" name="estado" required>@foreach($estados as $e)<option>{{ $e }}</option>@endforeach</select></label>
      <label style="grid-column:1/-1">Observaciones<input class="in" name="observacion" maxlength="255" value="{{ old('observacion') }}"></label>
    </div>
    <div style="margin-top:14px"><button class="btn alt">Guardar distribución</button></div>
  </form>
</section>
<section class="card" style="margin-top:16px">
  <div class="card-h"><div><h2>Consulta de distribuciones</h2></div></div>
  <form method="GET" class="tools">
    <label>Desde<input class="in" type="date" name="desde" value="{{ request('desde') }}"></label>
    <label>Hasta<input class="in" type="date" name="hasta" value="{{ request('hasta') }}"></label>
    <select class="in" name="zona" aria-label="Zona"><option value="">Todas las zonas</option>@foreach($zonas as $z)<option @selected(request('zona')===$z)>{{ $z }}</option>@endforeach</select>
    <select class="in" name="estado" aria-label="Estado"><option value="">Todos los estados</option>@foreach($estados as $estado)<option @selected(request('estado')===$estado)>{{ $estado }}</option>@endforeach</select>
    <button class="btn">Filtrar</button>
    <a class="btn ghost" href="{{ route('distribuciones.index') }}">Limpiar</a>
  </form>
  <div class="list">
    @forelse($distribuciones as $d)
      <div class="row">
        <div>
          <strong>{{ $d->producto }} · {{ $d->zona }}</strong>
          <small>{{ $d->fecha->format('d/m/Y') }} · {{ $d->destinatario }} · {{ number_format($d->cantidad,2) }} {{ $d->unidad }}</small>
          @if($d->observacion)<small>{{ $d->observacion }}</small>@endif
        </div>
        <span class="tag {{ $tono[$d->estado] ?? '' }}">{{ $d->estado }}</span>
        <b class="num">S/ {{ number_format($d->monto,2) }}</b>
        @include('partials.acciones', ['url_editar' => route('distribuciones.update', $d), 'url_borrar' => route('distribuciones.destroy', $d), 'titulo' => 'Editar distribución',
          'vals' => ['fecha' => $d->fecha->format('Y-m-d'), 'zona' => $d->zona, 'destinatario' => $d->destinatario, 'producto' => $d->producto, 'cantidad' => $d->cantidad, 'unidad' => $d->unidad, 'monto' => $d->monto, 'estado' => $d->estado, 'observacion' => $d->observacion]])
      </div>
    @empty <div class="empty"><span class="blob"></span><b>Sin distribuciones</b>No hay registros para los filtros seleccionados.</div> @endforelse
  </div>
  {{ $distribuciones->links('pagination::simple-default') }}
</section>
@include('partials.editar', ['campos' => [
  ['fecha', 'Fecha', 'date', null, 'futuro'], ['zona', 'Zona', 'select', $zonas], ['destinatario', 'Destinatario', 'text'], ['producto', 'Producto', 'text'],
  ['cantidad', 'Cantidad', 'number'], ['unidad', 'Unidad', 'select', $unidades], ['monto', 'Importe (S/)', 'number'], ['estado', 'Estado', 'select', $estados],
  ['observacion', 'Observación', 'text', null, 'opcional wide'],
]])
@endsection
