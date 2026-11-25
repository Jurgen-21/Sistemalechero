<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#E6EFEB" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#07120F" media="(prefers-color-scheme: dark)">
<title>@yield('titulo') · Gestión Láctea</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
<script>try{var g=localStorage.getItem('glass');if(g!==null)document.documentElement.style.setProperty('--solid',g)}catch(e){}</script>
</head>
<body>
@php
$u = auth()->user();
$i = fn ($d) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $d . '</svg>';
$menu = [
 ['dashboard', 'dashboard', 'Tablero', '<path d="M4 13h6V4H4zM14 20h6v-9h-6zM4 20h6v-3H4zM14 8h6V4h-6z"/>', true],
 ['proveedores.index', 'proveedores.*', 'Proveedores', '<circle cx="9" cy="8" r="3.5"/><path d="M3 20c0-3.5 2.7-6 6-6s6 2.5 6 6M16 5a3.5 3.5 0 0 1 0 7M18 14c2 .6 3 2.5 3 6"/>', true],
 ['entregas.index', 'entregas.*', 'Entregas', '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7M7 19a1.5 1.5 0 1 0 0 .1M17 19a1.5 1.5 0 1 0 0 .1"/>', true],
 ['pagos.index', 'pagos.*', 'Pagos', '<rect x="3" y="6" width="18" height="12" rx="3"/><circle cx="12" cy="12" r="2.5"/>', true],
 ['ventas.index', 'ventas.*', 'Ventas', '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>', false],
 ['produccion.index', 'produccion.*', 'Inventario', '<path d="M21 8l-9-5-9 5 9 5zM3 8v8l9 5 9-5V8M12 13v8"/>', false],
 ['distribuciones.index', 'distribuciones.*', 'Distribuciones', '<path d="M3 7h18M5 7l1 13h12l1-13M9 7V4h6v3M9 11v5M15 11v5"/>', false],
 ['gastos.index', 'gastos.*', 'Gastos', '<path d="M12 3v18M16 7c-1-1.5-2.4-2-4-2-2.5 0-4 1.2-4 3s1.5 2.6 4 3 4 1.2 4 3-1.5 3-4 3c-1.8 0-3.2-.6-4.2-2"/>', false],
 ['reportes.index', 'reportes.*', 'Reportes', '<path d="M7 3h8l4 4v14H7zM15 3v4h4M10 12h6M10 16h6"/>', false],
];
if ($u?->esAdmin()) {
  $menu[] = ['usuarios.index', 'usuarios.*', 'Usuarios', '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 3-6 7-6s7 2 7 6"/>', false];
  $menu[] = ['respaldo.index', 'respaldo.*', 'Respaldo', '<ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>', false];
}
$ini = mb_strtoupper(mb_substr($u->name ?? 'A', 0, 1));
@endphp

<div class="bar-top" id="bartop" aria-hidden="true">@yield('titulo')</div>

<div class="app">
<nav class="side" aria-label="Secciones">
  <div class="logo"><span class="drop"></span>GESTIÓN LÁCTEA</div>
  @foreach ($menu as [$ruta, $patron, $etiqueta, $icono])
    @if ($ruta === 'reportes.index' || $ruta === 'usuarios.index')<div class="sep"></div>@endif
    <a href="{{ route($ruta) }}" aria-current="{{ request()->routeIs($patron) ? 'true' : 'false' }}">{!! $i($icono) !!}{{ $etiqueta }}</a>
  @endforeach
  <div class="who">
    <label class="glassctl"><span>Transparencia <em style="font-style:normal" data-glass-label>Cristal</em></span><input type="range" min="0" max="100" value="25" data-glass aria-label="Transparencia del cristal"></label>
    <div class="me"><span class="av" style="background:var(--butter)">{{ $ini }}</span><div><b>{{ $u->name }}</b><small>{{ $u->rol_etiqueta }}</small></div></div>
    <a class="mini" href="{{ route('perfil.edit') }}">Mi perfil</a>
    <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf<button class="mini" type="submit">Salir</button></form>
  </div>
</nav>

<main>@yield('contenido')</main>
</div>

{{-- Barra flotante (móvil) --}}
<nav class="tabbar" aria-label="Navegación principal">
  @foreach (array_filter($menu, fn ($m) => $m[4]) as [$ruta, $patron, $etiqueta, $icono])
    <a href="{{ route($ruta) }}" aria-current="{{ request()->routeIs($patron) ? 'true' : 'false' }}">{!! $i($icono) !!}<span>{{ $etiqueta }}</span></a>
  @endforeach
  <button type="button" data-abrir="mas" aria-current="{{ collect($menu)->filter(fn ($m) => ! $m[4])->contains(fn ($m) => request()->routeIs($m[1])) ? 'true' : 'false' }}">{!! $i('<circle cx="5" cy="12" r="1.4"/><circle cx="12" cy="12" r="1.4"/><circle cx="19" cy="12" r="1.4"/>') !!}<span>Más</span></button>
</nav>

