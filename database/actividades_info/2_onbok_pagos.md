### Create new table
```mysql
CREATE TABLE IF NOT EXISTS `1090024db3`.`0cc_onbox_pagos_all` (
  `ind` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` INT(11) UNSIGNED DEFAULT NULL,
  
  -- Columnas separadas según tu requerimiento
  `mes` VARCHAR(7) DEFAULT NULL COMMENT 'Almacena estrictamente YYYY-MM',
  `d` VARCHAR(10) DEFAULT NULL COMMENT 'Almacena el código (D7, D4, S1) o NULL si no existe',
  
  `plan` VARCHAR(50) DEFAULT NULL,
  `monto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `dolares` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `zelle` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `recibo` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `fecha` INT(11) UNSIGNED NOT NULL COMMENT 'Unix Timestamp',
  `observacion` TEXT DEFAULT NULL,
  `operador` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`ind`),
  -- Índices de alta velocidad
  INDEX `idx_mes` (`mes`),
  INDEX `idx_mes_d` (`mes`, `d`),
  INDEX `idx_cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```



### Insert info

```mysql
-- TABLA 2022
INSERT INTO `1090024db3`.`0cc_onbox_pagos_all`
(`cedula`, `mes`, `d`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT
    `cedula`,
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_onbox_pagos_2022`;

-- TABLA 2023
INSERT INTO `1090024db3`.`0cc_onbox_pagos_all`
(`cedula`, `mes`, `d`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT
    `cedula`,
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_onbox_pagos_2023`;

-- TABLA 2024
INSERT INTO `1090024db3`.`0cc_onbox_pagos_all`
(`cedula`, `mes`, `d`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT
    `cedula`,
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_onbox_pagos_2024`;

-- TABLA 2025
INSERT INTO `1090024db3`.`0cc_onbox_pagos_all`
(`cedula`, `mes`, `d`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT
    `cedula`,
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_onbox_pagos_2025`;

-- TABLA 2026
INSERT INTO `1090024db3`.`0cc_onbox_pagos_all`
(`cedula`, `mes`, `d`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT
    `cedula`,
    SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
    IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
    `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM `1090024db2`.`0cc_onbox_pagos_2026`;
```
