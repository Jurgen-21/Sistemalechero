@extends('layouts.app')
@section('titulo', 'Pagos')
@section('contenido')
@include('partials.cabecera', ['h' => 'Pagos a proveedores', 'p' => 'Registra pagos y controla cuánto debes a cada proveedor', 'mes' => $mes, 'chip' => 'Pagado en el mes: S/ ' . number_format($pagadoMes, 2)])

<div class="grid g4" style="margin-bottom:16px">
  <div class="kpi"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/></svg></span><small>Total entregado</small><b>S/ {{ number_format($entregado, 2) }}</b></div>
  <div class="kpi"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="3"/><circle cx="12" cy="12" r="2.5"/></svg></span><small>Total pagado</small><b>S/ {{ number_format($pagado, 2) }}</b></div>
  <div class="kpi hero"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 8v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></span><small>Por pagar</small><b>S/ {{ number_format($pendiente, 2) }}</b></div>
  <div class="kpi"><span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg></span><small>Pagado este mes</small><b>S/ {{ number_format($pagadoMes, 2) }}</b></div>
</div>

<section class="card" id="form-pago">
  <div class="card-h">
    <span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
    <div><h2>Registrar pago</h2><small>No puedes pagar más de lo que debes al proveedor</small></div>
  </div>
  @if ($proveedores->isEmpty())
    <div class="empty"><span class="blob"></span><b>Aún no hay proveedores</b><a class="btn alt" href="{{ route('proveedores.index') }}">Agregar un proveedor</a></div>
  @else
  <form method="POST" action="{{ route('pagos.store') }}">
    @csrf
    <div class="form">
      <label>Fecha<input class="in" type="date" name="fecha" max="{{ now()->toDateString() }}" min="2020-01-01" value="{{ old('fecha', now()->toDateString()) }}" required></label>
      <label>Proveedor
        <select class="in" name="proveedor_id" id="pp" required>
          @foreach ($proveedores as $p)
            <option value="{{ $p->id }}" data-saldo="{{ number_format(max($p->saldo, 0), 2, '.', '') }}" @selected((int) old('proveedor_id', $seleccionado) === $p->id)>{{ $p->nombre }} — debes S/ {{ number_format(max($p->saldo, 0), 2) }}</option>
          @endforeach
        </select></label>
      <label>Monto (S/)<input class="in" type="number" step="0.01" min="0.01" name="monto" id="pm" value="{{ old('monto') }}" required></label>
      <label>Nota (opcional)<input class="in" name="nota" maxlength="160" value="{{ old('nota') }}" placeholder="Ej. adelanto, efectivo, Yape"></label>
      <button type="button" class="btn ghost" id="todo">Pagar todo el saldo</button>
      <button class="btn alt">Registrar pago</button>
    </div>
  </form>
  @endif
</section>

<section class="card" style="margin-top:16px">
  <div class="card-h"><div><h2>Estado por proveedor</h2><small>Lo entregado menos lo pagado</small></div></div>
  <div class="pgrid">
    @forelse ($proveedores as $p)
      @php $tot = $p->total_entregado; $deb = max($p->saldo, 0); $pct = $tot > 0 ? min(100, ($tot - $deb) / $tot * 100) : 0; @endphp
      <div class="pcard">
        <div class="pc-h"><a href="{{ route('proveedores.show', $p) }}"><strong>{{ $p->nombre }}</strong></a><span class="tag {{ $deb > 0.5 ? 'deb' : '' }}">{{ $deb > 0.5 ? 'Debes S/ ' . number_format($deb, 2) : 'Al día' }}</span></div>
        <small>Entregado S/ {{ number_format($tot, 2) }} · Pagado S/ {{ number_format($tot - $deb, 2) }}</small>
        <div class="bar" style="margin:10px 0 12px"><i style="width:{{ $pct }}%"></i></div>
        @if ($deb > 0.5)<button type="button" class="mini y" data-pagar="{{ $p->id }}">Pagar a {{ \Illuminate\Support\Str::before($p->nombre, ' ') }}</button>@endif
      </div>
    @empty
      <div class="empty"><span class="blob"></span><b>Sin proveedores</b></div>
    @endforelse
  </div>
</section>

<section class="card" style="margin-top:16px">
  <div class="card-h"><div><h2>Pagos del mes</h2></div></div>
  <div class="list">
    @forelse ($pagos as $x)
      <div class="row">
        <span><strong>{{ $x->proveedor->nombre }}</strong><small>{{ $x->fecha->format('d/m/Y') }} {{ $x->nota ? '· ' . $x->nota : '' }}</small></span>
        <b class="num">S/ {{ number_format($x->monto, 2) }}</b>
        @include('partials.acciones', ['url_editar' => route('pagos.update', $x), 'url_borrar' => route('pagos.destroy', $x), 'titulo' => 'Editar pago a ' . $x->proveedor->nombre, 'confirmar' => '¿Eliminar este pago?',
          'vals' => ['fecha' => $x->fecha->format('Y-m-d'), 'monto' => $x->monto, 'nota' => $x->nota]])
      </div>
    @empty
      <div class="empty"><span class="blob"></span><b>Sin pagos en este mes</b>Registra el primero con el formulario de arriba.</div>
    @endforelse
  </div>
  {{ $pagos->links('pagination::simple-default') }}
</section>

@include('partials.editar', ['campos' => [['fecha', 'Fecha', 'date'], ['monto', 'Monto (S/)', 'number'], ['nota', 'Nota', 'text', null, 'opcional wide']]])

<script>
const pp = document.getElementById('pp'), pm = document.getElementById('pm');
if (pp) {
  document.getElementById('todo').onclick = () => { pm.value = pp.selectedOptions[0].dataset.saldo; pm.focus(); };
  document.querySelectorAll('[data-pagar]').forEach(b => b.onclick = () => {
    pp.value = b.dataset.pagar; pm.value = pp.selectedOptions[0].dataset.saldo;
    document.getElementById('form-pago').scrollIntoView({behavior: 'smooth', block: 'center'}); pm.focus();
  });
}
</script>
@endsection
