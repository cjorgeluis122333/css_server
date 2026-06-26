```mysql
CREATE TABLE `1090024db3`.`0cc_batting_pagos_unificada` (
  `ind` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` INT(11) DEFAULT NULL,
  `mes` VARCHAR(7) DEFAULT NULL COMMENT 'Almacena estrictamente YYYY-MM',
  `d` VARCHAR(10) DEFAULT NULL COMMENT 'Almacena el código (D7, D4, S1) o NULL si no existe',
  `plan` VARCHAR(50) DEFAULT NULL,
  `monto` INT(11) NOT NULL,
  `dolares` INT(11) NOT NULL,
  `zelle` INT(11) NOT NULL,
  `recibo` INT(11) NOT NULL,
  `fecha` INT(11) NOT NULL COMMENT 'Timestamp de la transacción',
  `observacion` VARCHAR(255) DEFAULT NULL,
  `operador` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`ind`),
  -- Índices para mejorar la velocidad de las consultas
  KEY `idx_cedula` (`cedula`),
  KEY `idx_mes_d` (`mes`, `d`),
  KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```


```mysql
INSERT INTO `1090024db3`.`0cc_batting_pagos_unificada` 
(
  `cedula`, `mes`, `d`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
)
SELECT 
  `cedula`,
  SUBSTRING_INDEX(`mes`, '|', 1) AS `mes`,
  IF(`mes` LIKE '%|%', SUBSTRING_INDEX(`mes`, '|', -1), NULL) AS `d`,
  `plan`, 
  `monto`, 
  `dolares`, 
  `zelle`, 
  `recibo`, 
  `fecha`, 
  `observacion`, 
  `operador`
FROM (
  -- Unión de todas las tablas anuales de la base de datos origen
  SELECT * FROM `1090024db2`.`0cc_batting_pagos_2025`
  UNION ALL
  SELECT * FROM `1090024db2`.`0cc_batting_pagos_2026`
) AS `tablas_anuales`;
```
