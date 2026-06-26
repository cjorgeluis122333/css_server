## Table

```mysql
CREATE DATABASE IF NOT EXISTS `1090024db3`;
USE `1090024db3`;

CREATE TABLE `0cc_ingles_pagos_unificado` (
  `ano_tabla` int(4) NOT NULL COMMENT 'Año de la tabla original para evitar colisiones de ID',
  `ind` int(10) UNSIGNED NOT NULL,
  `cedula` int(11) DEFAULT NULL,
  `mes` tinytext DEFAULT NULL,
  `plan` tinytext DEFAULT NULL,
  `monto` int(11) NOT NULL,
  `dolares` int(11) NOT NULL,
  `zelle` int(11) NOT NULL,
  `recibo` int(11) NOT NULL,
  `fecha` int(11) NOT NULL COMMENT 'Timestamp de la transacción',
  `observacion` tinytext DEFAULT NULL,
  `operador` tinytext DEFAULT NULL,
  -- Clave primaria compuesta para asegurar unicidad absoluta
  PRIMARY KEY (`ano_tabla`, `ind`),
  -- Índices estratégicos para acelerar las consultas frecuentes
  INDEX `idx_cedula` (`cedula`),
  INDEX `idx_mes_fecha` (`mes`(7), `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Insert

```mysql
USE `1090024db3`;

START TRANSACTION;

-- Migración Año 2023
INSERT INTO `0cc_ingles_pagos_unificado` 
(`ano_tabla`, `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT 2023, `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_ingles_pagos_2023`;

-- Migración Año 2024
INSERT INTO `0cc_ingles_pagos_unificado` 
(`ano_tabla`, `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT 2024, `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_ingles_pagos_2024`;

-- Migración Año 2025
INSERT INTO `0cc_ingles_pagos_unificado` 
(`ano_tabla`, `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT 2025, `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_ingles_pagos_2025`;

-- Migración Año 2026
INSERT INTO `0cc_ingles_pagos_unificado` 
(`ano_tabla`, `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT 2026, `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_ingles_pagos_2026`;

COMMIT;
```
