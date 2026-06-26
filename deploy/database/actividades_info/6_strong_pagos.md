```mysql
CREATE TABLE `0cc_strong_pagos_unificada` (
  `id_global` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ind_original` INT(10) UNSIGNED NOT NULL COMMENT 'ID que tenía en su tabla anual',
  `ano` SMALLINT(4) UNSIGNED NOT NULL COMMENT 'Año extraído de la tabla origen',
  `cedula` INT(11) DEFAULT NULL,
  `mes` VARCHAR(20) DEFAULT NULL,
  `plan` VARCHAR(50) DEFAULT NULL,
  `monto` INT(11) NOT NULL,
  `dolares` INT(11) NOT NULL,
  `zelle` INT(11) NOT NULL,
  `recibo` INT(11) NOT NULL,
  `fecha` INT(11) NOT NULL COMMENT 'Timestamp Unix',
  `observacion` TEXT DEFAULT NULL,
  `operador` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id_global`),
  -- Índices para mejorar la velocidad de las consultas (Queries)
  KEY `idx_cedula_ano` (`cedula`, `ano`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_recibo` (`recibo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```


```mysql
INSERT INTO `1090024db3`.`0cc_strong_pagos_unificada` 
(`ind_original`, `ano`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)

SELECT `ind`, 2022, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_strong_pagos_2022`

UNION ALL

SELECT `ind`, 2023, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_strong_pagos_2023`

UNION ALL

SELECT `ind`, 2024, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_strong_pagos_2024`

UNION ALL

SELECT `ind`, 2025, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_strong_pagos_2025`

UNION ALL

SELECT `ind`, 2026, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_strong_pagos_2026`;
```
