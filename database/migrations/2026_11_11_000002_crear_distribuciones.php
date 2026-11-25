<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('distribuciones', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->index();
            $table->enum('zona', ['Zurite', 'Cusco'])->index();
            $table->string('destinatario', 120);
            $table->string('producto', 80);
            $table->decimal('cantidad', 10, 2);
            $table->string('unidad', 20)->default('unidades');
            $table->decimal('monto', 12, 2)->default(0);
            $table->enum('estado', ['Pendiente', 'En ruta', 'Entregado', 'Cancelado'])->default('Pendiente')->index();
            $table->string('observacion', 255)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('distribuciones'); }
};
