-- 1. Primero, asegúrate de haber creado la tabla unificado en 1090024db3
-- usando la estructura que definimos antes.

-- 2. Ejecuta la migración de datos (puedes correr estos inserts uno tras otro)

INSERT INTO `1090024db3`.`0cc_salones_control_unificado`
(fecha, salon, acc, nombre, abono, pago, pases, hora)
SELECT fecha, salon, acc, nombre, abono, pago, pases, hora
FROM `1090024db2`.`0cc_salones_control_2022`;

INSERT INTO `1090024db3`.`0cc_salones_control_unificado`
(fecha, salon, acc, nombre, abono, pago, pases, hora)
SELECT fecha, salon, acc, nombre, abono, pago, pases, hora
FROM `1090024db2`.`0cc_salones_control_2023`;

INSERT INTO `1090024db3`.`0cc_salones_control_unificado`
(fecha, salon, acc, nombre, abono, pago, pases, hora)
SELECT fecha, salon, acc, nombre, abono, pago, pases, hora
FROM `1090024db2`.`0cc_salones_control_2024`;

INSERT INTO `1090024db3`.`0cc_salones_control_unificado`
(fecha, salon, acc, nombre, abono, pago, pases, hora)
SELECT fecha, salon, acc, nombre, abono, pago, pases, hora
FROM `1090024db2`.`0cc_salones_control_2025`;

INSERT INTO `1090024db3`.`0cc_salones_control_unificado`
(fecha, salon, acc, nombre, abono, pago, pases, hora)
SELECT fecha, salon, acc, nombre, abono, pago, pases, hora
FROM `1090024db2`.`0cc_salones_control_2026`;



## Tabla
```mysql

CREATE TABLE `salones_control_unificado` (
-- 'ind' como BIGINT UNSIGNED AUTO_INCREMENT (Equivalente a $table->id)
`ind` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

-- Campos con tipos de datos optimizados para índices
`fecha` DATE DEFAULT NULL,
`salon` VARCHAR(100) DEFAULT NULL,
`acc` INT DEFAULT NULL,
`nombre` VARCHAR(255) DEFAULT NULL,

-- Campos numéricos
`abono` DECIMAL(11, 2) DEFAULT NULL,
`pago` DECIMAL(11, 2) DEFAULT NULL,
`pases` INT DEFAULT NULL,

-- Campo de tiempo
`hora` VARCHAR(50) DEFAULT NULL,

-- Definición de Índices
INDEX `idx_fecha` (`fecha`),
INDEX `idx_salon_fecha` (`salon`, `fecha`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
