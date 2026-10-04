-- Instalación desde cero (MySQL 5.6+ / MariaDB).
CREATE TABLE IF NOT EXISTS empleados (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  legajo VARCHAR(10) NOT NULL,
  apellido VARCHAR(50) NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  area VARCHAR(50) NOT NULL,
  ingreso DATE NOT NULL,
  sueldo DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_empleados_legajo (legajo),
  KEY idx_empleados_apellido (apellido),
  KEY idx_empleados_area (area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO empleados (legajo, apellido, nombre, area, ingreso, sueldo) VALUES
('A-0012', 'Gómez', 'Laura', 'Administración', '2015-03-02', 845000),
('S-0007', 'Fernández', 'Lucía', 'Sistemas', '2012-05-21', 1250000),
('V-0003', 'Martínez', 'Diego', 'Ventas', '2010-01-18', 870000),
('D-0015', 'Acosta', 'Mariana', 'Depósito', '2016-09-26', 710000),
('R-0009', 'Domínguez', 'Paula', 'RRHH', '2017-03-13', 830000);
