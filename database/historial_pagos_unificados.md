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




# Despues extraer todo los historiales y almancenarlos en una sola tabla pasamos :

## Paso1: Crear una nueva tabla 
La tabla anterior tine un problema y es que pose una columna llamada oper que contine los datos de tres columnas en una sola
- Ejemplo: oper -> pago|213113|32312 
Como puedes apreciar en esa columna esta: {operaction}|{factura}|{control_de_pago}

```mysql
CREATE TABLE `historial_pagos_separado` (
  `ind` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `acc` INT NOT NULL,
  `time` VARCHAR(50) DEFAULT NULL,
  `fecha` VARCHAR(20) DEFAULT NULL,
  `mes` VARCHAR(20) DEFAULT NULL,
  `oper` TEXT DEFAULT NULL,
  `factura` VARCHAR(50) DEFAULT NULL,
  `control` VARCHAR(50) DEFAULT NULL,
  `monto` DECIMAL(15,2) DEFAULT 0.00,
  `descript` TEXT DEFAULT NULL,
  `seniat` VARCHAR(100) DEFAULT 'no',
  `operador` TEXT DEFAULT NULL,
  PRIMARY KEY (`ind`),
  INDEX (`acc`),
  INDEX (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Paso2(No tan recomendado): Crear una consulta Sql que separe toda esta informacion

```mysql
INSERT INTO `historial_pagos_separado` (
    `ind`, `acc`, `time`, `fecha`, `mes`, `oper`, `factura`, `control`, `monto`, `descript`, `seniat`, `operador`
)
SELECT 
    `ind`, 
    `acc`, 
    `time`, 
    `fecha`, 
    `mes`, 
    
    -- 1. Operación: Toma todo lo que esté antes del primer '|'
    SUBSTRING_INDEX(`oper`, '|', 1) AS `oper`,
    
    -- 2. Factura: Si hay al menos un '|', toma el valor entre el primer y segundo '|'. Si no, es NULL.
    CASE 
        WHEN LOCATE('|', `oper`) > 0 
        THEN SUBSTRING_INDEX(SUBSTRING_INDEX(`oper`, '|', 2), '|', -1) 
        ELSE NULL 
    END AS `factura`,
    
    -- 3. Control: Cuenta cuántos '|' hay. Si hay 2 o más, toma todo después del último '|'. Si no, es NULL.
    CASE 
        WHEN LENGTH(`oper`) - LENGTH(REPLACE(`oper`, '|', '')) >= 2 
        THEN SUBSTRING_INDEX(`oper`, '|', -1) 
        ELSE NULL 
    END AS `control`,
    
    `monto`, 
    `descript`, 
    `seniat`, 
    `operador`
FROM `historial_pagos_unificado`;
```


## Paso2 (Recomendado): La misma consulta anterior pero utilizando el operador trim  para evitar espacios en blanco

```mysql
-- 1. Primero, asegúrate de que las columnas tengan espacio suficiente por si acaso
ALTER TABLE `historial_pagos_separado` 
MODIFY COLUMN `factura` VARCHAR(255) DEFAULT NULL,
MODIFY COLUMN `control` VARCHAR(255) DEFAULT NULL;

-- 2. Vacía la tabla de destino para reintentar la carga limpia
TRUNCATE TABLE `historial_pagos_separado`;

-- 3. Ejecuta la inserción aplicando TRIM() a cada segmento extraído
INSERT INTO `historial_pagos_separado` (
    `ind`, `acc`, `time`, `fecha`, `mes`, `oper`, `factura`, `control`, `monto`, `descript`, `seniat`, `operador`
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
    END AS `factura`,
    
    -- Limpiamos el control: extraemos y quitamos espacios
    CASE 
        WHEN LENGTH(`oper`) - LENGTH(REPLACE(`oper`, '|', '')) >= 2 
        THEN TRIM(SUBSTRING_INDEX(`oper`, '|', -1)) 
        ELSE NULL 
    END AS `control`,
    
    `monto`, 
    `descript`, 
    `seniat`, 
    `operador`
FROM `historial_pagos_unificado`;
```




## Sintaxis explicada
### SUBSTRING_INDEX(cadena, delimitador, contador_de_apariciones)
```contador_de_apariciones: {
       POSITIVE: Si el numero de apariciones es positivo cuenta la cantidad de veces que ocure el contador
       NEGATIVE: Si en negativo empiza por el ultimo    
}
```

### Ejemplos: Imagina que oper es 'pago|0171|0274'.

Obtener el tipo (primer valor):
```mysql
SELECT SUBSTRING_INDEX('pago|0171|0274', '|', 1);
```
***Resultado: 'pago'***


Obtener el control (último valor):
```mysql
SELECT SUBSTRING_INDEX('pago|0171|0274', '|', -1);
```
***Resultado: '0274'***


Obtener la factura (el truco del sándwich):
Primero cortamos hasta la segunda aparición ('pago|0171') y luego pedimos el último de ese resultado.
```mysql
SELECT SUBSTRING_INDEX(SUBSTRING_INDEX('pago|0171|0274', '|', 2), '|', -1);
```
***Resultado: '0171'***

