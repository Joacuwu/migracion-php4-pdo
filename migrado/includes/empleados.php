<?php
/**
 * [P10] Acceso a datos y reglas de negocio de empleados, separados de las vistas.
 * [P6] Todas las consultas son preparadas: ningún dato del usuario se concatena en el SQL.
 */
require_once dirname(__FILE__) . '/funciones.php';

function areas_validas()
{
    return array('Administración', 'Depósito', 'RRHH', 'Sistemas', 'Ventas');
}

/** Escapa los comodines de LIKE para que "%" o "_" se busquen literalmente. */
function escapar_like($texto)
{
    return str_replace(array('!', '%', '_'), array('!!', '!%', '!_'), $texto);
}

/** Arma el WHERE de búsqueda y sus parámetros. */
function filtro_empleados($texto, $area)
{
    $where = array();
    $params = array();

    if ($texto !== '') {
        $like = '%' . escapar_like($texto) . '%';
        $where[] = "(apellido LIKE ? ESCAPE '!' OR nombre LIKE ? ESCAPE '!' OR legajo LIKE ? ESCAPE '!')";
        array_push($params, $like, $like, $like);
    }
    if ($area !== '' && in_array($area, areas_validas(), true)) {
        $where[] = 'area = ?';
        $params[] = $area;
    }

    return array($where ? ' WHERE ' . implode(' AND ', $where) : '', $params);
}

function contar_empleados($texto, $area)
{
    list($where, $params) = filtro_empleados($texto, $area);
    $stmt = db()->prepare('SELECT COUNT(*) FROM empleados' . $where);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/** [P7] Devuelve solo la página pedida. */
function buscar_empleados($texto, $area, $pagina)
{
    list($where, $params) = filtro_empleados($texto, $area);
    $stmt = db()->prepare('SELECT id, legajo, apellido, nombre, area, ingreso, sueldo FROM empleados'
        . $where . ' ORDER BY apellido, nombre LIMIT ? OFFSET ?');

    $i = 1;
    foreach ($params as $valor) {
        $stmt->bindValue($i++, $valor, PDO::PARAM_STR);
    }
    $stmt->bindValue($i++, POR_PAGINA, PDO::PARAM_INT);
    $stmt->bindValue($i, ($pagina - 1) * POR_PAGINA, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtener_empleado($id)
{
    $stmt = db()->prepare('SELECT id, legajo, apellido, nombre, area, ingreso, sueldo FROM empleados WHERE id = ?');
    $stmt->execute(array($id));
    $fila = $stmt->fetch();
    return $fila ? $fila : null;
}

function legajo_existe($legajo, $excluir_id)
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM empleados WHERE legajo = ? AND id <> ?');
    $stmt->execute(array($legajo, $excluir_id));
    return $stmt->fetchColumn() > 0;
}

function largo($texto)
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : (int) preg_match_all('/./us', $texto);
}

/** Convierte "845.000,50", "845000,5" o "845000.50" a número. Devuelve null si no es válido. */
function parsear_sueldo($texto)
{
    $texto = str_replace(array('$', ' '), '', $texto);
    if (strpos($texto, ',') !== false) {
        $texto = str_replace(array('.', ','), array('', '.'), $texto);
    }
    return is_numeric($texto) ? round((float) $texto, 2) : null;
}

/**
 * [P9] Valida y normaliza los datos del formulario.
 * Devuelve array($datos_normalizados, $errores).
 */
function validar_empleado($entrada, $id)
{
    $d = array();
    foreach (array('legajo', 'apellido', 'nombre', 'area', 'ingreso', 'sueldo') as $campo) {
        $d[$campo] = isset($entrada[$campo]) && is_string($entrada[$campo]) ? trim($entrada[$campo]) : '';
    }
    $d['legajo'] = strtoupper($d['legajo']);
    $err = array();

    if (!preg_match('/^[A-Z]-\d{4}$/', $d['legajo'])) {
        $err['legajo'] = 'Formato esperado: letra, guion y 4 números (ej. S-0012).';
    } elseif (legajo_existe($d['legajo'], $id)) {
        $err['legajo'] = 'Ya existe un empleado con ese legajo.';
    }

    foreach (array('apellido' => 'El apellido', 'nombre' => 'El nombre') as $campo => $etiqueta) {
        $n = largo($d[$campo]);
        if ($n < 2 || $n > 50) {
            $err[$campo] = $etiqueta . ' debe tener entre 2 y 50 caracteres.';
        }
    }

    if (!in_array($d['area'], areas_validas(), true)) {
        $err['area'] = 'Elegí un área de la lista.';
    }

    $fecha = DateTime::createFromFormat('!Y-m-d', $d['ingreso']);
    if (!$fecha || $fecha->format('Y-m-d') !== $d['ingreso']) {
        $err['ingreso'] = 'Fecha inválida.';
    } elseif ($fecha > new DateTime('today')) {
        $err['ingreso'] = 'La fecha de ingreso no puede ser futura.';
    }

    $sueldo = parsear_sueldo($d['sueldo']);
    if ($sueldo === null || $sueldo <= 0 || $sueldo >= 100000000) {
        $err['sueldo'] = 'Ingresá un sueldo válido mayor a cero.';
    } else {
        $d['sueldo'] = $sueldo;
    }

    return array($d, $err);
}

function guardar_empleado($d, $id)
{
    $valores = array($d['legajo'], $d['apellido'], $d['nombre'], $d['area'], $d['ingreso'], $d['sueldo']);
    if ($id > 0) {
        $valores[] = $id;
        $stmt = db()->prepare('UPDATE empleados SET legajo = ?, apellido = ?, nombre = ?, area = ?, ingreso = ?, sueldo = ? WHERE id = ?');
        $stmt->execute($valores);
        return $id;
    }
    $stmt = db()->prepare('INSERT INTO empleados (legajo, apellido, nombre, area, ingreso, sueldo) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute($valores);
    return (int) db()->lastInsertId();
}

function eliminar_empleado($id)
{
    $stmt = db()->prepare('DELETE FROM empleados WHERE id = ?');
    $stmt->execute(array($id));
    return $stmt->rowCount() > 0;
}
