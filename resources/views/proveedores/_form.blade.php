{{-- Campos compartidos por create y edit. Espera $proveedor (nuevo o existente). --}}
@csrf

<div class="campo">
    <label for="nombre">Nombre completo</label>
    <input type="text" id="nombre" name="nombre" maxlength="120"
           value="{{ old('nombre', $proveedor->nombre) }}" required>
    @error('nombre') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="campo">
    <label for="zona">Zona</label>
    <input type="text" id="zona" name="zona" maxlength="80"
           value="{{ old('zona', $proveedor->zona) }}" required>
    @error('zona') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="campo">
    <label for="telefono">Teléfono (opcional)</label>
    <input type="text" id="telefono" name="telefono" inputmode="numeric" maxlength="9"
           value="{{ old('telefono', $proveedor->telefono) }}">
    @error('telefono') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="campo">
    <label for="estado">Estado</label>
    <select id="estado" name="estado" required>
        @foreach (['activo' => 'Activo', 'inactivo' => 'Inactivo'] as $valor => $texto)
            <option value="{{ $valor }}" @selected(old('estado', $proveedor->estado ?? 'activo') === $valor)>
                {{ $texto }}
            </option>
        @endforeach
    </select>
    @error('estado') <p class="error">{{ $message }}</p> @enderror
</div>

<div class="formulario-acciones">
    <button type="submit" class="boton boton-principal">Guardar</button>
    <a href="{{ route('proveedores.index') }}" class="boton boton-secundario">Cancelar</a>
</div>
