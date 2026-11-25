<?php
namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    private function reglas(): array
    {
        return [
            'nombre'         => 'required|string|min:3|max:120',
            'zona'           => 'required|string|max:80',
            'telefono'       => 'nullable|digits:9',
            'fecha_registro' => 'nullable|date|before_or_equal:today|after_or_equal:2020-01-01',
            'precio_litro'   => 'required|numeric|min:0',
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.required'       => 'Escribe el nombre del proveedor.',
            'nombre.min'            => 'El nombre debe tener al menos 3 caracteres.',
            'zona.required'         => 'Indica la zona del proveedor.',
            'telefono.digits'       => 'El teléfono debe tener 9 dígitos.',
            'fecha_registro.date'   => 'La fecha de registro no es válida.',
            'fecha_registro.before_or_equal' => 'La fecha de registro no puede ser futura.',
            'fecha_registro.after_or_equal'  => 'La fecha de registro es demasiado antigua (desde 01/01/2020).',
            'precio_litro.required' => 'Indica el precio por litro.',
            'precio_litro.numeric'  => 'El precio por litro debe ser un número.',
        ];
    }

    public function index(Request $r)
    {
        $ini = now()->startOfMonth();
        $fin = now()->endOfMonth();
        $proveedores = Proveedor::buscar($r->q)->estado($r->estado)
            ->withSum('entregas', 'importe')->withSum('pagos', 'monto')
            ->withSum(['entregas as litros_mes' => fn ($q) => $q->whereBetween('fecha', [$ini, $fin])], 'litros')
            ->orderBy('nombre')->paginate(15)->withQueryString();
        $total = Proveedor::count();
        $activos = Proveedor::where('activo', true)->count();
        return view('proveedores.index', compact('proveedores', 'total', 'activos'));
    }

    public function store(Request $r)
    {
        $d = $r->validate($this->reglas(), $this->mensajes());
        $d['fecha_registro'] = $d['fecha_registro'] ?? now()->toDateString();
        $p = Proveedor::create($d + ['activo' => true]);
        return redirect()->route('proveedores.index')
            ->with('ok', "Proveedor «{$p->nombre}» guardado. Ya aparece en Entregas.");
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->loadSum('entregas', 'importe')->loadSum('pagos', 'monto')->loadSum('entregas as litros_total', 'litros');
        $entregas = $proveedor->entregas()->latest('fecha')->latest('id')->limit(10)->get();
        $pagos = $proveedor->pagos()->latest('fecha')->latest('id')->limit(10)->get();
        return view('proveedores.show', compact('proveedor', 'entregas', 'pagos'));
    }

    public function update(Request $r, Proveedor $proveedor)
    {
        $d = $r->validate($this->reglas(), $this->mensajes());
        if (empty($d['fecha_registro'])) {
            unset($d['fecha_registro']); // no borrar la fecha existente si el campo llega vacío
        }
        $proveedor->update($d);
        return back()->with('ok', "Datos de «{$proveedor->nombre}» actualizados. Las entregas anteriores conservan su precio.");
    }

    public function estado(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => !$proveedor->activo]);
        return back()->with('ok', $proveedor->activo ? 'Proveedor activado' : 'Proveedor desactivado');
    }

    public function destroy(Proveedor $proveedor)
    {
        if ($proveedor->entregas()->exists() || $proveedor->pagos()->exists()) {
            return back()->withErrors('Tiene entregas o pagos registrados. Desactívalo en lugar de eliminarlo.');
        }
        $proveedor->delete();
        return redirect()->route('proveedores.index')->with('ok', 'Proveedor eliminado');
    }
}
