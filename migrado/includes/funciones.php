<?php
require_once dirname(__FILE__) . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/', '', isset($_SERVER['HTTPS']), true);
    session_start();
}

/** [P5] Escapa texto para HTML. Toda salida pasa por acá. */
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function redirigir($url)
{
    header('Location: ' . $url);
    exit;
}

/** [P4] Lectura explícita de la entrada (reemplaza register_globals). */
function get($campo)
{
    return isset($_GET[$campo]) ? trim((string) $_GET[$campo]) : '';
}

function post($campo)
{
    return isset($_POST[$campo]) ? trim((string) $_POST[$campo]) : '';
}

/** Entero positivo o 0 si no es válido. */
function entero($valor)
{
    return ctype_digit((string) $valor) ? (int) $valor : 0;
}

/* ---------- [P8] CSRF ---------- */

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = function_exists('random_bytes')
            ? bin2hex(random_bytes(32))
            : bin2hex(openssl_random_pseudo_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verificar_csrf()
{
    $token = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('Solicitud inválida. Volvé atrás y recargá la página.');
    }
}

/* ---------- Mensajes flash ---------- */

function flash($tipo, $texto)
{
    $_SESSION['flash'] = array('tipo' => $tipo, 'texto' => $texto);
}

function mostrar_flash()
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert-' . e($f['tipo']) . ' alert-dismissible fade show" role="alert">' . e($f['texto'])
         . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
}

/** Formato de moneda argentino: 1234567.5 -> 1.234.567,50 */
function pesos($numero)
{
    return number_format((float) $numero, 2, ',', '.');
}

/** [P3] Registra el error real en el log y muestra un mensaje genérico. */
function error_interno(Exception $ex)
{
    error_log('[legajos] ' . $ex->getMessage());
    http_response_code(500);
    exit('Ocurrió un error interno. Intentá de nuevo más tarde.');
}
