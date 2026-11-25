@extends('layouts.app')
@section('titulo', 'Ventas')
@section('contenido')
@include('partials.movimientos', ['titulo' => 'Ventas', 'subtitulo' => 'Leche, queso y yogur del mes', 'ruta' => 'ventas', 'campo' => 'producto', 'etiquetaCampo' => 'Producto', 'campo2' => 'cantidad', 'etiqueta2' => 'Cantidad', 'tipo2' => 'number', 'color' => 'var(--green)'])
@endsection
