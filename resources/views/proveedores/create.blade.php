@extends('layouts.app')

@section('titulo', 'Nuevo proveedor')

@section('contenido')
    <header class="cabecera">
        <div>
            <p class="sobre-titulo">DIRECTORIO</p>
            <h1>Nuevo proveedor</h1>
        </div>
    </header>

    <section class="panel formulario">
        <form action="{{ route('proveedores.store') }}" method="POST" novalidate>
            @include('proveedores._form', ['proveedor' => new \App\Models\Proveedor])
        </form>
    </section>
@endsection
