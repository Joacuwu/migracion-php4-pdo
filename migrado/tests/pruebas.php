<?php
/**
 * Pruebas de la capa de datos, sin dependencias. Usa una base SQLite en memoria.
 * Ejecutar desde la carpeta migrado/:  php tests/pruebas.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

putenv('RRHH_DB_DSN=sqlite::memory:');
$_SESSION = array();
require dirname(dirname(__FILE__)) . '/includes/empleados.php';
crear_base_demo(db());

$ok = 0;
$fallas = 0;
function probar($descripcion, $condicion)
{
    global $ok, $fallas;
    if ($condicion) {
        $ok++;
        echo "  OK    $descripcion\n";
    } else {
        $fallas++;
        echo "  FALLA $descripcion\n";
    }
}

$valido = array('legajo' => 's-0100', 'apellido' => 'Pérez', 'nombre' => 'Ana', 'area' => 'Ventas',
                'ingreso' => '2020-05-04', 'sueldo' => '850.000,50');

echo "Validaciones\n";
list($d, $err) = validar_empleado($valido, 0);
probar('datos válidos no tienen errores', $err === array());
probar('legajo se normaliza a mayúsculas', $d['legajo'] === 'S-0100');
probar('sueldo "850.000,50" se convierte a 850000.5', $d['sueldo'] === 850000.5);

list(, $err) = validar_empleado(array_merge($valido, array('legajo' => 'S-0007')), 0);
probar('legajo repetido es rechazado', isset($err['legajo']));

list(, $err) = validar_empleado(array_merge($valido, array('legajo' => 'S-0007')), 3);
probar('editar al mismo empleado conserva su legajo', !isset($err['legajo']));

list(, $err) = validar_empleado(array_merge($valido, array('ingreso' => '2021-02-30')), 0);
probar('fecha inexistente (30 de febrero) es rechazada', isset($err['ingreso']));

list(, $err) = validar_empleado(array_merge($valido, array('ingreso' => date('Y-m-d', strtotime('+1 day')))), 0);
probar('fecha futura es rechazada', isset($err['ingreso']));

list(, $err) = validar_empleado(array_merge($valido, array('sueldo' => '-5', 'area' => 'Marketing')), 0);
probar('sueldo negativo y área fuera de la lista son rechazados', isset($err['sueldo'], $err['area']));

list(, $err) = validar_empleado(array_merge($valido, array('apellido' => array('x'))), 0);
probar('un campo enviado como array no rompe la validación', isset($err['apellido']));

echo "Inyección SQL\n";
$total = contar_empleados('', '');
probar("búsqueda \"' OR '1'='1\" no devuelve nada", contar_empleados("' OR '1'='1", '') === 0);
probar('búsqueda "%" se trata como texto literal', contar_empleados('%', '') === 0);
probar("apellido con apóstrofo (O'Connor) se encuentra", contar_empleados("O'Connor", '') === 1);
probar('borrar "0 OR 1=1" no borra nada', eliminar_empleado(entero('0 OR 1=1')) === false && contar_empleados('', '') === $total);

echo "Alta, edición y baja\n";
list($d) = validar_empleado($valido, 0);
$id = guardar_empleado($d, 0);
probar('alta devuelve un id nuevo', $id > 0 && obtener_empleado($id) !== null);
$d['area'] = 'Sistemas';
guardar_empleado($d, $id);
$emp = obtener_empleado($id);
probar('edición actualiza el área', $emp['area'] === 'Sistemas');
probar('baja elimina el registro', eliminar_empleado($id) && obtener_empleado($id) === null);

echo "Paginación\n";
probar('la página 1 trae ' . POR_PAGINA . ' filas', count(buscar_empleados('', '', 1)) === POR_PAGINA);
probar('la página 2 trae el resto', count(buscar_empleados('', '', 2)) === $total - POR_PAGINA);
probar('filtro por área Sistemas trae 3', contar_empleados('', 'Sistemas') === 3);

echo "\n$ok pruebas OK, $fallas fallas\n";
exit($fallas > 0 ? 1 : 0);
