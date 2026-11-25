@extends('layouts.app')
@section('titulo', 'Tablero')
@section('contenido')
@php
  $max = max($serie->max('ventas'), $serie->max('egresos'), 1);
  $nombreMes = ucfirst($mes->locale('es')->translatedFormat('F'));
@endphp
@include('partials.cabecera', ['h' => ($perdida ? 'Pérdida' : 'Ganancia') . " de $nombreMes", 'p' => 'Ventas menos costo de la leche y gastos operativos', 'mes' => $mes])

<div class="grid g4">
  <div class="kpi {{ $perdida ? 'warn' : 'hero' }}"><small>{{ $perdida ? 'Resultado del mes' : 'Ganancia del mes' }}</small><b>S/ {{ number_format($t['ganancia'], 2) }}</b></div>
  <div class="kpi"><small>Ventas</small><b>S/ {{ number_format($t['ventas'], 2) }}</b></div>
  <div class="kpi"><small>Costo de la leche</small><b>S/ {{ number_format($t['leche'], 2) }}</b></div>
  <div class="kpi"><small>Gastos operativos</small><b>S/ {{ number_format($t['gastos'], 2) }}</b></div>
</div>

<div class="grid g2" style="margin-top:16px">
  <section class="card">
    <h2>¿A dónde va cada sol vendido?</h2>
    <div class="jug" role="img" aria-label="Reparto de las ventas">
      <i style="width:{{ $pct($t['leche']) }}%;background:var(--butter)"></i>
      <i style="width:{{ $pct($t['gastos']) }}%;background:var(--berry)"></i>
      <i style="width:{{ $pct($t['ganancia']) }}%;background:var(--green)"></i>
    </div>
    <div class="leg">
      <span style="--c:var(--butter)">Leche {{ $pct($t['leche']) }}%</span>
      <span style="--c:var(--berry)">Gastos {{ $pct($t['gastos']) }}%</span>
      <span style="--c:var(--green)">Ganancia {{ $pct($t['ganancia']) }}%</span>
    </div>
    @if ($perdida)
      <p class="aviso" style="margin:16px 0 0">Este mes los egresos superan a las ventas. El reparto se muestra sobre el total de egresos.</p>
    @else
      <p style="color:var(--mut);margin:16px 0 0">El costo de la leche usa el importe de las entregas, no los pagos. Lo que aún debes no cambia la ganancia.</p>
    @endif
  </section>

  <section class="card">
    <h2>Ventas y egresos, 6 meses</h2>
    <div class="bars">
      @foreach ($serie as $m)
        <div class="m"><div class="pair">
          <i style="height:{{ $m['ventas'] / $max * 100 }}%;background:var(--green)" title="Ventas S/ {{ number_format($m['ventas'], 2) }}"></i>
          <i style="height:{{ $m['egresos'] / $max * 100 }}%;background:var(--butter)" title="Egresos S/ {{ number_format($m['egresos'], 2) }}"></i>
        </div><span>{{ $m['mes'] }}</span></div>
      @endforeach
    </div>
    <div class="leg" style="margin-top:12px"><span style="--c:var(--green)">Ventas</span><span style="--c:var(--butter)">Leche + gastos</span></div>
  </section>
</div>

<section class="card" style="margin-top:16px">
  <div class="card-h"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5 9 5zM3 8v8l9 5 9-5V8M12 13v8"/></svg></span><div><h2>Existencias</h2><small>Lo que hay ahora en almacén · <a href="{{ route('produccion.index') }}">ver inventario</a></small></div></div>
  <div class="grid g3 keep">
    @foreach ($stock as $nombre => $s)
      <div class="kpi" style="background:rgb(var(--edge-rgb) / .04);box-shadow:none"><small>{{ $nombre }}</small><b class="{{ $s['stock'] < -0.005 ? 'neg' : '' }}">{{ number_format($s['stock'], 1) }} {{ $s['unidad'] }}</b></div>
    @endforeach
  </div>
</section>

<div style="margin-top:16px">@include('partials.cuadre', ['cuadre' => $cuadre])</div>

<section class="card" style="margin-top:16px">
  <h2>Saldo pendiente con proveedores</h2>
  <div class="list" style="margin-top:8px">
    @forelse ($deudas as $p)
      <a class="row" style="grid-template-columns:minmax(0,1fr) auto" href="{{ route('proveedores.show', $p) }}"><strong>{{ $p->nombre }}</strong><span class="num">S/ {{ number_format($p->saldo, 2) }}</span></a>
    @empty
      <p>No debes nada a tus proveedores.</p>
    @endforelse
  </div>
</section>
@endsection
