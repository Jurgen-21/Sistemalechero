<?php
namespace App\Http\Controllers;

use App\Models\Produccion;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    private function reglas(): array
    {
        return [
            'fecha' => $this->reglaFecha(),
            'producto' => 'required|in:' . implode(',', array_keys(Produccion::PRODUCTOS)),
            'litros_usados' => 'required|numeric|min:0.1',
            'cantidad' => 'required|numeric|min:0.01',
            'nota' => 'nullable|string|max:160',
        ];
    }

    public function index(Request $r)
    {
        $mes = $this->mes($r);
        $q = Produccion::whereBetween('fecha', [$mes, $mes->copy()->endOfMonth()]);
        $porProducto = (clone $q)->selectRaw('producto, SUM(litros_usados) as litros, SUM(cantidad) as cantidad')->groupBy('producto')->get()->keyBy('producto');
        $registros = $q->latest('fecha')->latest('id')->paginate(12)->withQueryString();
        $stock = Produccion::stock();
        $productos = Produccion::PRODUCTOS;
        return view('inventario.index', compact('mes', 'registros', 'porProducto', 'stock', 'productos'));
    }

    public function store(Request $r)
    {
        $p = Produccion::create($r->validate($this->reglas(), $this->mensajesFecha()));
        return back()->with('ok', "Producción registrada: {$p->cantidad} " . Produccion::PRODUCTOS[$p->producto] . " de {$p->producto}");
    }

    public function update(Request $r, Produccion $produccion)
    {
        $produccion->update($r->validate($this->reglas(), $this->mensajesFecha()));
        return back()->with('ok', 'Producción actualizada');
    }

    public function destroy(Produccion $produccion)
    {
        $produccion->delete();
        return back()->with('ok', 'Registro de producción eliminado');
    }
}
