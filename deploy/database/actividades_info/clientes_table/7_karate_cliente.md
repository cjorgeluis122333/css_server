## Table

```mysql
USE `1090024db3`;

CREATE TABLE `0cc_karate_pagos` (
                                    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                    `ind_original` INT(10) UNSIGNED NOT NULL COMMENT 'ID que tenía en la tabla anual',
                                    `cedula` INT(11) DEFAULT NULL,
                                    `mes` VARCHAR(7) DEFAULT NULL COMMENT 'Formato YYYY-MM',
                                    `plan` VARCHAR(50) DEFAULT NULL,
                                    `monto` INT(11) NOT NULL DEFAULT 0,
                                    `dolares` INT(11) NOT NULL DEFAULT 0,
                                    `zelle` INT(11) NOT NULL DEFAULT 0,
                                    `recibo` INT(11) NOT NULL DEFAULT 0,
                                    `fecha` INT(11) NOT NULL COMMENT 'Timestamp Unix',
                                    `observacion` VARCHAR(255) DEFAULT NULL,
                                    `operador` VARCHAR(50) DEFAULT NULL,
                                    `anio_origen` INT(4) NOT NULL COMMENT 'Año de la tabla de donde vino el dato',
                                    PRIMARY KEY (`id`),
    -- Índices estratégicos para acelerar consultas futuras
                                    KEY `idx_cedula` (`cedula`),
                                    KEY `idx_mes` (`mes`),
                                    KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Inserts
```mysql
USE `1090024db3`;

-- Migración del año 2023
INSERT INTO `0cc_karate_pagos` 
(`ind_original`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, `anio_origen`)
SELECT `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, 2023
FROM `1090024db2`.`0cc_karate_pagos_2023`;

-- Migración del año 2024
INSERT INTO `0cc_karate_pagos` 
(`ind_original`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, `anio_origen`)
SELECT `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, 2024
FROM `1090024db2`.`0cc_karate_pagos_2024`;

-- Migración del año 2025
INSERT INTO `0cc_karate_pagos` 
(`ind_original`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, `anio_origen`)
SELECT `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, 2025
FROM `1090024db2`.`0cc_karate_pagos_2025`;

-- Migración del año 2026
INSERT INTO `0cc_karate_pagos` 
(`ind_original`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, `anio_origen`)
SELECT `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`, 2026
FROM `1090024db2`.`0cc_karate_pagos_2026`;
```
