<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entregas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $t->date('fecha')->index();
            $t->decimal('litros', 10, 2);
            $t->decimal('precio_litro', 8, 2);   // precio vigente el día de la entrega
            $t->decimal('importe', 12, 2);       // litros x precio_litro
            $t->timestamps();
        });
        Schema::create('pagos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $t->date('fecha')->index();
            $t->decimal('monto', 12, 2);
            $t->string('nota')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('entregas');
    }
};
