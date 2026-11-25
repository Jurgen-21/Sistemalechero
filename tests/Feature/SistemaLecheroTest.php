<?php

namespace Tests\Feature;

use App\Models\{Entrega, Gasto, Pago, Produccion, Proveedor, User, Venta};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SistemaLecheroTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['rol' => 'admin']);
    }

    private function operador(): User
    {
        return User::factory()->create(['rol' => 'operador']);
    }

    private function proveedor(): Proveedor
    {
        return Proveedor::create(['nombre' => 'Juan Quispe', 'zona' => 'Zurite', 'precio_litro' => 2, 'activo' => true, 'fecha_registro' => now()]);
    }

    private function entrega(Proveedor $p, float $litros = 100): Entrega
    {
        return Entrega::create(['proveedor_id' => $p->id, 'fecha' => now(), 'litros' => $litros, 'precio_litro' => 2, 'importe' => $litros * 2]);
    }

    public function test_todas_las_pantallas_cargan_para_el_admin(): void
    {
        $this->actingAs($this->admin());
        $p = $this->proveedor();
        foreach (['/', '/proveedores', "/proveedores/{$p->id}", "/proveedores/{$p->id}/estado-cuenta", '/entregas', '/pagos', '/ventas', '/gastos', '/distribuciones', '/produccion', '/reportes', '/usuarios', '/respaldo', '/perfil'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_el_saldo_es_entregado_menos_pagado(): void
    {
        $p = $this->proveedor();
        $this->entrega($p, 100);                                   // 200
        Pago::create(['proveedor_id' => $p->id, 'fecha' => now(), 'monto' => 50]);
        $this->assertEqualsWithDelta(150, $p->fresh()->saldo, 0.001);
    }

    public function test_no_se_puede_pagar_mas_que_el_saldo(): void
    {
        $this->actingAs($this->operador());
        $p = $this->proveedor();
        $this->entrega($p, 100);
        $this->post('/pagos', ['proveedor_id' => $p->id, 'fecha' => now()->toDateString(), 'monto' => 250])->assertSessionHasErrors();
        $this->assertSame(0, Pago::count());
    }

    public function test_editar_entrega_recalcula_el_importe(): void
    {
        $this->actingAs($this->operador());
        $e = $this->entrega($this->proveedor(), 100);
        $this->put("/entregas/{$e->id}", ['fecha' => now()->toDateString(), 'litros' => 120, 'precio_litro' => 2.5])->assertSessionDoesntHaveErrors();
        $this->assertEqualsWithDelta(300, (float) $e->fresh()->importe, 0.001);
    }

    public function test_no_se_puede_reducir_una_entrega_ya_cubierta_por_pagos(): void
    {
        $this->actingAs($this->operador());
        $p = $this->proveedor();
        $e = $this->entrega($p, 100);                              // 200
        Pago::create(['proveedor_id' => $p->id, 'fecha' => now(), 'monto' => 200]);
        $this->put("/entregas/{$e->id}", ['fecha' => now()->toDateString(), 'litros' => 10, 'precio_litro' => 2])->assertSessionHasErrors();
        $this->assertEqualsWithDelta(200, (float) $e->fresh()->importe, 0.001);
    }

    public function test_editar_un_pago_no_puede_superar_el_saldo(): void
    {
        $this->actingAs($this->operador());
        $p = $this->proveedor();
        $this->entrega($p, 100);                                   // 200
        $pago = Pago::create(['proveedor_id' => $p->id, 'fecha' => now(), 'monto' => 50]);
        $this->put("/pagos/{$pago->id}", ['fecha' => now()->toDateString(), 'monto' => 300])->assertSessionHasErrors();
        $this->put("/pagos/{$pago->id}", ['fecha' => now()->toDateString(), 'monto' => 200])->assertSessionDoesntHaveErrors();
        $this->assertEqualsWithDelta(200, (float) $pago->fresh()->monto, 0.001);
    }

    public function test_ventas_y_gastos_se_pueden_editar(): void
    {
        $this->actingAs($this->operador());
        $v = Venta::create(['fecha' => now(), 'producto' => 'Queso', 'cantidad' => 5, 'monto' => 100]);
        $g = Gasto::create(['fecha' => now(), 'categoria' => 'Envases', 'monto' => 20]);
        $this->put("/ventas/{$v->id}", ['fecha' => now()->toDateString(), 'producto' => 'Yogur', 'cantidad' => 8, 'monto' => 90]);
        $this->put("/gastos/{$g->id}", ['fecha' => now()->toDateString(), 'categoria' => 'Insumos', 'monto' => 35, 'descripcion' => 'Sal']);
        $this->assertSame('Yogur', $v->fresh()->producto);
        $this->assertSame('Insumos', $g->fresh()->categoria);
    }

    public function test_el_operador_no_puede_borrar_pero_el_admin_si(): void
    {
        $v = Venta::create(['fecha' => now(), 'producto' => 'Leche', 'cantidad' => 1, 'monto' => 10]);
        $this->actingAs($this->operador())->delete("/ventas/{$v->id}")->assertSessionHasErrors();
        $this->assertSame(1, Venta::count());
        $this->actingAs($this->admin())->delete("/ventas/{$v->id}");
        $this->assertSame(0, Venta::count());
    }

    public function test_el_operador_no_entra_a_usuarios_ni_respaldo(): void
    {
        $this->actingAs($this->operador());
        $this->get('/usuarios')->assertRedirect();
        $this->get('/respaldo')->assertRedirect();
        $this->get('/respaldo/descargar')->assertRedirect();
    }

    public function test_el_inventario_calcula_existencias(): void
    {
        $p = $this->proveedor();
        $this->entrega($p, 1000);                                  // +1000 L de leche
        Produccion::create(['fecha' => now(), 'producto' => 'Queso', 'litros_usados' => 300, 'cantidad' => 30]);
        Venta::create(['fecha' => now(), 'producto' => 'Queso', 'cantidad' => 10, 'monto' => 200]);
        Venta::create(['fecha' => now(), 'producto' => 'Leche', 'cantidad' => 50, 'monto' => 100]);
        $s = Produccion::stock();
        $this->assertEqualsWithDelta(650, $s['Leche']['stock'], 0.001);   // 1000 - 300 - 50
        $this->assertEqualsWithDelta(20, $s['Queso']['stock'], 0.001);    // 30 - 10
    }

    public function test_el_csv_se_descarga_y_neutraliza_formulas(): void
    {
        $this->actingAs($this->admin());
        Gasto::create(['fecha' => now(), 'categoria' => 'Insumos', 'descripcion' => '=1+1', 'monto' => 10]);
        $r = $this->get('/reportes/exportar/gastos?mes=' . now()->format('Y-m'));
        $r->assertOk();
        $csv = $r->streamedContent();
        $this->assertStringContainsString("'=1+1", $csv);
        $this->assertStringContainsString('Categoría', $csv);
    }

    public function test_el_respaldo_trae_los_datos(): void
    {
        $this->actingAs($this->admin());
        $this->proveedor();
        $sql = $this->get('/respaldo/descargar')->streamedContent();
        $this->assertStringContainsString('INSERT INTO `proveedores`', $sql);
        $this->assertStringContainsString('Juan Quispe', $sql);
    }

    public function test_no_se_puede_quitar_el_ultimo_admin_ni_borrarse_a_si_mismo(): void
    {
        $a = $this->admin();
        $this->actingAs($a);
        $this->delete("/usuarios/{$a->id}")->assertSessionHasErrors();
        $this->put("/usuarios/{$a->id}", ['name' => $a->name, 'email' => $a->email, 'rol' => 'operador'])->assertSessionHasErrors();
        $this->assertSame('admin', $a->fresh()->rol);
    }

    public function test_el_admin_crea_usuarios_con_rol(): void
    {
        $this->actingAs($this->admin());
        $this->post('/usuarios', ['name' => 'Ana Torres', 'email' => 'ana@lacteos.local', 'rol' => 'operador', 'password' => 'Clave12345'])->assertSessionDoesntHaveErrors();
        $this->assertSame('operador', User::where('email', 'ana@lacteos.local')->value('rol'));
    }

    public function test_el_perfil_cambia_la_contrasena_solo_con_la_actual(): void
    {
        $u = User::factory()->create(['password' => 'ClaveVieja1']);
        $this->actingAs($u);
        $this->put('/perfil', ['name' => $u->name, 'password_actual' => 'mala', 'password' => 'ClaveNueva1', 'password_confirmation' => 'ClaveNueva1'])->assertSessionHasErrors();
        $this->put('/perfil', ['name' => $u->name, 'password_actual' => 'ClaveVieja1', 'password' => 'ClaveNueva1', 'password_confirmation' => 'ClaveNueva1'])->assertSessionDoesntHaveErrors();
        $this->assertTrue(\Hash::check('ClaveNueva1', $u->fresh()->password));
    }

    public function test_con_perdida_el_tablero_no_pasa_de_100_por_ciento(): void
    {
        $this->actingAs($this->admin());
        Venta::create(['fecha' => now(), 'producto' => 'Leche', 'cantidad' => 1, 'monto' => 100]);
        $this->entrega($this->proveedor(), 500);                   // costo 1000 > ventas 100
        $r = $this->get('/')->assertOk()->assertSee('Pérdida de');
        preg_match_all('/(\d+(?:\.\d+)?)%/', $r->getContent(), $m);
        foreach ($m[1] as $n) {
            $this->assertLessThanOrEqual(100, (float) $n);
        }
    }

    public function test_no_se_aceptan_fechas_futuras_ni_antiguas_en_movimientos(): void
    {
        $this->actingAs($this->operador());
        $p = $this->proveedor();
        $d = ['proveedor_id' => $p->id, 'litros' => 10, 'precio_litro' => 2];
        $this->post('/entregas', $d + ['fecha' => now()->addDay()->toDateString()])->assertSessionHasErrors('fecha');
        $this->post('/entregas', $d + ['fecha' => '2019-12-31'])->assertSessionHasErrors('fecha');
        $this->post('/entregas', $d + ['fecha' => now()->subDay()->toDateString()])->assertSessionDoesntHaveErrors();
        $this->assertSame(1, Entrega::count());
        $this->post('/gastos', ['fecha' => now()->addDays(3)->toDateString(), 'categoria' => 'Insumos', 'monto' => 10])->assertSessionHasErrors('fecha');
    }

    public function test_distribucion_valida_fecha_segun_el_estado(): void
    {
        $this->actingAs($this->operador());
        $base = ['zona' => 'Cusco', 'destinatario' => 'Tienda', 'producto' => 'Queso', 'cantidad' => 5, 'unidad' => 'kg', 'monto' => 100];
        $this->post('/distribuciones', $base + ['estado' => 'Pendiente', 'fecha' => now()->subDay()->toDateString()])->assertSessionHasErrors('fecha');
        $this->post('/distribuciones', $base + ['estado' => 'Entregado', 'fecha' => now()->addDay()->toDateString()])->assertSessionHasErrors('fecha');
        $this->post('/distribuciones', $base + ['estado' => 'Pendiente', 'fecha' => now()->addDay()->toDateString()])->assertSessionDoesntHaveErrors();
        $this->post('/distribuciones', $base + ['estado' => 'Entregado', 'fecha' => now()->subDay()->toDateString()])->assertSessionDoesntHaveErrors();
        $this->assertSame(2, \App\Models\Distribucion::count());
    }

    public function test_se_puede_editar_el_proveedor_sin_perder_su_fecha_de_registro(): void
    {
        $this->actingAs($this->operador());
        $p = $this->proveedor();
        $antes = $p->fecha_registro->toDateString();
        $this->put("/proveedores/{$p->id}", ['nombre' => 'Juan Quispe M.', 'zona' => 'Anta', 'telefono' => '987654321', 'precio_litro' => 2.2, 'fecha_registro' => ''])->assertSessionDoesntHaveErrors();
        $p = $p->fresh();
        $this->assertSame('Anta', $p->zona);
        $this->assertEqualsWithDelta(2.2, (float) $p->precio_litro, 0.001);
        $this->assertSame($antes, $p->fecha_registro->toDateString());
        $this->put("/proveedores/{$p->id}", ['nombre' => 'Juan Quispe M.', 'zona' => 'Anta', 'precio_litro' => 2, 'fecha_registro' => now()->addDay()->toDateString()])->assertSessionHasErrors('fecha_registro');
    }

    public function test_el_listado_de_proveedores_ofrece_editar_y_avisa_al_eliminar(): void
    {
        $this->actingAs($this->admin());
        $this->proveedor();
        $this->get('/proveedores')->assertOk()->assertSee('data-edit', false)->assertSee('data-confirmar', false);
    }
}
