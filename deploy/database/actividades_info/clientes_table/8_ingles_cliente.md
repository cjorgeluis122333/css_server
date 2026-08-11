## Table

```mysql
CREATE DATABASE IF NOT EXISTS `1090024db3`;
USE `1090024db3`;

CREATE TABLE `0cc_ingles_clientes` (
  `ind` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` int(11) NOT NULL,
  `nombre` tinytext DEFAULT NULL,
  `nacimiento` tinytext DEFAULT NULL,
  `sexo` tinytext DEFAULT NULL,
  `socio` tinytext DEFAULT 'No Socio',
  `padres` text DEFAULT NULL,
  `last_pay` tinytext DEFAULT NULL,
  `last_pay_mont` tinytext DEFAULT NULL,
  `d` tinytext DEFAULT NULL,
  `operador` tinytext DEFAULT NULL,
  PRIMARY KEY (`ind`),
  UNIQUE KEY `idx_cedula_unico` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

```

## Insert

```mysql


INSERT INTO `1090024db3`.`0cc_ingles_clientes` (
    `cedula`, `nombre`, `nacimiento`, `sexo`, `socio`, `padres`, `operador`,
    `last_pay`, `last_pay_mont`, `d`
)
SELECT 
    old.`cedula`,
    old.`nombre`,
    old.`nacimiento`,
    old.`sexo`,
    old.`socio`,
    old.`padres`,
    old.`operador`,
    -- 1. Extraer lo que está antes del primer '|'
    NULLIF(SUBSTRING_INDEX(old.`last_pay`, '|', 1), '') AS `last_pay`,
    
    -- 2. Extraer lo que está entre el primer y segundo '|'
    CASE 
        WHEN LENGTH(old.`last_pay`) - LENGTH(REPLACE(old.`last_pay`, '|', '')) >= 1 
        THEN NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX(old.`last_pay`, '|', 2), '|', -1), '')
        ELSE NULL 
    END AS `last_pay_mont`,
    
    -- 3. Extraer lo que está después del segundo '|' (Columna d)
    CASE 
        WHEN LENGTH(old.`last_pay`) - LENGTH(REPLACE(old.`last_pay`, '|', '')) >= 2 
        THEN NULLIF(SUBSTRING_INDEX(old.`last_pay`, '|', -1), '')
        ELSE NULL 
    END AS `d`
FROM `1090024db2`.`0cc_ingles_clientes` old
-- Unirse a sí mismo para asegurar que si hay cédulas repetidas, se tome el ID más alto (último ingresado)
WHERE old.`ind` IN (
    SELECT MAX(`ind`) 
    FROM `1090024db2`.`0cc_ingles_clientes`
    WHERE `cedula` IS NOT NULL
    GROUP BY `cedula`
);

```
