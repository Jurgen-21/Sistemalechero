<?php

use App\Http\Controllers\{AuthController, DashboardController, DistribucionController, EntregaController, GastoController, InventarioController, PagoController, PerfilController, ProveedorController, ReporteController, RespaldoController, UsuarioController, VentaController};
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');

    // Proveedores
    Route::resource('proveedores', ProveedorController::class)->parameters(['proveedores' => 'proveedor'])->only(['index', 'store', 'show', 'update']);
    Route::patch('proveedores/{proveedor}/estado', [ProveedorController::class, 'estado'])->name('proveedores.estado');

    // Movimientos: se pueden registrar y editar (admin y operador)
    Route::resource('entregas', EntregaController::class)->only(['index', 'store', 'update']);
    Route::resource('pagos', PagoController::class)->only(['index', 'store', 'update']);
    Route::resource('ventas', VentaController::class)->only(['index', 'store', 'update']);
    Route::resource('gastos', GastoController::class)->only(['index', 'store', 'update']);
    Route::resource('distribuciones', DistribucionController::class)->parameters(['distribuciones' => 'distribucion'])->only(['index', 'store', 'update']);
    Route::resource('produccion', InventarioController::class)->parameters(['produccion' => 'produccion'])->only(['index', 'store', 'update']);

    // Reportes
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/exportar/{tipo}', [ReporteController::class, 'exportar'])->name('reportes.exportar');
    Route::get('proveedores/{proveedor}/estado-cuenta', [ReporteController::class, 'estadoCuenta'])->name('reportes.cuenta');

    // Solo administrador: borrar registros, usuarios y respaldo
    Route::middleware('admin')->group(function () {
        Route::delete('proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
        Route::delete('entregas/{entrega}', [EntregaController::class, 'destroy'])->name('entregas.destroy');
        Route::delete('pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');
        Route::delete('ventas/{venta}', [VentaController::class, 'destroy'])->name('ventas.destroy');
        Route::delete('gastos/{gasto}', [GastoController::class, 'destroy'])->name('gastos.destroy');
        Route::delete('distribuciones/{distribucion}', [DistribucionController::class, 'destroy'])->name('distribuciones.destroy');
        Route::delete('produccion/{produccion}', [InventarioController::class, 'destroy'])->name('produccion.destroy');

        Route::resource('usuarios', UsuarioController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('respaldo', [RespaldoController::class, 'index'])->name('respaldo.index');
        Route::get('respaldo/descargar', [RespaldoController::class, 'descargar'])->name('respaldo.descargar');
    });
});
