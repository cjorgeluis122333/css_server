# Invitados unificados

## Create the table
```mysql
CREATE TABLE `0cc_invitados_unificados` (
`ind` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
`cedula` int(11) DEFAULT NULL,
`nombre` tinytext DEFAULT NULL,
`fecha` DATE DEFAULT NULL,
`acc` int(11) DEFAULT NULL,
`fuente` tinytext DEFAULT NULL,
`operador` tinytext DEFAULT NULL,
PRIMARY KEY (`ind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Sql for get a list of all insert has to make

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


### Response of this sql:

```mysql
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202204`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202205`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202206`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202207`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202208`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202209`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202210`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202211`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202212`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202301`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202302`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202303`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202304`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202305`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202306`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202307`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202308`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202309`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202310`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202311`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202312`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202401`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202402`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202403`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202404`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202405`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202406`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202407`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202408`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202409`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202410`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202411`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202412`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202501`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202502`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202503`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202504`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202505`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202506`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202507`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202508`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202509`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202510`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202511`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202512`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202601`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202602`;
INSERT INTO `0cc_invitados_unificados` (cedula, nombre, fecha, acc, fuente, operador) SELECT cedula, nombre, STR_TO_DATE(fecha, '%Y-%m-%d'), acc, fuente, operador FROM `0cc_invitados_202603`;
```
