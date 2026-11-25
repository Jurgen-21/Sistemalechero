<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->index();
            $table->string('producto', 20);                 // Queso | Yogur
            $table->decimal('litros_usados', 10, 2);         // leche que se gastó
            $table->decimal('cantidad', 10, 2);              // producto obtenido (kg de queso / litros de yogur)
            $table->string('nota', 160)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producciones');
    }
};
