<?
include("conexion.php");

// [P8] Cualquier link o <img src="borrar.php?id=5"> borra un registro.
// [P6] borrar.php?id=0 OR 1=1 borra la tabla entera.
mysql_query("DELETE FROM empleados WHERE id=$id");
header("Location: listado.php");
?>
