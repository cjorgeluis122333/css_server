## TABLE

```mysql

USE `1090024db3`;

CREATE TABLE `0cc_voleibol_clientes` (
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

## INSERT

```mysql
INSERT INTO `1090024db3`.`0cc_voleibol_clientes` (
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
    c.`cedula`,
    c.`nombre`,
    c.`nacimiento`,
    c.`sexo`,
    c.`socio`,
    c.`padres`,
    -- 1. Lo que está antes del primer '|'
    SUBSTRING_INDEX(c.`last_pay`, '|', 1) AS last_pay,
    
    -- 2. Lo que está entre el primer y segundo '|'
    NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX(c.`last_pay`, '|', 2), '|', -1), '') AS last_pay_mont,
    
    -- 3. Lo que está después del segundo '|' (si no existe o está vacío, será NULL)
    CASE 
        WHEN LENGTH(c.`last_pay`) - LENGTH(REPLACE(c.`last_pay`, '|', '')) >= 2 THEN 
            NULLIF(SUBSTRING_INDEX(c.`last_pay`, '|', -1), '')
        ELSE NULL 
    END AS d,
    
    c.`operador`
FROM `1090024db2`.`0cc_voleibol_clientes` c
WHERE c.`cedula` IS NOT NULL
  -- Filtramos para quedarnos con el registro más reciente ('ind' más alto) de cada cédula repetida
  AND c.`ind` = (
      SELECT MAX(orig.`ind`) 
      FROM `1090024db2`.`0cc_voleibol_clientes` orig 
      WHERE orig.`cedula` = c.`cedula`
  );

```
