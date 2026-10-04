<?php
/**
 * Configuración. Compatible con PHP 5.6+ (sin sintaxis de PHP 7).
 * [P1] Las credenciales se leen de variables de entorno o de config.local.php (fuera de git).
 * Si no hay DSN se usa SQLite en /data (modo demo local).
 */
$local = dirname(__FILE__) . '/config.local.php';
$cfg = file_exists($local) ? require $local : array();

function cfg_valor($cfg, $clave, $env)
{
    if (isset($cfg[$clave])) {
        return $cfg[$clave];
    }
    $valor = getenv($env);
    return $valor === false ? '' : $valor;
}

define('DB_DSN',  cfg_valor($cfg, 'dsn',  'RRHH_DB_DSN'));   // ej: 'mysql:host=localhost;dbname=rrhh;charset=utf8mb4'
define('DB_USER', cfg_valor($cfg, 'user', 'RRHH_DB_USER'));
define('DB_PASS', cfg_valor($cfg, 'pass', 'RRHH_DB_PASS'));

define('APP_NOMBRE', 'Legajos');
define('POR_PAGINA', 10);

// [P3] Los errores van al log del servidor, nunca a la pantalla.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
date_default_timezone_set('America/Argentina/Buenos_Aires');
