<? include("conexion.php"); ?>
<html>
<head><title>Legajos</title></head>
<body bgcolor="#FFFFFF">
<h2>Legajos de empleados</h2>
<form method="get" action="listado.php">
  Buscar: <input type="text" name="buscar" value="<? echo $buscar; ?>">  <!-- [P5] XSS: se imprime sin escapar -->
  <input type="submit" value="Buscar">
</form>
<a href="guardar.php">Nuevo empleado</a>
<table border="1" cellpadding="4">
<tr><td><b>Legajo</b></td><td><b>Apellido y nombre</b></td><td><b>Área</b></td><td><b>Ingreso</b></td><td><b>Sueldo</b></td><td></td></tr>
<?
// [P4] $buscar viene de register_globals. [P6] Inyección SQL: se concatena tal cual.
$sql = "SELECT * FROM empleados WHERE apellido LIKE '%$buscar%' OR area LIKE '%$buscar%' ORDER BY apellido";
$res = mysql_query($sql) or die("Error en la consulta: " . mysql_error() . "<br>" . $sql); // [P3] expone el SQL y el error al usuario

// [P7] Sin paginación: trae todos los registros de una vez.
while ($fila = mysql_fetch_array($res)) {
?>
<tr>
  <td><? echo $fila["legajo"]; ?></td>
  <td><? echo $fila["apellido"] . ", " . $fila["nombre"]; ?></td> <!-- [P5] XSS almacenado -->
  <td><? echo $fila["area"]; ?></td>
  <td><? echo $fila["ingreso"]; ?></td>
  <td>$ <? echo $fila["sueldo"]; ?></td>
  <td>
    <a href="guardar.php?id=<? echo $fila["id"]; ?>">Editar</a>
    <a href="borrar.php?id=<? echo $fila["id"]; ?>">Borrar</a> <!-- [P8] borra con un GET, sin confirmación ni CSRF -->
  </td>
</tr>
<? } ?>
</table>
</body>
</html>
