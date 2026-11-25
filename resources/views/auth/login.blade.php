<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title>Acceso · Gestión Láctea</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
<style>body{min-height:100vh;min-height:100dvh;display:grid;place-items:center;padding:20px}</style></head>
<body><main class="login">
<div class="logo"><span class="drop"></span>GESTIÓN LÁCTEA</div>
<h1>Iniciar sesión</h1><p style="color:var(--mut);margin:0 0 6px">Administración de operaciones · Zurite y Cusco</p>
@if($errors->any())<p class="error">{{ $errors->first() }}</p>@endif
<form method="POST" action="{{ route('login.store') }}">@csrf
<label>Correo electrónico<input class="in" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
<label>Contraseña<input class="in" type="password" name="password" autocomplete="current-password" required></label>
<label class="check"><input type="checkbox" name="remember"> Recordarme</label>
<button class="btn">Ingresar al sistema</button></form></main></body></html>
