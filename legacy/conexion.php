<?
/*
 * CÓDIGO ORIGINAL (estilo PHP 4). Se deja solo como referencia de la migración.
 * No corre en PHP 7+ porque las funciones mysql_* fueron eliminadas.
 * Los comentarios [Pn] marcan cada problema; se explican en docs/MIGRACION.md.
 */

// [P1] Credenciales escritas en el código y versionadas en el repositorio.
$host = "localhost";
$user = "root";
$pass = "root123";
$base = "rrhh";

// [P2] Extensión mysql_* (eliminada en PHP 7). [P3] El @ oculta los errores.
$link = @mysql_connect($host, $user, $pass) or die("Error: " . mysql_error());
mysql_select_db($base, $link);

// [P4] Depende de register_globals: $id, $buscar, etc. "aparecen" solos desde la URL.
?>
