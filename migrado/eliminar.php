<?php
require_once 'includes/empleados.php';

// [P8] Solo POST con token CSRF; un link o una imagen ya no pueden borrar registros.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}
verificar_csrf();

try {
    $borrado = eliminar_empleado(entero(post('id')));
} catch (PDOException $ex) {
    error_interno($ex);
}

flash($borrado ? 'success' : 'warning', $borrado ? 'Empleado borrado.' : 'El empleado ya no existía.');
redirigir('index.php');
