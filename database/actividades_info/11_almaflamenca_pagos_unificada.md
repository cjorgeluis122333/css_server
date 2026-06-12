## Table
```mysql
CREATE TABLE `0cc_almaflamenca_pagos_unificada` (
  `id_pago` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ind_original` int(10) UNSIGNED DEFAULT NULL, -- Guarda el 'ind' de la tabla vieja por seguridad
  `cedula` int(11) DEFAULT NULL,
  `mes` varchar(7) DEFAULT NULL, -- Optimizado: 'YYYY-MM' ocupa max 7 caracteres, mejor que tinytext
  `plan` varchar(50) DEFAULT NULL, -- Optimizado: varchar es más eficiente para indexar que tinytext
  `monto` int(11) NOT NULL,
  `dolares` int(11) NOT NULL,
  `zelle` int(11) NOT NULL,
  `recibo` int(11) NOT NULL,
  `fecha` int(11) NOT NULL, -- Almacena el Timestamp Unix original
  `observacion` tinytext DEFAULT NULL,
  `operador` varchar(50) DEFAULT NULL, -- Optimizado de tinytext a varchar
  PRIMARY KEY (`id_pago`),
  -- Índices para mejorar la velocidad de las consultas frecuentes:
  KEY `idx_cedula` (`cedula`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_recibo` (`recibo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```


## Insert

```mysql
INSERT INTO `1090024db3`.`0cc_almaflamenca_pagos_unificada` 
  (`ind_original`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)
SELECT `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_almaflamenca_pagos_2025`

UNION ALL

SELECT `ind`, `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` 
FROM `1090024db2`.`0cc_almaflamenca_pagos_2026`;
```
