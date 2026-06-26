# Invitados unificados

## Create the table
```mysql
CREATE TABLE `0cc_invitados_unificados` (
  `ind` INT UNSIGNED NOT NULL AUTO_INCREMENT,
   `cedula` VARCHAR(50) DEFAULT NULL,
   `nombre` VARCHAR(255) DEFAULT NULL,
   `fecha` DATE DEFAULT NULL,
  `acc` INT DEFAULT NULL,                      
  `fuente` VARCHAR(100) DEFAULT NULL,
  `operador` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`ind`),
    -- Índices optimizados
  INDEX `idx_fecha` (`fecha`),
  INDEX `idx_acc_fecha` (`acc`, `fecha`),
  INDEX `idx_cedula_fecha` (`cedula`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Sql for get a list of all insert has to make
### V1
```mysql
SELECT CONCAT(
    'INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) ',
    'SELECT cedula, nombre, STR_TO_DATE(fecha, ''%Y-%m-%d''), acc, fuente, operador ',
    'FROM `', TABLE_NAME, '`;'
) AS script_generado
FROM information_schema.tables
WHERE table_schema = 'nombre_de_tu_base_de_datos' 
  AND table_name REGEXP '^0cc_invitados_[0-9]{6}$';
```
### V2(Recomendado)
```mysql
SELECT CONCAT(
    'INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) ',
    'SELECT ',
    'CAST(cedula AS CHAR), ', -- Aseguramos que la cédula entre como texto
    'LEFT(nombre, 255), ',    -- Cortamos el nombre si es muy largo para evitar error
    'IF(fecha = "" OR fecha IS NULL, NULL, fecha), ', -- Validación simple de fecha
    'acc, fuente, operador ',
    'FROM `', TABLE_NAME, '`;'
) AS script_generado
FROM information_schema.tables
WHERE table_schema = 'nombre_de_tu_base_de_datos' 
  AND table_name REGEXP '^0cc_invitados_[0-9]{6}$';
```


### Response of this sql:

```mysql
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202209`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202402`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202507`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202508`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202210`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202301`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202407`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202501`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202204`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202405`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202212`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202502`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202308`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202205`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202410`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202504`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202505`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202603`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202412`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202312`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202503`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202511`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202404`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202306`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202401`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202208`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202601`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202206`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202509`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202409`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202406`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202311`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202302`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202309`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202510`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202403`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202602`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202305`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202506`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202512`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202408`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202303`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202304`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202307`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202207`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202411`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202211`;
INSERT IGNORE INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT CAST(cedula AS CHAR), LEFT(nombre, 255), IF(fecha = "" OR fecha IS NULL, NULL, fecha), acc, fuente, operador FROM `0cc_invitados_202310`;
```
