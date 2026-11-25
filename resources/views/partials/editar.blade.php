{{-- Hoja de edición genérica. $campos = [[name, etiqueta, tipo, opciones|null, atributos], ...] --}}
<dialog class="sheet" id="editar" aria-labelledby="editar-t">
  <div class="grab"></div>
  <h2 id="editar-t" data-titulo>Editar</h2>
  <form method="POST" action="#">
    @csrf @method('PUT')
    <div class="form">
      @foreach ($campos as $c)
        @php [$n, $l, $t] = $c; $op = $c[3] ?? null; $at = $c[4] ?? ''; @endphp
        <label @class(['wide' => str_contains($at, 'wide')])>{{ $l }}
          @if ($t === 'select')
            <select class="in" name="{{ $n }}" required>@foreach ($op as $o)<option>{{ $o }}</option>@endforeach</select>
          @else
            <input class="in" name="{{ $n }}" type="{{ $t }}" {!! $t === 'number' ? 'step="any" inputmode="decimal"' : '' !!} {!! $t === 'date' ? 'min="2020-01-01"' . (str_contains($at, 'futuro') ? '' : ' max="' . now()->toDateString() . '"') : '' !!} {{ str_contains($at, 'opcional') ? '' : 'required' }}>
          @endif
        </label>
      @endforeach
    </div>
    <div class="acts"><button class="btn alt">Guardar cambios</button><button type="button" class="btn ghost" data-cerrar>Cancelar</button></div>
  </form>
</dialog>
