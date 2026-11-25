<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RespaldoController extends Controller
{
    private const TABLAS = ['users', 'proveedores', 'entregas', 'pagos', 'ventas', 'gastos', 'distribuciones', 'producciones'];

    public function index()
    {
        $tablas = collect(self::TABLAS)->filter(fn ($t) => Schema::hasTable($t))->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()]);
        return view('respaldo.index', compact('tablas'));
    }

    /** Copia de los datos en un archivo .sql (sentencias INSERT) que se puede importar desde phpMyAdmin. */
    public function descargar()
    {
        $nombre = 'respaldo-sistema-lechero-' . now()->format('Y-m-d_His') . '.sql';
        return response()->streamDownload(function () {
            $pdo = DB::getPdo();
            echo "-- Respaldo de datos · Sistema Lechero · " . now()->toDateTimeString() . "\n";
            echo "-- Importar en una base con las tablas ya creadas (php artisan migrate).\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";
            foreach (self::TABLAS as $t) {
                if (! Schema::hasTable($t)) {
                    continue;
                }
                echo "-- {$t}\n";
                foreach (DB::table($t)->orderBy('id')->cursor() as $fila) {
                    $fila = (array) $fila;
                    $cols = implode(',', array_map(fn ($c) => "`$c`", array_keys($fila)));
                    $vals = implode(',', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $fila));
                    echo "INSERT INTO `{$t}` ({$cols}) VALUES ({$vals});\n";
                }
                echo "\n";
            }
            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $nombre, ['Content-Type' => 'application/sql; charset=UTF-8']);
    }
}
