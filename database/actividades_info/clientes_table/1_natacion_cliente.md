## Tabla
```mysql
CREATE TABLE 1090024db3.`0cc_natacion_pagos` (
  `ind` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` INT(11) DEFAULT NULL,
  `anio` INT(4) NOT NULL, -- Columna clave para identificar el origen tras la unificación
  `mes` TINYTEXT DEFAULT NULL,
  `plan` TINYTEXT DEFAULT NULL,
  `monto` INT(11) NOT NULL,
  `dolares` INT(11) NOT NULL,
  `zelle` INT(11) NOT NULL,
  `recibo` INT(11) NOT NULL,
  `fecha` INT(11) NOT NULL,
  `observacion` TINYTEXT DEFAULT NULL,
  `operador` TINYTEXT DEFAULT NULL,
  PRIMARY KEY (`ind`),
  -- Índices para maximizar la velocidad de lectura con millones de filas
  INDEX `idx_cedula_anio` (`cedula`, `anio`),
  INDEX `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Get data of refactor tables
```mysql
INSERT INTO 1090024db3.`0cc_natacion_pagos` 
(`cedula`, `anio`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)

SELECT `cedula`, 2022, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM 1090024db2.`0cc_natacion_pagos_2022`

UNION ALL

SELECT `cedula`, 2023, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM 1090024db2.`0cc_natacion_pagos_2023`

UNION ALL

SELECT `cedula`, 2024, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM 1090024db2.`0cc_natacion_pagos_2024`

UNION ALL

SELECT `cedula`, 2025, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM 1090024db2.`0cc_natacion_pagos_2025`

UNION ALL

SELECT `cedula`, 2026, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`
FROM 1090024db2.`0cc_natacion_pagos_2026`;
```
