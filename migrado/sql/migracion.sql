-- [P11] Migración de la tabla vieja (legacy/empleados.sql) sin perder datos.
-- Hacer un backup antes: mysqldump rrhh empleados > empleados_backup.sql
-- Cada paso se puede revisar antes de seguir con el próximo.

-- 1. Revisar datos que van a fallar la conversión (corregirlos a mano antes de seguir).
SELECT id, legajo FROM empleados WHERE legajo IS NULL OR legajo = '';
SELECT legajo, COUNT(*) AS repetidos FROM empleados GROUP BY legajo HAVING COUNT(*) > 1;
SELECT id, ingreso FROM empleados WHERE STR_TO_DATE(ingreso, '%Y-%m-%d') IS NULL;
SELECT id, sueldo FROM empleados WHERE sueldo NOT REGEXP '^[0-9]+([.][0-9]{1,2})?$';

-- 2. Motor transaccional y charset que soporta acentos y ñ sin problemas.
ALTER TABLE empleados ENGINE = InnoDB;
ALTER TABLE empleados CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. Tipos correctos y campos obligatorios.
ALTER TABLE empleados
  MODIFY legajo VARCHAR(10) NOT NULL,
  MODIFY apellido VARCHAR(50) NOT NULL,
  MODIFY nombre VARCHAR(50) NOT NULL,
  MODIFY area VARCHAR(50) NOT NULL,
  MODIFY ingreso DATE NOT NULL,
  MODIFY sueldo DECIMAL(12,2) NOT NULL;

-- 4. Índices: legajo único y búsquedas frecuentes.
ALTER TABLE empleados
  ADD UNIQUE KEY uk_empleados_legajo (legajo),
  ADD KEY idx_empleados_apellido (apellido),
  ADD KEY idx_empleados_area (area);
