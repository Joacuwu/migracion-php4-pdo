# Migración de un módulo PHP 4 a PDO

🇬🇧 [Read in English](README.en.md)

Caso práctico de **mantenimiento de un sistema heredado**: un ABM de legajos de empleados escrito al estilo PHP 4 (`mysql_*`, `register_globals`, SQL concatenado) migrado a **PHP con PDO**, compatible con PHP 5.6 y 8.x, con Bootstrap 5 y jQuery.

El repositorio tiene **las dos versiones**, para comparar el antes y el después:

| Carpeta | Contenido |
|---|---|
| `legacy/` | Código original. Solo de referencia: no corre en PHP 7+. Cada problema está marcado con un comentario `[P1]`…`[P11]`. |
| `migrado/` | Versión nueva, funcionando. Los mismos códigos `[Pn]` marcan dónde se resolvió cada problema. |
| `docs/MIGRACION.md` | Bitácora: orden de trabajo y explicación de cada problema con el código de antes y después. |

## Qué se corrigió

| # | Problema en el código viejo | Solución |
|---|---|---|
| P1 | Usuario y contraseña de la base escritos en el código | Variables de entorno o `config.local.php` fuera de git |
| P2 | Funciones `mysql_*` (eliminadas en PHP 7) | PDO con una sola conexión |
| P3 | Errores ocultos con `@` o mostrados con el SQL al usuario | Excepciones, `error_log()` y mensaje genérico |
| P4 | Depende de `register_globals` | Lectura explícita de `$_GET` / `$_POST` |
| P5 | XSS: datos impresos sin escapar | `htmlspecialchars` en toda salida |
| P6 | Inyección SQL en búsqueda, alta, edición y baja | Consultas preparadas y escape de comodines `LIKE` |
| P7 | Listado sin paginación | `COUNT` + `LIMIT/OFFSET` enlazados como enteros |
| P8 | Borrado por GET, sin confirmación ni CSRF | Modal de confirmación, POST y token CSRF |
| P9 | Sin validación de datos | Validación del lado del servidor con errores por campo |
| P10 | SQL, lógica y HTML en el mismo archivo | Capa de datos, utilidades y vistas separadas |
| P11 | Tabla MyISAM con fechas y sueldos como texto | Script de migración a InnoDB, `DATE`, `DECIMAL` e índices |

## Funcionalidades de la versión migrada
- Listado con búsqueda por apellido, nombre o legajo, filtro por área y paginación.
- Alta y edición en un mismo formulario, con validación: legajo con formato `S-0012` y único, fecha real y no futura, sueldo en formato argentino (`850.000,50`).
- Baja con confirmación en un modal.
- jQuery para dar formato al legajo mientras se escribe y para el modal de baja.
- Diseño adaptable a celular.

## Casos de prueba documentados
| # | Caso | En el sistema viejo | En el migrado |
|---|---|---|---|
| 1 | Buscar `' OR '1'='1` | Devuelve toda la tabla | 0 resultados |
| 2 | Abrir `borrar.php?id=0 OR 1=1` | Borra todos los empleados | `eliminar.php` solo acepta POST con token: 405 |
| 3 | Apellido `<script>alert(1)</script>` | Se ejecuta el script en el listado | Se muestra como texto |
| 4 | Buscar `O'Connor` | Error de SQL en pantalla | Lo encuentra |
| 5 | Alta con legajo repetido | Se guarda duplicado | "Ya existe un empleado con ese legajo" |
| 6 | Fecha de ingreso `2021-02-30` | Se guarda tal cual | "Fecha inválida" |
| 7 | Sueldo `850.000,50` | Error de SQL o valor truncado | Se guarda 850000.50 |
| 8 | Base de datos caída | Muestra el error de MySQL | Mensaje genérico; el detalle va al log |

Los casos 1, 2, 4, 5, 6 y 7 están automatizados en `migrado/tests/pruebas.php`, junto con alta, edición, baja y paginación.

## Cómo correrlo
**Local (modo demo, sin instalar MySQL):**
```bash
cd migrado
php -S localhost:8000
```
Abrir `http://localhost:8000`. Se crea `data/demo.sqlite` con 12 empleados de ejemplo.

**Pruebas:**
```bash
cd migrado
php tests/pruebas.php
```

**Servidor con MySQL (ej. InfinityFree):**
1. Base nueva: ejecutar `migrado/sql/schema.sql` en phpMyAdmin. Base existente: hacer un backup y seguir `migrado/sql/migracion.sql` paso a paso.
2. Copiar `includes/config.local.ejemplo.php` como `includes/config.local.php` y completar los datos.
3. Subir el contenido de `migrado/` a `htdocs/`.

Requiere PHP 5.6 o superior con PDO (`pdo_mysql`, o `pdo_sqlite` para el modo demo).

## Capturas (versión migrada)
| Listado | Validaciones |
|---|---|
| ![](docs/capturas/1-listado.png) | ![](docs/capturas/2-validaciones.png) |
