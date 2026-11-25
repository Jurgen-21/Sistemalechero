@extends('layouts.app')
@section('titulo', 'Estado de cuenta')
@section('contenido')
@include('partials.cabecera', ['h' => 'Estado de cuenta', 'p' => $proveedor->nombre . ' · ' . ($proveedor->zona ?: 'Sin zona') . ' · emitido el ' . now()->format('d/m/Y')])
<div class="acts noprint" style="justify-content:flex-start;margin-bottom:16px">
  <button class="btn alt" type="button" onclick="print()">Imprimir / Guardar PDF</button>
  <a class="btn ghost" href="{{ route('reportes.cuenta', [$proveedor, 'csv' => 1]) }}">Descargar CSV</a>
  <a class="btn ghost" href="{{ route('proveedores.show', $proveedor) }}">Volver</a>
</div>
<div class="grid g3">
  <div class="kpi"><small>Total entregado</small><b>S/ {{ number_format($mov->sum('cargo'), 2) }}</b></div>
  <div class="kpi"><small>Total pagado</small><b>S/ {{ number_format($mov->sum('abono'), 2) }}</b></div>
  <div class="kpi hero"><small>Saldo que se debe</small><b>S/ {{ number_format(max($saldo, 0), 2) }}</b></div>
</div>
<section class="card" style="margin-top:16px">
  <h2>Movimientos</h2>
  <div class="list" style="margin-top:8px">
    @forelse ($mov as $m)
      <div class="row ent" style="grid-template-columns:90px minmax(0,1fr) 1fr 1fr 1fr">
        <span class="num">{{ $m['fecha']->format('d/m/Y') }}</span>
        <span><span class="tag {{ $m['tipo'] === 'Pago' ? '' : 'y' }}">{{ $m['tipo'] }}</span> <small>{{ $m['detalle'] }}</small></span>
        <span class="hm"><small>Entregado</small><br><b class="num">{{ $m['cargo'] ? 'S/ ' . number_format($m['cargo'], 2) : '—' }}</b></span>
        <span class="hm"><small>Pagado</small><br><b class="num">{{ $m['abono'] ? 'S/ ' . number_format($m['abono'], 2) : '—' }}</b></span>
        <span><small>Saldo</small><br><b class="num {{ $m['saldo'] > 0.5 ? 'neg' : '' }}">S/ {{ number_format($m['saldo'], 2) }}</b></span>
      </div>
    @empty <div class="empty"><span class="blob"></span><b>Sin movimientos</b>Este proveedor aún no tiene entregas ni pagos.</div> @endforelse
  </div>
</section>
@endsection
