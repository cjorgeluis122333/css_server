```mysql

CREATE DATABASE IF NOT EXISTS `1090024db3`;
USE `1090024db3`;

CREATE TABLE `0cc_strong_clientes` (
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
  PRIMARY KEY (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

```


```mysql
INSERT INTO `1090024db3`.`0cc_strong_clientes` (
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
    -- 1. Parte antes del primer '|'
    NULLIF(SUBSTRING_INDEX(`last_pay`, '|', 1), '') AS `last_pay`,
    
    -- 2. Parte entre el primer y segundo '|'
    NULLIF(
        IF(
            LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 1,
            SUBSTRING_INDEX(SUBSTRING_INDEX(`last_pay`, '|', 2), '|', -1),
            NULL
        ), 
        ''
    ) AS `last_pay_mont`,
    
    -- 3. Parte después del segundo '|' (Columna d)
    NULLIF(
        IF(
            LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 2,
            SUBSTRING_INDEX(`last_pay`, '|', -1),
            NULL
        ), 
        ''
    ) AS `d`,
    
    `operador`
FROM `1090024db2`.`0cc_strong_clientes`
WHERE `cedula` IS NOT NULL
GROUP BY `cedula`;
```
