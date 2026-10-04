<?
include("conexion.php");

// [P4] register_globals: $id, $legajo, $apellido... llegan solos desde GET/POST.
if ($enviar) {
    // [P9] Sin validación: sueldo vacío, fechas inválidas o legajo repetido entran igual.
    if ($id) {
        // [P6] Inyección SQL en todos los campos.
        $sql = "UPDATE empleados SET legajo='$legajo', apellido='$apellido', nombre='$nombre',
                area='$area', ingreso='$ingreso', sueldo=$sueldo WHERE id=$id";
    } else {
        $sql = "INSERT INTO empleados (legajo, apellido, nombre, area, ingreso, sueldo)
                VALUES ('$legajo', '$apellido', '$nombre', '$area', '$ingreso', $sueldo)";
    }
    mysql_query($sql) or die(mysql_error()); // [P3]
    header("Location: listado.php");
    exit;
}

if ($id) {
    $res = mysql_query("SELECT * FROM empleados WHERE id=$id"); // [P6]
    $emp = mysql_fetch_array($res);
}
?>
<html>
<body>
<h2><? if ($id) echo "Editar"; else echo "Nuevo"; ?> empleado</h2>
<!-- [P10] Lógica, SQL y HTML mezclados en el mismo archivo. -->
<form method="post" action="guardar.php">
  <input type="hidden" name="id" value="<? echo $id; ?>">
  Legajo: <input type="text" name="legajo" value="<? echo $emp["legajo"]; ?>"><br>
  Apellido: <input type="text" name="apellido" value="<? echo $emp["apellido"]; ?>"><br>
  Nombre: <input type="text" name="nombre" value="<? echo $emp["nombre"]; ?>"><br>
  Área: <input type="text" name="area" value="<? echo $emp["area"]; ?>"><br>
  Ingreso (aaaa-mm-dd): <input type="text" name="ingreso" value="<? echo $emp["ingreso"]; ?>"><br>
  Sueldo: <input type="text" name="sueldo" value="<? echo $emp["sueldo"]; ?>"><br>
  <input type="submit" name="enviar" value="Guardar">
</form>
</body>
</html>
