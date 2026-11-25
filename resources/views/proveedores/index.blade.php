@extends('layouts.app')
@section('titulo', 'Proveedores')
@section('contenido')
@include('partials.cabecera', ['h' => 'Proveedores', 'p' => $total . ' registrados · ' . $activos . ' activos', 'chip' => $activos . ' activos'])

<section class="card">
  <div class="card-h">
    <span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="8" r="3.5"/><path d="M3 20c0-3.5 3-6 7-6M18 14v6M15 17h6"/></svg></span>
    <div><h2>Nuevo proveedor</h2><small>Al guardarlo aparece de inmediato en Entregas y Pagos</small></div>
  </div>
  @if ($errors->any() && old('nombre') !== null)
    <div class="alerta">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  @endif
  <form method="POST" action="{{ route('proveedores.store') }}" class="form">
    @csrf
    <label>Nombre completo<input class="in" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. María Quispe" required></label>
    <label>Zona<input class="in" name="zona" list="zonas" value="{{ old('zona') }}" placeholder="Ej. Zurite" required>
      <datalist id="zonas"><option value="Zurite"><option value="Cusco"><option value="Anta"><option value="Saylla"></datalist></label>
    <label>Teléfono (9 dígitos)<input class="in" name="telefono" value="{{ old('telefono') }}" inputmode="numeric" maxlength="9" placeholder="987654321"></label>
    <label>Precio por litro (S/)<input class="in" name="precio_litro" type="number" step="0.01" min="0" inputmode="decimal" value="{{ old('precio_litro') }}" placeholder="1.80" required></label>
    <label>Fecha de registro<input class="in" name="fecha_registro" type="date" max="{{ now()->toDateString() }}" min="2020-01-01" value="{{ old('fecha_registro', now()->toDateString()) }}"></label>
    <button class="btn alt">Guardar proveedor</button>
  </form>
</section>

<section class="card">
  <div class="card-h"><div><h2>Directorio</h2></div></div>
  <form class="tools" method="GET">
    <input class="in" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o zona" aria-label="Buscar">
    <div class="seg" role="group" aria-label="Estado">
      @foreach (['' => 'Todos', 'activo' => 'Activos', 'inactivo' => 'Inactivos'] as $v => $l)
        <a href="{{ request()->fullUrlWithQuery(['estado' => $v ?: null, 'page' => null]) }}" aria-current="{{ (string) request('estado') === (string) $v ? 'true' : 'false' }}">{{ $l }}</a>
      @endforeach
    </div>
    <button class="btn">Buscar</button>
  </form>
  @php $paleta = ['#F5C73D', '#8FD9BB', '#BFDCE6', '#F7C3CC', '#E6D79A']; @endphp
  <div class="list">
    @forelse ($proveedores as $p)
      <div class="prov">
        <span class="av" style="background:{{ $paleta[crc32($p->nombre) % count($paleta)] }}">{{ mb_strtoupper(mb_substr($p->nombre, 0, 1)) }}</span>
        <a class="pn" href="{{ route('proveedores.show', $p) }}"><strong>{{ $p->nombre }}</strong><small>{{ $p->zona ?: 'Sin zona' }}@if($p->telefono) · {{ $p->telefono }}@endif</small></a>
        <span class="hm"><small>Precio por litro</small><br><b class="num">S/ {{ number_format($p->precio_litro, 2) }}</b></span>
        <span class="hm"><small>Litros este mes</small><br><b class="num">{{ number_format($p->litros_mes ?? 0, 0) }} L</b></span>
        <span><small>Saldo</small><br><b class="num {{ $p->saldo > 0.5 ? 'neg' : '' }}">S/ {{ number_format(max($p->saldo, 0), 2) }}</b></span>
        <span class="acts">
          <span class="tag {{ $p->activo ? '' : 'off' }}">{{ $p->activo ? 'Activo' : 'Inactivo' }}</span>
          @if ($p->activo)<a class="mini y" href="{{ route('entregas.index', ['proveedor' => $p->id]) }}">+ Entrega</a>@endif
          @if ($p->saldo > 0.5)<a class="mini" href="{{ route('pagos.index', ['proveedor' => $p->id]) }}">Pagar</a>@endif
          <button type="button" class="linkx ed" data-edit="{{ json_encode(['action' => route('proveedores.update', $p), 'titulo' => 'Editar a ' . $p->nombre, 'vals' => ['nombre' => $p->nombre, 'zona' => $p->zona, 'telefono' => $p->telefono, 'precio_litro' => $p->precio_litro, 'fecha_registro' => optional($p->fecha_registro)->format('Y-m-d')]], JSON_UNESCAPED_UNICODE) }}">Editar</button>
          @if (auth()->user()->esAdmin())
            <form method="POST" action="{{ route('proveedores.destroy', $p) }}" data-confirmar="{{ $p->entregas_sum_importe || $p->pagos_sum_monto ? 'Este proveedor tiene entregas o pagos registrados: no se podrá eliminar. Desactívalo si ya no te entrega leche.' : '¿Eliminar a ' . $p->nombre . '?' }}" data-detalle="Se borrará su ficha. Esta acción no se puede deshacer.">@csrf @method('DELETE')<button class="linkx">Quitar</button></form>
          @endif
        </span>
      </div>
    @empty
      <div class="empty"><span class="blob"></span>
        @if (request('q') || request('estado'))
          <b>Ningún proveedor coincide con la búsqueda</b>Prueba con otro nombre o zona.
        @else
          <b>Aún no tienes proveedores</b>Llena el formulario de arriba y guarda el primero.
        @endif
      </div>
    @endforelse
  </div>
  {{ $proveedores->links('pagination::simple-default') }}
</section>
@include('partials.editar', ['campos' => [['nombre', 'Nombre completo', 'text', null, 'wide'], ['zona', 'Zona', 'text'], ['telefono', 'Teléfono (9 dígitos)', 'text', null, 'opcional'], ['precio_litro', 'Precio por litro (S/)', 'number'], ['fecha_registro', 'Fecha de registro', 'date', null, 'opcional']]])
@endsection
