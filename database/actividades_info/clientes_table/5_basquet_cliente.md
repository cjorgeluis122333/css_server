## Table

```mysql
CREATE TABLE `1090024db3`.`0cc_basquet_clientes` (
  `ind` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` int(11) NOT NULL,
  `nombre` tinytext DEFAULT NULL,
  `nacimiento` tinytext DEFAULT NULL,
  `sexo` tinytext DEFAULT NULL,
  `socio` tinytext DEFAULT 'No Socio',
  `padres` text NOT NULL,
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
INSERT INTO `1090024db3`.`0cc_basquet_clientes` (
    `cedula`, `nombre`, `nacimiento`, `sexo`, `socio`, `padres`, `operador`, `last_pay`, `last_pay_mont`, `d`
)
SELECT 
    `cedula`,
    MAX(`nombre`) AS `nombre`,
    MAX(`nacimiento`) AS `nacimiento`,
    MAX(`sexo`) AS `sexo`,
    MAX(`socio`) AS `socio`,
    MAX(`padres`) AS `padres`,
    MAX(`operador`) AS `operador`,
    -- 1. Extrae lo que está antes del primer '|'
    NULLIF(SUBSTRING_INDEX(`last_pay`, '|', 1), '') AS `last_pay`,
    
    -- 2. Extrae lo que está entre el primer y segundo '|'
    NULLIF(
        IF(
            LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 1,
            SUBSTRING_INDEX(SUBSTRING_INDEX(`last_pay`, '|', 2), '|', -1),
            NULL
        ), ''
    ) AS `last_pay_mont`,
    
    -- 3. Extrae lo que está después del segundo '|' (columna d). Si no hay o está vacío, devuelve NULL.
    NULLIF(
        IF(
            LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 2,
            SUBSTRING_INDEX(`last_pay`, '|', -1),
            NULL
        ), ''
    ) AS `d`
FROM `1090024db2`.`0cc_basquet_clientes`
WHERE `cedula` IS NOT NULL
GROUP BY `cedula`;
```
