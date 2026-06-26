

```mysql
CREATE TABLE `1090024db3`.`0cc_lever_pagos_unificado` (
  `id_pago` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` INT(11) DEFAULT NULL,
  `mes` VARCHAR(7) DEFAULT NULL COMMENT 'Almacena estrictamente YYYY-MM',
  `d` VARCHAR(10) DEFAULT NULL COMMENT 'Almacena el código (D7, D4, S1) o NULL si no existe',
  `plan` VARCHAR(100) DEFAULT NULL,
  `monto` DECIMAL(11,2) NOT NULL DEFAULT 0.00,
  `dolares` DECIMAL(11,2) NOT NULL DEFAULT 0.00,
  `zelle` DECIMAL(11,2) NOT NULL DEFAULT 0.00,
  `recibo` INT(11) NOT NULL,
  `fecha` INT(11) NOT NULL COMMENT 'Almacena fecha en formato Unix Timestamp',
  `observacion` VARCHAR(255) DEFAULT NULL,
  `operador` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id_pago`),
  -- Índices de Rendimiento
  INDEX `idx_cedula` (`cedula`),
  INDEX `idx_mes_d` (`mes`, `d`),
  INDEX `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```



```mysql
INSERT INTO `1090024db3`.`0cc_lever_pagos_unificado` 
(cedula, mes, d, plan, monto, dolares, zelle, recibo, fecha, observacion, operador)
SELECT 
    cedula,
    SUBSTRING_INDEX(mes, '|', 1) AS mes,
    IF(mes LIKE '%|%', SUBSTRING_INDEX(mes, '|', -1), NULL) AS d,
    plan,
    monto,
    dolares,
    zelle,
    recibo,
    fecha,
    observacion,
    operador
FROM (
    SELECT * FROM `1090024db2`.`0cc_lever_pagos_2022`
    UNION ALL
    SELECT * FROM `1090024db2`.`0cc_lever_pagos_2023`
    UNION ALL
    SELECT * FROM `1090024db2`.`0cc_lever_pagos_2024`
    UNION ALL
    SELECT * FROM `1090024db2`.`0cc_lever_pagos_2025`
    UNION ALL
    SELECT * FROM `1090024db2`.`0cc_lever_pagos_2026`
) AS tablas_anuales;
```
