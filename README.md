# Gestión Láctea · Sistema Lechero

Panel administrativo en **Laravel 12** para una empresa láctea (Zurite y Cusco): proveedores, entregas de leche, pagos, ventas, gastos, distribuciones, inventario, reportes y usuarios. Diseño responsive estilo **Liquid Glass (iOS 27)**.

## Requisitos
- XAMPP con **PHP 8.4** y MySQL/MariaDB (el `vendor` incluido lo exige).
- La carpeta `vendor` ya viene incluida: **no ejecutes `composer install`**.

## Instalación (primera vez)
1. Copia la carpeta `SistemaLechero` a `C:\xampp\htdocs`.
2. En XAMPP inicia **Apache** y **MySQL**.
3. En phpMyAdmin crea la base `sistema_lechero` (cotejamiento `utf8mb4_unicode_ci`).
4. Revisa `.env`: usa `DB_PORT=3306`, usuario `root` y clave vacía. Si tu MySQL usa otro puerto (por ejemplo 3307), cámbialo ahí.
5. En PowerShell, dentro de la carpeta del proyecto:
   ```powershell
   C:\xampp\php\php.exe artisan migrate --seed
   C:\xampp\php\php.exe artisan optimize:clear
   C:\xampp\php\php.exe artisan serve
   ```
6. Abre http://127.0.0.1:8000

**Acceso inicial:** `admin@lacteos.local` / `Admin12345!` → cámbiala en **Mi perfil**.

## Si ya tenías la base con datos
Solo ejecuta `php artisan migrate`. Las migraciones nuevas son aditivas (agregan la columna `rol` a `users`, con valor `admin` para los usuarios que ya existen, y la tabla `producciones`). **Nunca uses `migrate:fresh`** en una base con datos. Antes de migrar, descarga un respaldo o expórtala desde phpMyAdmin.

## Módulos
| Módulo | Qué hace |
|---|---|
| Tablero | Ganancia del mes, reparto de cada sol vendido, 6 meses, existencias, cuadre y deudas |
| Proveedores | Alta, búsqueda, detalle, activar/desactivar, estado de cuenta |
| Entregas | Litros × precio = importe; editar y quitar (si no hay pagos que la cubran) |
| Pagos | Abonos y saldo por proveedor; no se puede pagar más de lo que se debe |
| Ventas / Gastos | Registrar, editar y quitar, con resumen por producto / categoría |
| Inventario | Producción de queso y yogur, rendimiento y existencias de leche, queso y yogur |
| Distribuciones | Envíos a Zurite y Cusco con estado, filtros y edición |
| Reportes | Resumen mensual imprimible (PDF) y descarga de datos en CSV para Excel |
| Usuarios | Alta, rol, cambio de contraseña (solo administrador) |
| Respaldo | Descarga de todos los datos en un `.sql` (solo administrador) |

**Existencias:** Leche = litros recibidos − litros usados en producción − leche vendida. Queso y yogur = producido − vendido (según la *cantidad* que registres en cada venta). Las distribuciones no descuentan existencias.

## Roles
- **Administrador:** todo, incluido borrar registros, usuarios y respaldo.
- **Operador:** registra y edita, pero no borra ni entra a Usuarios ni Respaldo.
El sistema no deja eliminar al último administrador ni a tu propio usuario.

## Reglas de fechas y borrado
- **Fechas:** entregas, pagos, ventas, gastos, producción y fecha de registro del proveedor no admiten fechas futuras ni anteriores al 01/01/2020. En **Distribuciones**, «Pendiente» y «En ruta» no admiten fecha pasada (al editar se conserva la ya guardada) y «Entregado» no admite fecha futura.
- **Proveedores:** se editan desde el botón *Editar* del directorio (nombre, zona, teléfono, precio por litro y fecha de registro) o desde su ficha. Cambiar el precio no altera las entregas ya registradas.
- **Eliminar:** todos los botones *Quitar* abren una ventana de advertencia («Esta acción no se puede deshacer») antes de borrar.

## Reportes y respaldo
- **PDF:** en Reportes o en Estado de cuenta usa *Imprimir / Guardar PDF* y elige «Guardar como PDF».
- **Excel:** los CSV llevan tildes correctas y se abren directo en Excel.
- **Restaurar un respaldo:** crea las tablas con `php artisan migrate` e importa el `.sql` desde phpMyAdmin → Importar.

## Diseño
Cristal translúcido con borde oscuro y brillo, barra lateral en escritorio y barra flotante en el celular. El deslizador **Transparencia** (Cristal ↔ Sólido) se guarda en cada navegador y el sistema respeta «reducir transparencia» y «mayor contraste» del dispositivo.

## Pruebas
```powershell
C:\xampp\php\php.exe vendor\phpunit\phpunit\phpunit
```
Cubren saldos, pagos, edición, roles, inventario, CSV, respaldo y el tablero con pérdida.

## Antes de publicarlo en internet
`APP_DEBUG` ya está en `false`. Usa `APP_ENV=production`, contraseña propia para MySQL (no `root` vacío) y HTTPS.
