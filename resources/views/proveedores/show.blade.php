@extends('layouts.app')
@section('titulo', $proveedor->nombre)
@section('contenido')
@php $bloqueado = $entregas->isNotEmpty() || $pagos->isNotEmpty(); $admin = auth()->user()->esAdmin(); @endphp
@include('partials.cabecera', ['h' => $proveedor->nombre, 'p' => ($proveedor->zona ?: 'Sin zona') . ' · S/ ' . number_format($proveedor->precio_litro, 2) . ' por litro', 'chip' => $proveedor->activo ? 'Activo' : 'Inactivo'])

<div class="grid g3">
  <div class="kpi"><small>Litros entregados</small><b>{{ number_format($proveedor->litros_total ?? 0, 0) }}</b></div>
  <div class="kpi"><small>Total entregado</small><b>S/ {{ number_format($proveedor->total_entregado, 2) }}</b></div>
  <div class="kpi hero"><small>Saldo que debes</small><b>S/ {{ number_format(max($proveedor->saldo, 0), 2) }}</b></div>
</div>

<div class="grid g2" style="margin-top:16px">
  <section class="card"><h2>Últimas entregas</h2><div class="list" style="margin-top:8px">
    @forelse ($entregas as $e)
      <div class="row" style="grid-template-columns:minmax(0,1fr) auto"><span>{{ $e->fecha->format('d/m/Y') }} · {{ number_format($e->litros, 0) }} L</span><span class="num">S/ {{ number_format($e->importe, 2) }}</span></div>
    @empty <p>Aún no tiene entregas.</p> @endforelse
  </div></section>
  <section class="card"><h2>Últimos pagos</h2><div class="list" style="margin-top:8px">
    @forelse ($pagos as $p)
      <div class="row" style="grid-template-columns:minmax(0,1fr) auto"><span>{{ $p->fecha->format('d/m/Y') }} {{ $p->nota }}</span><span class="num">S/ {{ number_format($p->monto, 2) }}</span></div>
    @empty <p>Aún no tiene pagos.</p> @endforelse
  </div></section>
</div>

<section class="card" style="margin-top:16px">
  <h2 style="margin-bottom:14px">Datos del proveedor</h2>
  <form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="form">
    @csrf @method('PUT')
    <label>Nombre<input class="in" name="nombre" value="{{ old('nombre', $proveedor->nombre) }}" required></label>
    <label>Zona<input class="in" name="zona" value="{{ old('zona', $proveedor->zona) }}" required></label>
    <label>Teléfono<input class="in" name="telefono" value="{{ old('telefono', $proveedor->telefono) }}" inputmode="numeric" maxlength="9"></label>
    <label>Fecha de registro<input class="in" name="fecha_registro" type="date" min="2020-01-01" max="{{ now()->toDateString() }}" value="{{ old('fecha_registro', optional($proveedor->fecha_registro)->format('Y-m-d')) }}"></label>
    <label>Precio por litro<input class="in" name="precio_litro" type="number" step="0.01" min="0" inputmode="decimal" value="{{ old('precio_litro', $proveedor->precio_litro) }}" required></label>
    <button class="btn">Guardar cambios</button>
  </form>
  <div class="acts" style="justify-content:flex-start;margin-top:18px">
    <a class="btn alt" href="{{ route('reportes.cuenta', $proveedor) }}">Estado de cuenta</a>
    <form method="POST" action="{{ route('proveedores.estado', $proveedor) }}">@csrf @method('PATCH')
      <button class="btn ghost">{{ $proveedor->activo ? 'Desactivar' : 'Activar' }}</button></form>
    @if ($admin)
    <form method="POST" action="{{ route('proveedores.destroy', $proveedor) }}" data-confirmar="¿Eliminar a {{ $proveedor->nombre }}?" data-detalle="Se borrará su ficha. Esta acción no se puede deshacer.">@csrf @method('DELETE')
      <button class="btn danger" @disabled($bloqueado)>Eliminar</button></form>
    @endif
    <a class="btn ghost" href="{{ route('proveedores.index') }}">Volver</a>
  </div>
  @if ($bloqueado && $admin)<p style="color:var(--mut);font-size:13px;margin:12px 0 0">Tiene movimientos registrados, por eso no se puede eliminar. Desactívalo para que no aparezca al registrar leche.</p>@endif
</section>
@endsection
