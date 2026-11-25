@extends('layouts.app')
@section('titulo', 'Gastos operativos')
@section('contenido')
@include('partials.movimientos', ['titulo' => 'Gastos operativos', 'subtitulo' =>  'Lo que cuesta mover el negocio', 'ruta' =>  'gastos', 'campo' =>  'categoria', 'etiquetaCampo' =>  'Categoría', 'campo2' =>  'descripcion', 'etiqueta2' =>  'Descripción', 'tipo2' =>  'text', 'color' =>  'var(--butter)'])
@endsection
