@extends('layouts.app')
@section('titulo', 'Inventario')
@section('contenido')
@include('partials.cabecera', ['h' => 'Inventario', 'p' => 'Existencias y producción de queso y yogur', 'mes' => $mes])

<div class="grid g3">
  @foreach ($stock as $nombre => $s)
    <div class="kpi {{ $s['stock'] < -0.005 ? 'warn' : ($loop->first ? 'hero' : '') }}">
      <small>Existencias de {{ strtolower($nombre) }}</small>
      <b>{{ number_format($s['stock'], 1) }} {{ $s['unidad'] }}</b>
      @if ($s['stock'] < -0.005)<small>Revisa: se vendió más de lo producido</small>@endif
    </div>
  @endforeach
</div>
<p class="aviso" style="margin-top:14px">Leche = litros recibidos − litros usados en producción − leche vendida. Queso y yogur = producido − vendido (las ventas descuentan según la cantidad que registres).</p>

<section class="card">
  <div class="card-h"><span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span><div><h2>Registrar producción</h2><small>Cuánta leche se usó y cuánto producto salió</small></div></div>
  <form method="POST" action="{{ route('produccion.store') }}">
    @csrf
    <div class="form">
      <label>Fecha<input class="in" type="date" name="fecha" max="{{ now()->toDateString() }}" min="2020-01-01" value="{{ old('fecha', now()->toDateString()) }}" required></label>
      <label>Producto<select class="in" name="producto" required>@foreach ($productos as $p => $u)<option value="{{ $p }}" @selected(old('producto') === $p)>{{ $p }} ({{ $u }})</option>@endforeach</select></label>
      <label>Litros de leche usados<input class="in" type="number" step="0.1" min="0.1" inputmode="decimal" name="litros_usados" value="{{ old('litros_usados') }}" required></label>
      <label>Cantidad obtenida<input class="in" type="number" step="0.01" min="0.01" inputmode="decimal" name="cantidad" value="{{ old('cantidad') }}" required></label>
      <label>Nota<input class="in" name="nota" maxlength="160" value="{{ old('nota') }}" placeholder="Opcional"></label>
      <button class="btn alt">Guardar producción</button>
    </div>
  </form>
</section>

<section class="card">
  <div class="card-h"><div><h2>Rendimiento del mes</h2><small>Litros de leche por unidad de producto</small></div></div>
  <div class="list">
    @foreach ($productos as $p => $u)
      @php $x = $porProducto[$p] ?? null; $rend = $x && $x->cantidad > 0 ? $x->litros / $x->cantidad : null; @endphp
      <div class="row" style="grid-template-columns:minmax(0,1fr) auto auto">
        <strong>{{ $p }}</strong>
        <span><small>Producido</small><br><b class="num">{{ number_format($x->cantidad ?? 0, 1) }} {{ $u }}</b></span>
        <span><small>Rinde</small><br><b class="num">{{ $rend ? number_format($rend, 2) . ' L por ' . $u : '—' }}</b></span>
      </div>
    @endforeach
  </div>
</section>

<section class="card">
  <div class="card-h"><div><h2>Producción del mes</h2></div></div>
  <div class="list">
    @forelse ($registros as $x)
      <div class="row">
        <span><strong>{{ $x->producto }} · {{ number_format($x->cantidad, 1) }} {{ $productos[$x->producto] ?? '' }}</strong><small>{{ $x->fecha->format('d/m/Y') }} · {{ number_format($x->litros_usados, 1) }} L de leche @if($x->nota)· {{ $x->nota }}@endif</small></span>
        @include('partials.acciones', ['url_editar' => route('produccion.update', $x), 'url_borrar' => route('produccion.destroy', $x), 'titulo' => 'Editar producción',
          'vals' => ['fecha' => $x->fecha->format('Y-m-d'), 'producto' => $x->producto, 'litros_usados' => $x->litros_usados, 'cantidad' => $x->cantidad, 'nota' => $x->nota]])
      </div>
    @empty
      <div class="empty"><span class="blob"></span><b>Sin producción en este mes</b>Registra la primera con el formulario de arriba.</div>
    @endforelse
  </div>
  {{ $registros->links('pagination::simple-default') }}
</section>

@include('partials.editar', ['campos' => [['fecha', 'Fecha', 'date'], ['producto', 'Producto', 'select', array_keys($productos)], ['litros_usados', 'Litros usados', 'number'], ['cantidad', 'Cantidad obtenida', 'number'], ['nota', 'Nota', 'text', null, 'opcional wide']]])
@endsection
