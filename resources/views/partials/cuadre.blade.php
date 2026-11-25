{{-- Cuadre del mes: mismas cifras que el Tablero. Espera $cuadre y $mes. --}}
<section class="card cuadre">
  <div class="card-h">
    <span class="ic y"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h16M4 17h10"/></svg></span>
    <div><h2>Cuadre de {{ ucfirst($mes->locale('es')->translatedFormat('F Y')) }}</h2><small>Las mismas cifras del Tablero, Entregas y Pagos</small></div>
  </div>
  <div class="eq">
    <div><small>Compra de leche (entregas)</small><b>S/ {{ number_format($cuadre['leche'], 2) }}</b><a href="{{ route('entregas.index', ['mes' => $mes->format('Y-m')]) }}">Ver entregas →</a></div>
    <span class="op">+</span>
    <div><small>Gastos operativos</small><b>S/ {{ number_format($cuadre['gastos'], 2) }}</b><a href="{{ route('gastos.index', ['mes' => $mes->format('Y-m')]) }}">Ver gastos →</a></div>
    <span class="op">=</span>
    <div class="tot"><small>Total egresos</small><b>S/ {{ number_format($cuadre['egresos'], 2) }}</b><span>Ventas: S/ {{ number_format($cuadre['ventas'], 2) }}</span></div>
  </div>
  <div class="stats">
    <div><small>Ya pagado a proveedores este mes</small><b>S/ {{ number_format($cuadre['pagado_mes'], 2) }}</b></div>
    <div><small>Aún por pagar a proveedores</small><b class="{{ $cuadre['por_pagar'] > 0.5 ? 'neg' : '' }}">S/ {{ number_format($cuadre['por_pagar'], 2) }}</b></div>
    <div><small>Dinero que ya salió (pagos + gastos)</small><b>S/ {{ number_format($cuadre['caja_salio'], 2) }}</b></div>
    <div><small>Ganancia del mes</small><b class="{{ $cuadre['ganancia'] < 0 ? 'neg' : 'pos' }}">S/ {{ number_format($cuadre['ganancia'], 2) }}</b></div>
  </div>
</section>
