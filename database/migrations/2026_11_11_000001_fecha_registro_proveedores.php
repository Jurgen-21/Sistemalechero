<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('proveedores') && !Schema::hasColumn('proveedores', 'fecha_registro')) {
            Schema::table('proveedores', function (Blueprint $table) {
                $table->date('fecha_registro')->nullable()->after('telefono')->index();
            });
            \Illuminate\Support\Facades\DB::table('proveedores')->whereNull('fecha_registro')->update(['fecha_registro' => now()->toDateString()]);
        }
    }
    public function down(): void {
        if (Schema::hasTable('proveedores') && Schema::hasColumn('proveedores', 'fecha_registro')) {
            Schema::table('proveedores', fn (Blueprint $table) => $table->dropColumn('fecha_registro'));
        }
    }
};
