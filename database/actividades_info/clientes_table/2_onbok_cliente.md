### Create new table
```mysql

CREATE TABLE `1090024db3`.`0cc_onbox_clientes` (
  `ind` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` bigint(20) NOT NULL, -- <-- Cambiado de INT a BIGINT para soportar números grandes
  `nombre` tinytext DEFAULT NULL,
  `nacimiento` tinytext NOT NULL,
  `sexo` tinytext DEFAULT NULL,
  `socio` tinytext DEFAULT 'No Socio',
  `padres` text NOT NULL,
  `last_pay` tinytext DEFAULT NULL,
  `last_pay_mont` tinytext DEFAULT NULL,
  `d` tinytext DEFAULT NULL,
  `operador` tinytext NOT NULL,
  PRIMARY KEY (`ind`),
  UNIQUE KEY `idx_cedula_unico` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```



### Insert info

```mysql

INSERT IGNORE INTO `1090024db3`.`0cc_onbox_clientes` (
    `cedula`, `nombre`, `nacimiento`, `sexo`, `socio`, `padres`, `last_pay`, `last_pay_mont`, `d`, `operador`
)
SELECT 
    `cedula`, `nombre`, `nacimiento`, `sexo`, `socio`, `padres`,
    SUBSTRING_INDEX(`last_pay`, '|', 1) AS `last_pay`,
    CASE 
        WHEN LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 1 
        THEN SUBSTRING_INDEX(SUBSTRING_INDEX(`last_pay`, '|', 2), '|', -1)
        ELSE NULL 
    END AS `last_pay_mont`,
    CASE 
        WHEN LENGTH(`last_pay`) - LENGTH(REPLACE(`last_pay`, '|', '')) >= 2 
        THEN NULLIF(TRIM(SUBSTRING_INDEX(`last_pay`, '|', -1)), '')
        ELSE NULL 
    END AS `d`,
    `operador`
FROM `1090024db2`.`0cc_onbox_clientes`
WHERE `cedula` IS NOT NULL;

```
