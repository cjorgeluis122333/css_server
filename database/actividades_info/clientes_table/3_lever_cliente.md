
## Table

```mysql

USE `1090024db3`;

CREATE TABLE `0cc_lever_clientes` (
  `ind` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` BIGINT(20) NOT NULL,
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
  UNIQUE KEY `idx_cedula_unica` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Insert

```mysql

INSERT IGNORE INTO `1090024db3`.`0cc_lever_clientes` (
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
    `nombre`,
    `nacimiento`,
    `sexo`,
    `socio`,
    `padres`,
    -- 1. Extrae todo lo que está antes del primer '|'
    SUBSTRING_INDEX(`last_pay`, '|', 1) AS `last_pay`,
    
    -- 2. Extrae lo que está entre el primer y el segundo '|'
    CASE 
        WHEN LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 1 
        THEN SUBSTRING_INDEX(SUBSTRING_INDEX(`last_pay`, '|', 2), '|', -1)
        ELSE NULL 
    END AS `last_pay_mont`,
    
    -- 3. Extrae lo que va estrictamente después del segundo '|'
    CASE 
        WHEN LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 2 
        THEN IF(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(`last_pay`, '|', 3), '|', -1)) = '', NULL, TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(`last_pay`, '|', 3), '|', -1)))
        ELSE NULL 
    END AS `d`,
    
    `operador`
FROM `1090024db2`.`0cc_lever_clientes`
WHERE `cedula` IS NOT NULL AND `cedula` > 0;

```
