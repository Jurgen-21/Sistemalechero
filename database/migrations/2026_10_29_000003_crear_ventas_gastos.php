<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $t) {
            $t->id();
            $t->date('fecha')->index();
            $t->string('producto');              // Leche, Queso, Yogur
            $t->decimal('cantidad', 10, 2)->nullable();
            $t->decimal('monto', 12, 2);
            $t->timestamps();
        });
        Schema::create('gastos', function (Blueprint $t) {
            $t->id();
            $t->date('fecha')->index();
            $t->string('categoria');             // Transporte, Envases, Servicios, Insumos
            $t->string('descripcion')->nullable();
            $t->decimal('monto', 12, 2);
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('ventas');
    }
};
