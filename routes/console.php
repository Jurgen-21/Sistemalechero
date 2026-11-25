<?php
use Illuminate\Support\Facades\Artisan;
Artisan::command('app:about', function () { $this->info('Sistema de Gestión Láctea - Zurite y Cusco'); })->purpose('Muestra información del sistema');
