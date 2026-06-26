## Table

```mysql
CREATE TABLE `1090024db3`.`0cc_pinpon_pagos_unificada` (
  `ind_original` INT(10) UNSIGNED NOT NULL COMMENT 'ID original de la tabla anual',
  `anio_origen` INT(4) NOT NULL COMMENT 'Año de la tabla de la cual proviene el dato',
  `cedula` INT(11) DEFAULT NULL,
  `mes` VARCHAR(7) DEFAULT NULL COMMENT 'Almacena estrictamente YYYY-MM',
  `d` VARCHAR(10) DEFAULT NULL COMMENT 'Almacena el código (D7, D4, S1) o NULL si no existe',
  `plan` VARCHAR(50) DEFAULT NULL,
  `monto` INT(11) NOT NULL,
  `dolares` INT(11) NOT NULL,
  `zelle` INT(11) NOT NULL,
  `recibo` INT(11) NOT NULL,
  `fecha` INT(11) NOT NULL COMMENT 'Unix Timestamp',
  `observacion` VARCHAR(255) DEFAULT NULL,
  `operador` VARCHAR(50) DEFAULT NULL,
  -- Llave primaria compuesta para asegurar unicidad si los 'ind' se repiten por año
  PRIMARY KEY (`anio_origen`, `ind_original`),
  -- Índices para optimización de velocidad en consultas
  INDEX `idx_cedula_mes` (`cedula`, `mes`),
  INDEX `idx_fecha` (`fecha`),
  INDEX `idx_recibo` (`recibo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Insert

```mysql
INSERT INTO `1090024db3`.`0cc_pinpon_pagos_unificada` (
    `ind_original`, 
    `anio_origen`, 
    `cedula`, 
    `mes`, 
    `d`, 
    `plan`, 
    `monto`, 
    `dolares`, 
    `zelle`, 
    `recibo`, 
    `fecha`, 
    `observacion`, 
    `operador`
)
SELECT 
    `ind`, 
    2022 AS `anio_origen`, 
    `cedula`, 
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_pinpon_pagos_2022`

UNION ALL

SELECT 
    `ind`, 
    2023 AS `anio_origen`, 
    `cedula`, 
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_pinpon_pagos_2023`

UNION ALL

SELECT 
    `ind`, 
    2024 AS `anio_origen`, 
    `cedula`, 
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_pinpon_pagos_2024`

UNION ALL

SELECT 
    `ind`, 
    2025 AS `anio_origen`, 
    `cedula`, 
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_pinpon_pagos_2025`

UNION ALL

SELECT 
    `ind`, 
    2026 AS `anio_origen`, 
    `cedula`, 
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_pinpon_pagos_2026`;
```
