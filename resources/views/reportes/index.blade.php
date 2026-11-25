@extends('layouts.app')
@section('titulo', 'Reportes')
@section('contenido')
@php $nombre = ucfirst($mes->locale('es')->translatedFormat('F Y')); $m = $mes->format('Y-m'); @endphp
@include('partials.cabecera', ['h' => 'Reporte de ' . $nombre, 'p' => 'Resumen mensual para imprimir, guardar como PDF o abrir en Excel', 'mes' => $mes])

<div class="acts noprint" style="justify-content:flex-start;margin-bottom:16px">
  <button class="btn alt" type="button" onclick="print()">Imprimir / Guardar PDF</button>
</div>

@include('partials.cuadre', ['cuadre' => $cuadre])

<div class="grid g2" style="margin-top:16px">
  <section class="card"><h2>Ventas por producto</h2><div class="list" style="margin-top:8px">
    @forelse ($ventas as $v)
      <div class="row" style="grid-template-columns:minmax(0,1fr) auto auto"><strong>{{ $v->producto }}</strong><small>{{ $v->cantidad ? number_format($v->cantidad, 1) . ' u.' : '' }}</small><b class="num">S/ {{ number_format($v->monto, 2) }}</b></div>
    @empty <p>Sin ventas este mes.</p> @endforelse
  </div></section>
  <section class="card"><h2>Gastos por categoría</h2><div class="list" style="margin-top:8px">
    @forelse ($gastos as $g)
      <div class="row" style="grid-template-columns:minmax(0,1fr) auto"><strong>{{ $g->categoria }}</strong><b class="num">S/ {{ number_format($g->monto, 2) }}</b></div>
    @empty <p>Sin gastos este mes.</p> @endforelse
  </div></section>
</div>

<section class="card" style="margin-top:16px">
  <h2>Proveedores</h2><small>Leche del mes, pagos del mes y saldo acumulado</small>
  <div class="list" style="margin-top:10px">
    @forelse ($proveedores as $p)
      <div class="row ent" style="grid-template-columns:minmax(0,1.4fr) 1fr 1fr 1fr auto">
        <a href="{{ route('proveedores.show', $p) }}" style="text-decoration:none"><strong>{{ $p->nombre }}</strong><small>{{ $p->zona }}</small></a>
        <span class="hm"><small>Litros</small><br><b class="num">{{ number_format($p->litros_mes ?? 0, 1) }}</b></span>
        <span class="hm"><small>Importe</small><br><b class="num">S/ {{ number_format($p->importe_mes ?? 0, 2) }}</b></span>
        <span class="hm"><small>Pagado</small><br><b class="num">S/ {{ number_format($p->pagado_mes ?? 0, 2) }}</b></span>
        <span><small>Saldo</small><br><b class="num {{ $p->saldo > 0.5 ? 'neg' : '' }}">S/ {{ number_format(max($p->saldo, 0), 2) }}</b></span>
      </div>
    @empty <p>Sin proveedores.</p> @endforelse
  </div>
</section>

<div class="grid g2" style="margin-top:16px">
  <section class="card"><h2>Distribuciones por zona</h2><div class="list" style="margin-top:8px">
    @forelse ($zonas as $z)
      <div class="row" style="grid-template-columns:minmax(0,1fr) auto auto"><strong>{{ $z->zona }}</strong><small>{{ $z->n }} envíos</small><b class="num">S/ {{ number_format($z->monto, 2) }}</b></div>
    @empty <p>Sin distribuciones este mes.</p> @endforelse
  </div></section>
  <section class="card"><h2>Existencias hoy</h2><div class="list" style="margin-top:8px">
    @foreach ($stock as $n => $s)
      <div class="row" style="grid-template-columns:minmax(0,1fr) auto"><strong>{{ $n }}</strong><b class="num {{ $s['stock'] < -0.005 ? 'neg' : '' }}">{{ number_format($s['stock'], 1) }} {{ $s['unidad'] }}</b></div>
    @endforeach
  </div></section>
</div>

<section class="card noprint" style="margin-top:16px">
  <div class="card-h"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M4 20h16"/></svg></span><div><h2>Descargar datos (CSV para Excel)</h2><small>Del mes mostrado, o el historial completo</small></div></div>
  <div class="list">
    @foreach ($tipos as $clave => $titulo)
      <div class="row" style="grid-template-columns:minmax(0,1fr) auto auto"><strong>{{ $titulo }}</strong>
        <a class="mini" href="{{ route('reportes.exportar', ['tipo' => $clave, 'mes' => $m]) }}">Este mes</a>
        <a class="mini" href="{{ route('reportes.exportar', ['tipo' => $clave, 'mes' => 'todo']) }}">Todo</a></div>
    @endforeach
  </div>
</section>
@endsection