<dialog class="sheet" id="mas" aria-label="Más secciones">
  <div class="grab"></div>
  <nav>
    @foreach ($menu as [$ruta, $patron, $etiqueta, $icono, $prim])
      <a href="{{ route($ruta) }}" aria-current="{{ request()->routeIs($patron) ? 'true' : 'false' }}">{!! $i($icono) !!}{{ $etiqueta }}</a>
    @endforeach
  </nav>
  <label class="glassctl"><span>Transparencia <em style="font-style:normal" data-glass-label>Cristal</em></span><input type="range" min="0" max="100" value="25" data-glass aria-label="Transparencia del cristal"></label>
  <div class="me" style="display:flex;gap:10px;align-items:center"><span class="av" style="background:var(--butter)">{{ $ini }}</span><div><b>{{ $u->name }}</b><br><small>{{ $u->rol_etiqueta }}</small></div></div>
  <div class="acts" style="justify-content:flex-start">
    <a class="mini" href="{{ route('perfil.edit') }}">Mi perfil</a>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="mini" type="submit">Cerrar sesión</button></form>
    <button class="mini" type="button" data-cerrar>Cerrar</button>
  </div>
</dialog>

<dialog class="sheet" id="confirmar" aria-labelledby="confirmar-t">
  <div class="grab"></div>
  <h2 id="confirmar-t" data-c-titulo>¿Eliminar?</h2>
  <p class="aviso-borrar"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4M12 17.5v.01"/></svg><span data-c-detalle>Esta acción no se puede deshacer.</span></p>
  <div class="acts"><button type="button" class="btn danger" data-c-ok>Sí, eliminar</button><button type="button" class="btn ghost" data-c-no autofocus>Cancelar</button></div>
</dialog>

@if (session('ok') || $errors->any())
  <div class="toast {{ $errors->any() ? 'err' : '' }}" id="toast" role="status">{{ $errors->any() ? $errors->first() : session('ok') }}</div>
@endif

<script>
(() => {
  // Hojas (diálogos) y editor genérico
  const abrir = d => d.showModal ? d.showModal() : d.setAttribute('open', '');
  document.addEventListener('click', e => {
    const a = e.target.closest('[data-abrir]');
    if (a) { abrir(document.getElementById(a.dataset.abrir)); return; }
    if (e.target.closest('[data-cerrar]')) { e.target.closest('dialog').close(); return; }
    const ed = e.target.closest('[data-edit]');
    if (ed) {
      const d = JSON.parse(ed.dataset.edit), dlg = document.getElementById('editar'), f = dlg.querySelector('form');
      f.action = d.action; dlg.querySelector('[data-titulo]').textContent = d.titulo || 'Editar';
      Object.entries(d.vals).forEach(([k, v]) => { const c = f.elements[k]; if (c) c.value = v ?? ''; });
      abrir(dlg); return;
    }
    if (e.target.tagName === 'DIALOG') e.target.close();           // clic fuera = cerrar
  });
  // Advertencia antes de eliminar: cualquier <form data-confirmar="..." data-detalle="...">
  const cd = document.getElementById('confirmar');
  let pendiente = null;
  document.addEventListener('submit', e => {
    const f = e.target.closest('form[data-confirmar]');
    if (!f || f.dataset.ok === '1') return;
    e.preventDefault();
    if (!cd.showModal) { if (confirm(f.dataset.confirmar + ' ' + (f.dataset.detalle || ''))) { f.dataset.ok = '1'; f.submit(); } return; }
    pendiente = f;
    cd.querySelector('[data-c-titulo]').textContent = f.dataset.confirmar;
    cd.querySelector('[data-c-detalle]').textContent = f.dataset.detalle || 'Esta acción no se puede deshacer.';
    cd.showModal();
  });
  cd.querySelector('[data-c-ok]').addEventListener('click', () => { const f = pendiente; pendiente = null; cd.close(); if (f) { f.dataset.ok = '1'; f.submit(); } });
  cd.querySelector('[data-c-no]').addEventListener('click', () => { pendiente = null; cd.close(); });
  cd.addEventListener('close', () => { pendiente = null; });
  // Aviso que se retira solo
  setTimeout(() => document.getElementById('toast')?.remove(), 4200);
  // Barra superior que aparece al desplazar (iOS 27)
  const bt = document.getElementById('bartop');
  const f = () => bt.classList.toggle('on', scrollY > 70);
  addEventListener('scroll', f, {passive: true}); f();
  // Control de transparencia: Cristal ↔ Sólido
  const rs = document.querySelectorAll('[data-glass]'), ls = document.querySelectorAll('[data-glass-label]');
  const set = v => {
    const s = v / 100; document.documentElement.style.setProperty('--solid', s);
    rs.forEach(r => r.value = v);
    ls.forEach(l => l.textContent = v < 35 ? 'Cristal' : v < 70 ? 'Equilibrado' : 'Sólido');
  };
  let ini = 25; try { const g = localStorage.getItem('glass'); if (g !== null) ini = Math.round(g * 100); } catch (e) {}
  set(ini);
  rs.forEach(r => r.addEventListener('input', () => { set(+r.value); try { localStorage.setItem('glass', r.value / 100); } catch (e) {} }));
})();
</script>
</body>
</html>
