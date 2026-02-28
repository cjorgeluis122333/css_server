## Ejecuta la transferencia "Mirror"
Inserta todo lo de una base de datos en otra 
```mysql
   INSERT INTO `1090024db3`.`historial_pagos_unificado` 
        (`acc`, `time`, `fecha`, `mes`, `oper`, `monto`, `descript`, `seniat`, `operador`)
   SELECT 
        `acc`, `time`, `fecha`, `mes`, `oper`, `monto`, `descript`, `seniat`, `operador` 
   FROM `1090024db2`.`historial_pagos_unificado`;
```



## Crear una tabla que va a ser la union de mil tablas
```mysql
SELECT CONCAT(
    'INSERT INTO historial_pagos_unificado (acc, `time`, fecha, mes, oper, monto, descript, seniat, operador) ',
    'SELECT ', REPLACE(table_name, '0history_', ''), ', `time`, fecha, mes, oper, monto, descript, seniat, operador ',
    'FROM `', table_name, '`;'
) AS consulta_generada
FROM information_schema.tables
WHERE table_schema = '1090024db2' 
AND table_name LIKE '0history_%';
```


## Crear una tabla que va a ser la union de mil tablas(En caso de no tener la columna seniat ponerle un ***no***)
```mysql
SELECT CONCAT(
    'INSERT INTO historial_pagos_unificado (acc, `time`, fecha, mes, oper, monto, descript, seniat, operador) ',
    'SELECT ', REPLACE(t.table_name, '0history_', ''), ', `time`, fecha, mes, oper, monto, descript, ',
    IF(c.column_name IS NULL, '''no''', 'seniat'), ', operador ',
    'FROM `', t.table_name, '`;'
) AS consulta_generada
FROM information_schema.tables t
LEFT JOIN information_schema.columns c 
    ON t.table_schema = c.table_schema 
    AND t.table_name = c.table_name 
    AND c.column_name = 'seniat'
WHERE t.table_schema = '1090024db2' 
AND t.table_name LIKE '0history_%';
```


## Detectar todas las tablas que les falta la columna seniat
```mysql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = '1090024db2' 
AND table_name LIKE '0history_%'
AND table_name NOT IN (
    SELECT table_name 
    FROM information_schema.columns 
    WHERE column_name = 'seniat' 
    AND table_schema = '1090024db2'
);
```

## Tabla historial_pagos_unificado
```mysql
CREATE TABLE `historial_pagos_unificado` (
  `ind` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `acc` INT NOT NULL,
  `time` VARCHAR(50) DEFAULT NULL,
  `fecha` VARCHAR(20) DEFAULT NULL,
  `mes` VARCHAR(20) DEFAULT NULL,
  `oper` TEXT DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0.00,
  `descript` TEXT DEFAULT NULL,
  `seniat` VARCHAR(100) DEFAULT 'no',
  `operador` TEXT DEFAULT NULL,
  PRIMARY KEY (`ind`),
  INDEX (`acc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```




