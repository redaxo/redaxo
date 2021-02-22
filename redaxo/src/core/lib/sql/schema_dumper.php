<?php

/**
 * Class for generating the php code for a rex_sql_table definition.
 *
 * Especially useful to generate the code for the `install.php` of packages.
 *
 * @author gharlan
 *
 * @package redaxo\core\sql
 */
class rex_sql_schema_dumper
{
    /**
     * Dumps the schema for the given table as php code (using `rex_sql_table`).
     *
     * @return string
     */
    public function dumpTable(rex_sql_table $table)
    {
        $code = 'rex_sql_table::get('.$this->tableName($table->getName()).')';

        $setPrimaryKey = true;
        $primaryKeyIsId = ['id'] === $table->getPrimaryKey();
        $idColumn = new rex_sql_column('id', 'int(10) unsigned', false, null, 'auto_increment');

        foreach ($table->getColumns() as $column) {
            if ($primaryKeyIsId && $column->equals($idColumn)) {
                $code .= "\n    ->ensurePrimaryIdColumn()";
                $setPrimaryKey = false;

                continue;
            }

            $code .= "\n    ->ensureColumn(".$this->getColumn($column).')';
        }

        $code = str_replace(
            "
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))",
            '
    ->ensureGlobalColumns()',
            $code
        );

        if ($setPrimaryKey && $table->getPrimaryKey()) {
            $code .= "\n    ->setPrimaryKey(".$this->getPrimaryKey($table->getPrimaryKey()).')';
        }

        foreach ($table->getIndexes() as $index) {
            $code .= "\n    ->ensureIndex(".$this->getIndex($index).')';
        }

        foreach ($table->getForeignKeys() as $foreignKey) {
            $code .= "\n    ->ensureForeignKey(".$this->getForeignKey($foreignKey).')';
        }

        $code .= "\n    ->ensure();\n";

        return $code;
    }

    /**
     * @return string
     */
    private function getColumn(rex_sql_column $column)
    {
        $parameters = [];
        $nonDefault = false;

        if (null !== $column->getComment()) {
            $parameters[] = $this->scalar($column->getComment());
            $nonDefault = true;
        }

        if ($nonDefault || null !== $column->getExtra()) {
            $parameters[] = $this->scalar($column->getExtra());
            $nonDefault = true;
        }

        if ($nonDefault || null !== $column->getDefault()) {
            $parameters[] = $this->scalar($column->getDefault());
            $nonDefault = true;
        }

        if ($nonDefault || false !== $column->isNullable()) {
            $parameters[] = $this->scalar($column->isNullable());
        }

        $parameters[] = $this->scalar($column->getType());
        $parameters[] = $this->scalar($column->getName());

        return 'new rex_sql_column('.implode(', ', array_reverse($parameters)).')';
    }

    /**
     * @return string
     */
    private function getIndex(rex_sql_index $index)
    {
        $parameters = [
            $this->scalar($index->getName()),
            $this->simpleArray($index->getColumns()),
        ];

        static $types = [
            rex_sql_index::UNIQUE => 'rex_sql_index::UNIQUE',
            rex_sql_index::FULLTEXT => 'rex_sql_index::FULLTEXT',
        ];

        if (rex_sql_index::INDEX !== $index->getType()) {
            $parameters[] = $types[$index->getType()];
        }

        return 'new rex_sql_index('.implode(', ', $parameters).')';
    }

    /**
     * @return string
     */
    private function getForeignKey(rex_sql_foreign_key $foreignKey)
    {
        $parameters = [
            $this->scalar($foreignKey->getName()),
            $this->tableName($foreignKey->getTable()),
            $this->map($foreignKey->getColumns()),
        ];

        static $options = [
            rex_sql_foreign_key::RESTRICT => 'rex_sql_foreign_key::RESTRICT',
            rex_sql_foreign_key::CASCADE => 'rex_sql_foreign_key::CASCADE',
            rex_sql_foreign_key::SET_NULL => 'rex_sql_foreign_key::SET_NULL',
        ];

        $nonDefaultOnDelete = rex_sql_foreign_key::RESTRICT !== $foreignKey->getOnDelete();

        if ($nonDefaultOnDelete || rex_sql_foreign_key::RESTRICT !== $foreignKey->getOnUpdate()) {
            $parameters[] = $options[$foreignKey->getOnUpdate()];
        }

        if ($nonDefaultOnDelete) {
            $parameters[] = $options[$foreignKey->getOnDelete()];
        }

        return 'new rex_sql_foreign_key('.implode(', ', $parameters).')';
    }

    private function getPrimaryKey(array $primaryKey)
    {
        if (1 === count($primaryKey)) {
            return $this->scalar(reset($primaryKey));
        }

        return $this->simpleArray($primaryKey);
    }

    private function tableName($name)
    {
        if (!str_starts_with($name, rex::getTablePrefix())) {
            return $this->scalar($name);
        }

        $name = substr($name, strlen(rex::getTablePrefix()));

        return 'rex::getTable('.$this->scalar($name).')';
    }

    /**
     * @return string
     */
    private function scalar($scalar)
    {
        if (null === $scalar) {
            return 'null';
        }

        return var_export($scalar, true);
    }

    /**
     * @return string
     */
    private function simpleArray(array $list)
    {
        $parts = [];

        foreach ($list as $value) {
            $parts[] = $this->scalar($value);
        }

        return '['.implode(', ', $parts).']';
    }

    /**
     * @return string
     */
    private function map(array $map)
    {
        $parts = [];

        foreach ($map as $key => $value) {
            $parts[] = $this->scalar($key).' => '.$this->scalar($value);
        }

        return '['.implode(', ', $parts).']';
    }
}
