## Table
```mysql

-- Ejecutar en la base de datos: 1090024db3

CREATE TABLE `0cc_almaflamenca_clientes` (
  `ind` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cedula` int(11) NOT NULL, -- Cambiado a NOT NULL para asegurar la integridad del índice único
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
  UNIQUE KEY `idx_cedula_unico` (`cedula`) -- Evita cédulas duplicadas
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 
-- Se recomienda InnoDB en lugar de MyISAM para mejor manejo de restricciones y transacciones

```


## Insert

```mysql

-- Ejecutar posicionándote o apuntando a la nueva base de datos 1090024db3

INSERT INTO `1090024db3`.`0cc_almaflamenca_clientes` 
(
  `cedula`, `nombre`, `nacimiento`, `sexo`, `socio`, `padres`, 
  `last_pay`, `last_pay_mont`, `d`, `operador`
)
SELECT 
  orig.`cedula`,
  orig.`nombre`,
  orig.`nacimiento`,
  orig.`sexo`,
  orig.`socio`,
  orig.`padres`,
  
  -- 1. Extrae lo que está antes del primer '|'
  NULLIF(SUBSTRING_INDEX(orig.`last_pay`, '|', 1), '') AS last_pay,
  
  -- 2. Extrae lo que está entre el primer y segundo '|'
  NULLIF(
    SUBSTRING_INDEX(SUBSTRING_INDEX(orig.`last_pay`, '|', 2), '|', -1), 
    SUBSTRING_INDEX(orig.`last_pay`, '|', 1)
  ) AS last_pay_mont,
  
  -- 3. Extrae la descripción (columna d) si existe un segundo '|' con contenido
  CASE 
    -- Si no hay un segundo '|', o termina en '|', guardamos NULL
    WHEN LENGTH(orig.`last_pay`) - LENGTH(REPLACE(orig.`last_pay`, '|', '')) < 2 THEN NULL
    WHEN RIGHT(orig.`last_pay`, 1) = '|' THEN NULL
    -- De lo contrario, extraemos el tercer elemento
    ELSE NULLIF(TRIM(SUBSTRING_INDEX(orig.`last_pay`, '|', -1)), '')
  END AS d,
  
  orig.`operador`
FROM `1090024db2`.`0cc_almaflamenca_clientes` orig
WHERE orig.`cedula` IS NOT NULL
-- Agrupamos por cédula para eliminar los duplicados de la tabla antigua. 
-- El uso de ANY_VALUE() asegura compatibilidad si el modo ONLY_FULL_GROUP_BY está activo.
GROUP BY orig.`cedula`;

```
