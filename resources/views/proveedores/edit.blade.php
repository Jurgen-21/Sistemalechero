@extends('layouts.app')

@section('titulo', 'Editar proveedor')

@section('contenido')
    <header class="cabecera">
        <div>
            <p class="sobre-titulo">{{ $proveedor->codigo }}</p>
            <h1>Editar proveedor</h1>
        </div>
    </header>

    <section class="panel formulario">
        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST" novalidate>
            @method('PUT')
            @include('proveedores._form')
        </form>
    </section>
@endsection
