# Bitácora de la migración

Detalle de cada problema marcado como `[Pn]` en `legacy/` y cómo se resolvió en `migrado/`. El mismo código `[Pn]` aparece como comentario en los dos lados para poder seguirlo.

## Orden de trabajo

1. **Relevar** el código viejo y anotar cada problema con un código `[Pn]` (sin cambiar nada).
2. **Base de datos primero:** revisar datos sucios y convertir la tabla (`migrado/sql/migracion.sql`).
3. **Capa de datos:** una sola conexión PDO y todas las consultas en `includes/empleados.php`.
4. **Pantallas:** reescribir listado, formulario y baja usando esa capa.
5. **Pruebas:** `tests/pruebas.php` cubre validaciones, inyección SQL, ABM y paginación.

## Problemas y soluciones

### P1 · Credenciales en el código
**Antes** (`legacy/conexion.php`):
```php
$user = "root";
$pass = "root123";
```
**Después** (`migrado/includes/config.php`): se leen de variables de entorno (`RRHH_DB_DSN`, `RRHH_DB_USER`, `RRHH_DB_PASS`) o de `config.local.php`, que está en `.gitignore`. El repositorio solo tiene `config.local.ejemplo.php`.

### P2 · Extensión `mysql_*`
Eliminada en PHP 7, así que el sistema no puede actualizar la versión de PHP. Se reemplazó por **PDO**, que además permite cambiar de motor (en modo demo usa SQLite sin tocar el resto del código).

### P3 · Errores ocultos o expuestos
El código viejo usaba `@` para esconder errores y `die(mysql_error() . $sql)` para mostrarlos, con el SQL incluido. Ahora PDO lanza excepciones (`ERRMODE_EXCEPTION`), `error_interno()` las registra con `error_log()` y el usuario ve un mensaje genérico. `display_errors` queda apagado.

### P4 · `register_globals`
Variables como `$buscar` o `$id` "aparecían" solas desde la URL. Además de inseguro, dejó de existir en PHP 5.4. Ahora toda entrada se lee explícitamente con `get()` y `post()`, y los ids pasan por `entero()`.

### P5 · XSS
Todo se imprimía con `echo` directo. Un apellido como `<script>...</script>` se ejecutaba en el navegador de quien viera el listado. Ahora toda salida pasa por `e()` (`htmlspecialchars` con `ENT_QUOTES` y UTF-8).

### P6 · Inyección SQL
**Antes:**
```php
$sql = "SELECT * FROM empleados WHERE apellido LIKE '%$buscar%' ...";
mysql_query("DELETE FROM empleados WHERE id=$id");
```
`borrar.php?id=0 OR 1=1` vaciaba la tabla.

**Después:**
```php
$stmt = db()->prepare('DELETE FROM empleados WHERE id = ?');
$stmt->execute(array($id));
```
En las búsquedas también se escapan los comodines de `LIKE` (`%` y `_`) con `ESCAPE '!'`, para que se busquen como texto.

### P7 · Sin paginación
El listado traía la tabla completa. Ahora se cuenta el total y se trae solo la página pedida con `LIMIT ? OFFSET ?`, con los parámetros enlazados como enteros.

### P8 · Borrado por GET sin confirmación
Cualquier link o `<img src="borrar.php?id=5">` borraba un registro (CSRF). Ahora la baja se confirma en un modal, viaja por **POST** con un **token CSRF** por sesión y `eliminar.php` responde 405 a cualquier otro método.

### P9 · Sin validación
Entraban legajos repetidos, fechas como `2021-02-30` y sueldos vacíos. Ahora `validar_empleado()` controla formato y unicidad del legajo, largo de nombre y apellido, área dentro de una lista, fecha real y no futura, y sueldo numérico positivo (acepta `850.000,50` o `850000.50`). Los errores se muestran junto a cada campo.

### P10 · Todo mezclado en un archivo
SQL, lógica y HTML convivían en `guardar.php`. Ahora:
- `includes/empleados.php`: consultas y reglas de negocio.
- `includes/funciones.php`: utilidades (entrada, escape, CSRF, mensajes).
- `includes/layout.php`: encabezado y pie comunes.
- `index.php`, `empleado.php`, `eliminar.php`: solo controlan el flujo y muestran datos.

### P11 · Esquema débil
MyISAM, sin charset, sueldo y fecha como texto, sin índice único en legajo. `sql/migracion.sql` primero **lista los datos que van a fallar** la conversión (legajos vacíos o repetidos, fechas y sueldos inválidos) para corregirlos, y después convierte a InnoDB + utf8mb4, `DATE`, `DECIMAL(12,2)`, `NOT NULL` y agrega índices.

## Compatibilidad
El código migrado evita sintaxis de PHP 7 (sin `??`, sin tipos escalares, `array()` en vez de `[]`) para correr tanto en un servidor con **PHP 5.6** como en **PHP 8.x**. Así se puede migrar primero el código y después actualizar la versión de PHP del servidor, sin hacer las dos cosas el mismo día.
