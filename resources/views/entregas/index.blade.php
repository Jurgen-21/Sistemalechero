@extends('layouts.app')
@section('titulo', 'Entregas')
@section('contenido')
@include('partials.cabecera', ['h' => 'Recepción de leche', 'p' => 'Litros que te entregan tus proveedores', 'mes' => $mes, 'chip' => number_format($litros, 0) . ' L · S/ ' . number_format($total, 2)])

<div class="grid g3" style="margin-bottom:16px">
  <div class="kpi"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/></svg></span><small>Litros recibidos</small><b>{{ number_format($litros, 1) }} L</b></div>
  <div class="kpi hero"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 3v18M16 7c-1-1.5-2.4-2-4-2-2.5 0-4 1.2-4 3s1.5 2.6 4 3 4 1.2 4 3-1.5 3-4 3c-1.8 0-3.2-.6-4.2-2"/></svg></span><small>Costo de la leche del mes</small><b>S/ {{ number_format($total, 2) }}</b></div>
  <div class="kpi"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/></svg></span><small>Entregas registradas</small><b>{{ $numero }}</b></div>
</div>

<section class="card">
  <div class="card-h">
    <span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
    <div><h2>Nueva entrega</h2><small>El precio se completa solo según el proveedor</small></div>
  </div>
  @if ($proveedores->isEmpty())
    <div class="empty"><span class="blob"></span><b>No hay proveedores activos</b><a class="btn alt" href="{{ route('proveedores.index') }}">Agregar un proveedor</a></div>
  @else
  <form method="POST" action="{{ route('entregas.store') }}">
    @csrf
    <div class="form">
      <label>Fecha<input class="in" type="date" name="fecha" max="{{ now()->toDateString() }}" min="2020-01-01" value="{{ old('fecha', now()->toDateString()) }}" required></label>
      <label>Proveedor
        <select class="in" name="proveedor_id" id="fp" required>
          @foreach ($proveedores as $p)
            <option value="{{ $p->id }}" data-precio="{{ $p->precio_litro }}" @selected((int) old('proveedor_id', $seleccionado->id) === $p->id)>{{ $p->nombre }} — {{ $p->zona }}</option>
          @endforeach
        </select></label>
      <label>Litros<input class="in" type="number" step="0.1" min="0.1" name="litros" id="fl" value="{{ old('litros') }}" inputmode="decimal" required></label>
      <label>Precio por litro (S/)<input class="in" type="number" step="0.01" min="0" name="precio_litro" id="fr" value="{{ old('precio_litro', $seleccionado->precio_litro) }}" required></label>
      <div class="imp"><small>Importe</small><b id="fi">S/ 0.00</b></div>
      <button class="btn alt">Registrar entrega</button>
    </div>
  </form>
  @endif
</section>

<section class="card" style="margin-top:16px">
  <div class="card-h"><div><h2>Entregas del mes</h2></div></div>
  <div class="list">
    @forelse ($entregas as $e)
      <div class="row ent">
        <span><strong>{{ $e->proveedor->nombre }}</strong><small>{{ $e->fecha->format('d/m/Y') }}</small></span>
        <span><small>Litros</small><br><b class="num">{{ number_format($e->litros, 1) }}</b></span>
        <span><small>Precio</small><br><b class="num">S/ {{ number_format($e->precio_litro, 2) }}</b></span>
        <b class="num">S/ {{ number_format($e->importe, 2) }}</b>
        @include('partials.acciones', ['url_editar' => route('entregas.update', $e), 'url_borrar' => route('entregas.destroy', $e), 'titulo' => 'Editar entrega de ' . $e->proveedor->nombre, 'confirmar' => '¿Eliminar esta entrega?',
          'vals' => ['fecha' => $e->fecha->format('Y-m-d'), 'litros' => $e->litros, 'precio_litro' => $e->precio_litro]])
      </div>
    @empty
      <div class="empty"><span class="blob"></span><b>Sin entregas en este mes</b>Registra la primera con el formulario de arriba.</div>
    @endforelse
  </div>
  {{ $entregas->links('pagination::simple-default') }}
</section>

@include('partials.editar', ['campos' => [['fecha', 'Fecha', 'date'], ['litros', 'Litros', 'number'], ['precio_litro', 'Precio por litro (S/)', 'number']]])

<script>
const sel = document.getElementById('fp'), l = document.getElementById('fl'), r = document.getElementById('fr'), i = document.getElementById('fi');
if (sel) {
  const calc = () => i.textContent = 'S/ ' + ((+l.value || 0) * (+r.value || 0)).toFixed(2);
  sel.onchange = () => { r.value = sel.selectedOptions[0].dataset.precio; calc(); };
  [l, r].forEach(e => e.oninput = calc);
  calc();
}
</script>
@endsection
