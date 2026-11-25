{{-- Botones Editar / Quitar de una fila. $url_editar, $vals, $url_borrar, $confirmar --}}
<span class="acts">
  <button type="button" class="linkx ed" data-edit="{{ json_encode(['action' => $url_editar, 'vals' => $vals, 'titulo' => $titulo ?? 'Editar'], JSON_UNESCAPED_UNICODE) }}">Editar</button>
  @if (auth()->user()->esAdmin())
    <form method="POST" action="{{ $url_borrar }}" data-confirmar="{{ $confirmar ?? '¿Eliminar este registro?' }}" data-detalle="{{ $detalle ?? 'Esta acción no se puede deshacer.' }}">@csrf @method('DELETE')<button class="linkx">Quitar</button></form>
  @endif
</span>
