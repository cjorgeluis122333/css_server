## TABLE

```mysql
-- Crear la tabla unificada en la base de datos de destino
CREATE TABLE `1090024db3`.`0cc_voleibol_pagos_unificado` (
  `ind` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` INT(11) DEFAULT NULL,
  `mes` VARCHAR(10) DEFAULT NULL,         -- Optimizado de tinytext a varchar
  `plan` VARCHAR(50) DEFAULT NULL,        -- Optimizado de tinytext a varchar
  `monto` INT(11) NOT NULL,
  `dolares` INT(11) NOT NULL,
  `zelle` INT(11) NOT NULL,
  `recibo` INT(11) NOT NULL,
  `fecha` INT(11) NOT NULL,               -- Almacena timestamp unix
  `observacion` VARCHAR(255) DEFAULT NULL, -- Optimizado de tinytext a varchar
  `operador` VARCHAR(50) DEFAULT NULL,    -- Optimizado de tinytext a varchar
  `ano_origen` INT(4) NOT NULL,           -- Columna de control para identificar el año de la tabla origen
  PRIMARY KEY (`ind`),
  -- ÍNDICES DE VELOCIDAD OPTIMIZADOS
  KEY `idx_cedula_mes` (`cedula`, `mes`), -- Agiliza búsquedas de estados de cuenta de clientes
  KEY `idx_fecha` (`fecha`),              -- Agiliza búsquedas por rangos de fechas/años
  KEY `idx_mes` (`mes`)                   -- Agiliza reportes financieros mensuales generales
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```


## INSERT
```mysql
START TRANSACTION;

-- Migración del año 2023
INSERT INTO `1090024db3`.`0cc_voleibol_pagos_unificado` 
(`cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, `ano_origen`)
SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, 2023
FROM `1090024db2`.`0cc_voleibol_pagos_2023`;

-- Migración del año 2024
INSERT INTO `1090024db3`.`0cc_voleibol_pagos_unificado` 
(`cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, `ano_origen`)
SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, 2024
FROM `1090024db2`.`0cc_voleibol_pagos_2024`;

-- Migración del año 2025
INSERT INTO `1090024db3`.`0cc_voleibol_pagos_unificado` 
(`cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, `ano_origen`)
SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, 2025
FROM `1090024db2`.`0cc_voleibol_pagos_2025`;

-- Migración del año 2026
INSERT INTO `1090024db3`.`0cc_voleibol_pagos_unificado` 
(`cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, `ano_origen`)
SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, 2026
FROM `1090024db2`.`0cc_voleibol_pagos_2026`;

COMMIT;
```
