## Table

```mysql

CREATE TABLE `1090024db3`.`0cc_karate_clientes` (
  `ind` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `idx_cedula_unique` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

```

## Inserts
```mysql

INSERT INTO `1090024db3`.`0cc_karate_clientes` (
    `cedula`, `nombre`, `nacimiento`, `sexo`, `socio`, `padres`, `last_pay`, `last_pay_mont`, `d`, `operador`
)
SELECT 
    `cedula`,
    MAX(`nombre`) AS `nombre`,
    MAX(`nacimiento`) AS `nacimiento`,
    MAX(`sexo`) AS `sexo`,
    MAX(`socio`) AS `socio`,
    MAX(`padres`) AS `padres`,
    -- 1. Lo que va antes del primer '|'
    NULLIF(SUBSTRING_INDEX(`last_pay`, '|', 1), '') AS `last_pay`,
    
    -- 2. Lo que va entre el primer y el segundo '|'
    CASE 
        WHEN `last_pay` LIKE '%|%|%' THEN NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX(`last_pay`, '|', 2), '|', -1), '')
        WHEN `last_pay` LIKE '%|%' THEN NULLIF(SUBSTRING_INDEX(`last_pay`, '|', -1), '')
        ELSE NULL
    END AS `last_pay_mont`,
    
    -- 3. Lo que va después del segundo '|' (columna d)
    CASE 
        WHEN `last_pay` LIKE '%|%|%' THEN NULLIF(SUBSTRING_INDEX(`last_pay`, '|', -1), '')
        ELSE NULL
    END AS `d`,
    
    MAX(`operador`) AS `operador`
FROM `1090024db2`.`0cc_karate_clientes`
WHERE `cedula` IS NOT NULL
GROUP BY `cedula`;

```
