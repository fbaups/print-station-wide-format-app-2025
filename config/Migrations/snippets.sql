/*
 Used to find Columns that are ntext in SQL Server
 */
SELECT TABLE_NAME               AS TableName,
       COLUMN_NAME              AS ColumnName,
       DATA_TYPE                AS DataType,
       CHARACTER_MAXIMUM_LENGTH AS MaxLength
FROM INFORMATION_SCHEMA.COLUMNS
WHERE DATA_TYPE = 'ntext'



/*
 Used to find Column information such as type, length and index on SQL Server
 */
SELECT
    c.TABLE_SCHEMA,
    c.TABLE_NAME,
    c.COLUMN_NAME,
    c.DATA_TYPE,
    c.CHARACTER_MAXIMUM_LENGTH AS MAX_LENGTH,
    CASE WHEN i.name IS NOT NULL THEN 'Yes' ELSE 'No' END AS HAS_INDEX,
    i.name AS INDEX_NAME
FROM
    INFORMATION_SCHEMA.COLUMNS c
        LEFT JOIN
    sys.index_columns ic ON c.TABLE_NAME = OBJECT_NAME(ic.object_id) AND c.COLUMN_NAME = COL_NAME(ic.object_id, ic.column_id)
        LEFT JOIN
    sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE
        c.DATA_TYPE = 'nvarchar'
ORDER BY
    c.TABLE_SCHEMA,
    c.TABLE_NAME,
    c.ORDINAL_POSITION;
