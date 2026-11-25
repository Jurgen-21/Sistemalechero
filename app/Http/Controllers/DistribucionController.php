<?php
namespace App\Http\Controllers;
use App\Models\Distribucion;
use Illuminate\Http\Request;
use Illuminate\Validation\{Rule, ValidationException};
class DistribucionController extends Controller {
    public function index(Request $request) {
        $query = Distribucion::query();
        if ($request->filled('desde')) $query->whereDate('fecha', '>=', $request->date('desde'));
        if ($request->filled('hasta')) $query->whereDate('fecha', '<=', $request->date('hasta'));
        if ($request->filled('zona')) $query->where('zona', $request->input('zona'));
        if ($request->filled('estado')) $query->where('estado', $request->input('estado'));
        $distribuciones = (clone $query)->latest('fecha')->latest('id')->paginate(15)->withQueryString();
        $resumen = [
            'total' => (clone $query)->count(),
            'monto' => (clone $query)->sum('monto'),
            'pendientes' => (clone $query)->whereIn('estado', ['Pendiente','En ruta'])->count(),
            'entregadas' => (clone $query)->where('estado', 'Entregado')->count(),
        ];
        return view('distribuciones.index', compact('distribuciones','resumen'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'fecha'=>'required|date',
            'zona'=>['required', Rule::in(['Zurite','Cusco'])],
            'destinatario'=>'required|string|max:120',
            'producto'=>'required|string|max:80',
            'cantidad'=>'required|numeric|min:0.01',
            'unidad'=>'required|in:litros,kg,unidades,cajas',
            'monto'=>'required|numeric|min:0',
            'estado'=>['required', Rule::in(['Pendiente','En ruta','Entregado','Cancelado'])],
            'observacion'=>'nullable|string|max:255',
        ]);
        $this->validarFecha($data);
        Distribucion::create($data);
        return back()->with('ok','Distribución registrada correctamente.');
    }
    public function update(Request $request, Distribucion $distribucion) {
        $data = $request->validate([
            'fecha'=>'required|date',
            'zona'=>['required', Rule::in(['Zurite','Cusco'])],
            'destinatario'=>'required|string|max:120',
            'producto'=>'required|string|max:80',
            'cantidad'=>'required|numeric|min:0.01',
            'unidad'=>'required|in:litros,kg,unidades,cajas',
            'monto'=>'required|numeric|min:0',
            'estado'=>['required', Rule::in(['Pendiente','En ruta','Entregado','Cancelado'])],
            'observacion'=>'nullable|string|max:255',
        ]);
        $this->validarFecha($data, $distribucion->fecha?->toDateString());
        $distribucion->update($data);
        return back()->with('ok','Distribución actualizada correctamente.');
    }
    /**
     * Pendiente / En ruta: no admiten fecha pasada (se mantiene la fecha ya guardada al editar).
     * Entregado: no admite fecha futura. Ninguna puede ser anterior a 2020.
     */
    private function validarFecha(array $data, ?string $fechaActual = null): void
    {
        $f = \Illuminate\Support\Carbon::parse($data['fecha'])->toDateString();
        $hoy = now()->toDateString();
        $error = null;
        if ($f < '2020-01-01') {
            $error = 'La fecha es demasiado antigua: usa una fecha desde el 01/01/2020.';
        } elseif (in_array($data['estado'], ['Pendiente', 'En ruta'], true) && $f < $hoy && $f !== $fechaActual) {
            $error = 'Una distribución «' . $data['estado'] . '» no puede tener una fecha pasada. Usa hoy o una fecha futura.';
        } elseif ($data['estado'] === 'Entregado' && $f > $hoy) {
            $error = 'Una distribución «Entregado» no puede tener fecha futura: ya debió realizarse.';
        }
        if ($error) {
            throw ValidationException::withMessages(['fecha' => $error]);
        }
    }

    public function destroy(Distribucion $distribucion) {
        $distribucion->delete();
        return back()->with('ok','Registro de distribución eliminado.');
    }
}
