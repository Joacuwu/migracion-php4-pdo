-- Tabla original (MySQL 4, MyISAM). Problemas: [P11] sin índice único en legajo,
-- sueldo como VARCHAR, fechas como texto y sin charset definido.
CREATE TABLE empleados (
  id int(11) NOT NULL auto_increment,
  legajo varchar(10),
  apellido varchar(50),
  nombre varchar(50),
  area varchar(50),
  ingreso varchar(10),
  sueldo varchar(20),
  PRIMARY KEY (id)
) TYPE=MyISAM;
