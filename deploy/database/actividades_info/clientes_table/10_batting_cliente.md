
## Table

```mysql
CREATE DATABASE IF NOT EXISTS `1090024db3`;
USE `1090024db3`;

CREATE TABLE `0cc_batting_clientes` (
  `ind` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` int(11) NOT NULL, -- Cambiado a NOT NULL para garantizar la restricción UNIQUE de forma óptima
  `nombre` tinytext DEFAULT NULL,
  `nacimiento` tinytext DEFAULT NULL,
  `sexo` tinytext DEFAULT NULL,
  `socio` tinytext DEFAULT 'No Socio',
  `padres` text DEFAULT NULL,
  `last_pay` tinytext DEFAULT NULL,      -- Guardará el número (ej: 488.80)
  `last_pay_mont` tinytext DEFAULT NULL, -- Guardará el mes (ej: 2025-08)
  `d` tinytext DEFAULT NULL,             -- Guardará la descripción o NULL (ej: D7)
  `operador` tinytext DEFAULT NULL,
  PRIMARY KEY (`ind`),
  UNIQUE KEY `idx_cedula_unique` (`cedula`) -- Evita que se repitan las cédulas
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

```

## Instert

```mysql

INSERT INTO `1090024db3`.`0cc_batting_clientes` 
(
    `cedula`, 
    `nombre`, 
    `nacimiento`, 
    `sexo`, 
    `socio`, 
    `padres`, 
    `last_pay`, 
    `last_pay_mont`, 
    `d`, 
    `operador`
)
SELECT 
    `cedula`,
    MIN(`nombre`), -- En caso de duplicados, toma un nombre (puedes usar MAX o MIN)
    MIN(`nacimiento`),
    MIN(`sexo`),
    MIN(`socio`),
    MIN(`padres`),
    -- 1. Obtiene lo que está antes del primer '|'
    SUBSTRING_INDEX(`last_pay`, '|', 1) AS last_pay,
    
    -- 2. Obtiene lo que está entre el primer y segundo '|'
    IF(
        INSTR(`last_pay`, '|') > 0, 
        SUBSTRING_INDEX(SUBSTRING_INDEX(`last_pay`, '|', 2), '|', -1), 
        NULL
    ) AS last_pay_mont,
    
    -- 3. Obtiene lo que está después del segundo '|' si existe y no está vacío
    IF(
        LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 2, 
        IF(SUBSTRING_INDEX(`last_pay`, '|', -1) = '', NULL, SUBSTRING_INDEX(`last_pay`, '|', -1)), 
        NULL
    ) AS d,
    
    MIN(`operador`)
FROM `1090024db2`.`0cc_batting_clientes`
WHERE `cedula` IS NOT NULL
GROUP BY `cedula`; -- Elimina los duplicados de cédula dejando solo un registro único por persona


```
