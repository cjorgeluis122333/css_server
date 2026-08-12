

## Historial Pagos


### Paso 1

```mysql
SELECT CONCAT(
    'INSERT IGNORE INTO historial_pagos_unificado (acc, `time`, fecha, mes, oper, monto, descript, seniat, operador) ',
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

Esto me da un texto que esta en @history_sq;.txt


### Paso2 Insertar de historial_pagos_unificado de la base de datos 2 al la base de datos 3

```mysql
INSERT INTO `1090024db3`.historial_pagos_unificado 
SELECT * FROM `1090024db2`.historial_pagos_unificado;
```

### Paso 3

```mysql
INSERT INTO `historial_pagos_separado` (
`ind`, `acc`, `time`, `fecha`, `mes`, `oper`, `resibo`, `control`, `factura`,`monto`, `descript`, `observaciones`,`seniat`, `operador`
)
SELECT
`ind`,
`acc`,
`time`,
`fecha`,
`mes`,

    -- Limpiamos la operación por si también tiene espacios
    TRIM(SUBSTRING_INDEX(`oper`, '|', 1)) AS `oper`,
    
    -- Limpiamos la factura: extraemos y quitamos espacios
    CASE 
        WHEN LOCATE('|', `oper`) > 0 
        THEN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(`oper`, '|', 2), '|', -1)) 
        ELSE NULL 
    END AS `resibo`,
    
    -- Limpiamos el control: extraemos y quitamos espacios
    CASE 
        WHEN LENGTH(`oper`) - LENGTH(REPLACE(`oper`, '|', '')) >= 2 
        THEN TRIM(SUBSTRING_INDEX(`oper`, '|', -1)) 
        ELSE NULL 
    END AS `control`,
    null,
    `monto`,
    `descript`,
    null,
    `seniat`,
    `operador`
FROM `historial_pagos_unificado`;
```

## ===================================== Invitados Unificados

Copiar lo que se encuentra dentro de  @h_invitados.txt 
Insertas la consulta masiva y luego pasas de una base de datos a otra con

