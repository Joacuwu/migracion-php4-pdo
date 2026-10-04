<?php
require_once dirname(__FILE__) . '/config.php';

/**
 * [P2] Conexión PDO única (reemplaza mysql_connect). En modo demo crea una base SQLite
 * con los mismos datos que tenía el sistema viejo.
 */
function db()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $opciones = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    );

    if (DB_DSN !== '') {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $opciones);
        return $pdo;
    }

    $archivo = dirname(dirname(__FILE__)) . '/data/demo.sqlite';
    $nueva = !file_exists($archivo);
    $pdo = new PDO('sqlite:' . $archivo, null, null, $opciones);
    if ($nueva) {
        crear_base_demo($pdo);
    }
    return $pdo;
}

function crear_base_demo(PDO $pdo)
{
    // [P11] Legajo único, sueldo numérico y fecha como DATE.
    $pdo->exec('CREATE TABLE empleados (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        legajo VARCHAR(10) NOT NULL UNIQUE,
        apellido VARCHAR(50) NOT NULL,
        nombre VARCHAR(50) NOT NULL,
        area VARCHAR(50) NOT NULL,
        ingreso DATE NOT NULL,
        sueldo DECIMAL(12,2) NOT NULL
    )');

    $ejemplos = array(
        array('A-0012', 'Gómez', 'Laura', 'Administración', '2015-03-02', 845000),
        array('A-0031', 'Pereyra', 'Martín', 'Administración', '2019-08-12', 790000),
        array('S-0007', 'Fernández', 'Lucía', 'Sistemas', '2012-05-21', 1250000),
        array('S-0044', 'Ríos', 'Tomás', 'Sistemas', '2021-02-01', 980000),
        array('S-0051', "O'Connor", 'Sofía', 'Sistemas', '2022-11-14', 920000),
        array('V-0003', 'Martínez', 'Diego', 'Ventas', '2010-01-18', 870000),
        array('V-0029', 'López', 'Carla', 'Ventas', '2018-06-04', 760000),
        array('V-0060', 'Sosa', 'Julián', 'Ventas', '2023-04-10', 690000),
        array('D-0015', 'Acosta', 'Mariana', 'Depósito', '2016-09-26', 710000),
        array('D-0022', 'Benítez', 'Ramón', 'Depósito', '2014-07-07', 735000),
        array('D-0048', 'Castro', 'Nicolás', 'Depósito', '2020-10-19', 680000),
        array('R-0009', 'Domínguez', 'Paula', 'RRHH', '2017-03-13', 830000),
    );
    $stmt = $pdo->prepare('INSERT INTO empleados (legajo, apellido, nombre, area, ingreso, sueldo) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($ejemplos as $e) {
        $stmt->execute($e);
    }
}
