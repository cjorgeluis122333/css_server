## Table

```mysql

CREATE TABLE `1090024db3`.`0cc_karate_pagos` (
                                                 `ind` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                 `cedula` int(11) DEFAULT NULL,
                                                 `mes` varchar(7) DEFAULT NULL,
                                                 `plan` varchar(50) DEFAULT NULL,
                                                 `monto` int(11) NOT NULL DEFAULT 0,
                                                 `dolares` int(11) NOT NULL DEFAULT 0,
                                                 `zelle` int(11) NOT NULL DEFAULT 0,
                                                 `recibo` int(11) NOT NULL DEFAULT 0,
                                                 `fecha` int(11) NOT NULL,
                                                 `observacion` varchar(255) DEFAULT NULL,
                                                 `operador` varchar(50) DEFAULT NULL,
                                                 PRIMARY KEY (`ind`),
                                                 KEY `idx_cedula` (`cedula`),
                                                 KEY `idx_mes` (`mes`),
                                                 KEY `idx_recibo` (`recibo`),
                                                 KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Inserts
```mysql

INSERT INTO `1090024db3`.`0cc_karate_pagos`
(`cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador`)

SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` FROM `1090024db2`.`0cc_karate_pagos`
UNION ALL
SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` FROM `1090024db2`.`0cc_karate_pagos_2023`
UNION ALL
SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` FROM `1090024db2`.`0cc_karate_pagos_2024`
UNION ALL
SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` FROM `1090024db2`.`0cc_karate_pagos_2025`
UNION ALL
SELECT `cedula`, `mes`, `plan`, `monto`, `dolares`, `zelle`, `recibo`, `fecha`, `observacion`, `operador` FROM `1090024db2`.`0cc_karate_pagos_2026`;
```
