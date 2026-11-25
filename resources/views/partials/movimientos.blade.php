@include('partials.cabecera', ['h' => $titulo, 'p' => $subtitulo, 'mes' => $mes, 'chip' => 'Total S/ ' . number_format($total, 2)])

@isset($cuadre)
  @include('partials.cuadre')
@endisset

<section class="card">
  <div class="card-h"><span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span><div><h2>Nuevo registro</h2></div></div>
  <form method="POST" action="{{ route($ruta . '.store') }}">
    @csrf
    <div class="form">
      <label>Fecha<input class="in" type="date" name="fecha" max="{{ now()->toDateString() }}" min="2020-01-01" value="{{ old('fecha', now()->toDateString()) }}" required></label>
      <label>{{ $etiquetaCampo }}
        <select class="in" name="{{ $campo }}" required>@foreach ($opciones as $o)<option @selected(old($campo) === $o)>{{ $o }}</option>@endforeach</select></label>
      <label>{{ $etiqueta2 }}<input class="in" name="{{ $campo2 }}" type="{{ $tipo2 }}" step="any" inputmode="{{ $tipo2 === 'number' ? 'decimal' : 'text' }}" value="{{ old($campo2) }}"></label>
      <label>Monto (S/)<input class="in" type="number" step="0.01" min="0.01" inputmode="decimal" name="monto" value="{{ old('monto') }}" required></label>
      <button class="btn alt">Guardar</button>
    </div>
  </form>
</section>

<section class="card">
  <div class="card-h"><div><h2>Resumen por {{ strtolower($etiquetaCampo) }}</h2></div></div>
  <div class="list">
    @foreach ($opciones as $o)
      @php $m = $porGrupo[$o] ?? 0; @endphp
      <div class="row" style="grid-template-columns:minmax(80px,1fr) 2fr auto"><strong>{{ $o }}</strong>
        <div class="bar"><i style="width:{{ $total > 0 ? $m / $total * 100 : 0 }}%;background:{{ $color }}"></i></div>
        <b class="num">S/ {{ number_format($m, 2) }}</b></div>
    @endforeach
  </div>
</section>

<section class="card">
  <div class="card-h"><div><h2>Registros del mes</h2></div></div>
  <div class="list">
    @forelse ($registros as $x)
      <div class="row">
        <span><strong>{{ $x->{$campo} }}</strong><small>{{ $x->fecha->format('d/m/Y') }}@if($x->{$campo2}) · {{ $x->{$campo2} }}@endif</small></span>
        <b class="num">S/ {{ number_format($x->monto, 2) }}</b>
        @include('partials.acciones', ['url_editar' => route($ruta . '.update', $x), 'url_borrar' => route($ruta . '.destroy', $x),
          'vals' => ['fecha' => $x->fecha->format('Y-m-d'), $campo => $x->{$campo}, $campo2 => $x->{$campo2}, 'monto' => $x->monto]])
      </div>
    @empty
      <div class="empty"><span class="blob"></span><b>Sin registros en este mes</b>Usa el formulario de arriba para agregar el primero.</div>
    @endforelse
  </div>
  {{ $registros->links('pagination::simple-default') }}
</section>

@include('partials.editar', ['campos' => [
  ['fecha', 'Fecha', 'date'],
  [$campo, $etiquetaCampo, 'select', $opciones],
  [$campo2, $etiqueta2, $tipo2, null, 'opcional' . ($tipo2 === 'text' ? ' wide' : '')],
  ['monto', 'Monto (S/)', 'number'],
]])
