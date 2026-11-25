<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('proveedores')) {
            Schema::create('proveedores', function (Blueprint $t) {
                $t->id();
                $t->string('nombre');
                $t->string('zona')->nullable();
                $t->string('telefono')->nullable();
                $t->timestamps();
            });
        }
        Schema::table('proveedores', function (Blueprint $t) {
            if (!Schema::hasColumn('proveedores', 'precio_litro')) $t->decimal('precio_litro', 8, 2)->default(0);
            if (!Schema::hasColumn('proveedores', 'activo')) $t->boolean('activo')->default(true);
        });
    }
    public function down(): void
    {
        Schema::table('proveedores', fn (Blueprint $t) => $t->dropColumn(['precio_litro', 'activo']));
    }
};
