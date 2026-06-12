## Table
```mysql
CREATE TABLE `1090024db3`.`0cc_basquet_pagos` (
  `ind` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` INT(11) DEFAULT NULL,
  `mes` VARCHAR(7) DEFAULT NULL COMMENT 'Almacena estrictamente YYYY-MM',
  `d` VARCHAR(10) DEFAULT NULL COMMENT 'Almacena el código (D7, D4, S1) o NULL si no existe',
  `plan` VARCHAR(50) DEFAULT NULL,
  `monto` INT(11) NOT NULL,
  `dolares` INT(11) NOT NULL,
  `zelle` INT(11) NOT NULL,
  `recibo` INT(11) NOT NULL,
  `fecha` INT(11) NOT NULL COMMENT 'Unix Timestamp de la transaccion',
  `observacion` VARCHAR(255) DEFAULT NULL,
  `operador` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`ind`),
  INDEX `idx_cedula_mes` (`cedula`, `mes`),
  INDEX `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Insert

```mysql
INSERT INTO `1090024db3`.`0cc_basquet_pagos` 
(cedula, mes, d, plan, monto, dolares, zelle, recibo, fecha, observacion, operador)

SELECT 
    cedula, 
    SUBSTRING_INDEX(mes, '|', 1) AS mes,
    IF(mes LIKE '%|%', SUBSTRING_INDEX(mes, '|', -1), NULL) AS d,
    plan, monto, dolares, zelle, recibo, fecha, observacion, operador
FROM `1090024db2`.`0cc_basquet_pagos_2022`

UNION ALL

SELECT 
    cedula, 
    SUBSTRING_INDEX(mes, '|', 1) AS mes,
    IF(mes LIKE '%|%', SUBSTRING_INDEX(mes, '|', -1), NULL) AS d,
    plan, monto, dolares, zelle, recibo, fecha, observacion, operador
FROM `1090024db2`.`0cc_basquet_pagos_2023`

UNION ALL

SELECT 
    cedula, 
    SUBSTRING_INDEX(mes, '|', 1) AS mes,
    IF(mes LIKE '%|%', SUBSTRING_INDEX(mes, '|', -1), NULL) AS d,
    plan, monto, dolares, zelle, recibo, fecha, observacion, operador
FROM `1090024db2`.`0cc_basquet_pagos_2024`

UNION ALL

SELECT 
    cedula, 
    SUBSTRING_INDEX(mes, '|', 1) AS mes,
    IF(mes LIKE '%|%', SUBSTRING_INDEX(mes, '|', -1), NULL) AS d,
    plan, monto, dolares, zelle, recibo, fecha, observacion, operador
FROM `1090024db2`.`0cc_basquet_pagos_2025`

UNION ALL

SELECT 
    cedula, 
    SUBSTRING_INDEX(mes, '|', 1) AS mes,
    IF(mes LIKE '%|%', SUBSTRING_INDEX(mes, '|', -1), NULL) AS d,
    plan, monto, dolares, zelle, recibo, fecha, observacion, operador
FROM `1090024db2`.`0cc_basquet_pagos_2026`;
```
