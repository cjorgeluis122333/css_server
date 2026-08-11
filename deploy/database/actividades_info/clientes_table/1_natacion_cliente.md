
## Tabla

```mysql

CREATE TABLE `1090024db3`.`0cc_natacion_clientes` (
  `ind` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `nacimiento` varchar(50) DEFAULT NULL,
  `sexo` varchar(10) DEFAULT NULL,
  `socio` varchar(50) DEFAULT 'No Socio',
  `padres` text DEFAULT NULL,
  `repre_cedula1` int(11) DEFAULT NULL,
  `repre_nombre1` varchar(255) DEFAULT NULL,
  `repre_cedula2` int(11) DEFAULT NULL,
  `repre_nombre2` varchar(255) DEFAULT NULL,
  `repre_cedula3` int(11) DEFAULT NULL,
  `repre_nombre3` varchar(255) DEFAULT NULL,
  `last_pay` varchar(50) DEFAULT NULL,
  `last_pay_mont` varchar(50) DEFAULT NULL,
  `operador` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ind`),
  UNIQUE KEY `uq_cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

```

## INSERT
```mysql

INSERT INTO `1090024db3`.`0cc_natacion_clientes` (
  `ind`, 
  `cedula`, 
  `nombre`, 
  `nacimiento`, 
  `sexo`, 
  `socio`, 
  `padres`, 
  `repre_cedula1`, 
  `repre_nombre1`, 
  `repre_cedula2`, 
  `repre_nombre2`, 
  `repre_cedula3`, 
  `repre_nombre3`, 
  `last_pay`, 
  `last_pay_mont`, 
  `operador`
)
SELECT 
  c.`ind`,
  IFNULL(c.`cedula`, 0), -- Si es NULL, lo convierte en 0 para cumplir con el NOT NULL de la nueva tabla
  c.`nombre`, 
  c.`nacimiento`, 
  c.`sexo`, 
  c.`socio`, 
  c.`padres`, 
  c.`repre_cedula1`, 
  c.`repre_nombre1`, 
  c.`repre_cedula2`, 
  c.`repre_nombre2`, 
  c.`repre_cedula3`, 
  c.`repre_nombre3`, 
  NULLIF(TRIM(SUBSTRING_INDEX(c.`last_pay`, '|', 1)), ''),
  NULLIF(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(c.`last_pay`, '|', 2), '|', -1)), ''),
  c.`operador`
FROM `1090024db2`.`0cc_natacion_clientes` c
WHERE c.`ind` IN (
    -- Agrupamos directamente por cédula sin discriminar los valores 0 o NULL
    SELECT MAX(sub.`ind`) 
    FROM `1090024db2`.`0cc_natacion_clientes` sub 
    GROUP BY sub.`cedula`
);
```
