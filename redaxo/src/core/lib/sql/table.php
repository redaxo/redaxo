<?php

/**
 * Class to represent sql tables.
 *
 * @author gharlan
 *
 * @package redaxo\core\sql
 */
class rex_sql_table
{
    use rex_instance_pool_trait {
        clearInstance as private baseClearInstance;
    }

    public const FIRST = 'FIRST '; // The space is intended: column names cannot end with space

    /** @var int */
    private $db;

    /** @var rex_sql */
    private $sql;

    /** @var bool */
    private $new;

    /** @var string */
    private $name;

    /** @var string */
    private $originalName;

    /** @var rex_sql_column[] */
    private $columns = [];

    /** @var string[] mapping from current (new) name to existing (old) name in database */
    private $columnsExisting = [];

    /** @var string[] */
    private $implicitOrder = [];

    /** @var string[] */
    private $positions = [];

    /** @var string[] */
    private $primaryKey = [];

    /** @var string[] */
    private $primaryKeyExisting = [];

    /** @var rex_sql_index[] */
    private $indexes = [];

    /** @var string[] mapping from current (new) name to existing (old) name in database */
    private $indexesExisting = [];

    /** @var rex_sql_foreign_key[] */
    private $foreignKeys = [];

    /** @var string[] mapping from current (new) name to existing (old) name in database */
    private $foreignKeysExisting = [];

    /** @var string */
    private static $explicitCharset;

    private function __construct($name, int $db = 1)
    {
        $this->db = $db;
        $this->sql = rex_sql::factory($db);
        $this->name = $name;
        $this->originalName = $name;

        try {
            $columns = rex_sql::showColumns($name, $db);
            $this->new = false;
        } catch (rex_sql_exception $exception) {
            // Error code 42S02 means: Table does not exist
            if ($exception->getSql() && '42S02' !== $exception->getSql()->getErrno()) {
                throw $exception;
            }

            $this->new = true;

            return;
        }

        foreach ($columns as $column) {
            $this->columns[$column['name']] = new rex_sql_column(
                $column['name'],
                $column['type'],
                'YES' === $column['null'],
                $column['default'],
                $column['extra'] ?: null
            );

            $this->columnsExisting[$column['name']] = $column['name'];

            if ('PRI' === $column['key']) {
                $this->primaryKey[] = $column['name'];
            }
        }

        $this->primaryKeyExisting = $this->primaryKey;

        $indexParts = $this->sql->getArray('SHOW INDEXES FROM '.$this->sql->escapeIdentifier($name));
        $indexes = [];
        foreach ($indexParts as $part) {
            if ('PRIMARY' !== $part['Key_name']) {
                $indexes[$part['Key_name']][] = $part;
            }
        }

        foreach ($indexes as $indexName => $parts) {
            $columns = [];
            foreach ($parts as $part) {
                $columns[] = $part['Column_name'];
            }

            if ('FULLTEXT' === $parts[0]['Index_type']) {
                $type = rex_sql_index::FULLTEXT;
            } elseif (0 === (int) $parts[0]['Non_unique']) {
                $type = rex_sql_index::UNIQUE;
            } else {
                $type = rex_sql_index::INDEX;
            }

            $this->indexes[$indexName] = new rex_sql_index($indexName, $columns, $type);
            $this->indexesExisting[$indexName] = $indexName;
        }

        $foreignKeyParts = $this->sql->getArray('
            SELECT c.constraint_name, c.referenced_table_name, c.update_rule, c.delete_rule, k.column_name, k.referenced_column_name
            FROM information_schema.referential_constraints c
            LEFT JOIN information_schema.key_column_usage k ON c.constraint_name = k.constraint_name
            WHERE c.constraint_schema = DATABASE() AND c.table_name = ?', [$name]);
        $foreignKeys = [];
        foreach ($foreignKeyParts as $part) {
            $foreignKeys[$part['constraint_name']][] = $part;
        }

        foreach ($foreignKeys as $fkName => $parts) {
            $columns = [];
            foreach ($parts as $part) {
                $columns[$part['column_name']] = $part['referenced_column_name'];
            }

            $fk = $parts[0];

            $this->foreignKeys[$fkName] = new rex_sql_foreign_key($fkName, $fk['referenced_table_name'], $columns, $fk['update_rule'], $fk['delete_rule']);
            $this->foreignKeysExisting[$fkName] = $fkName;
        }
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public static function get($name, int $db = 1)
    {
        return self::getInstance([$db, $name], static function ($db, $name) {
            return new self($name, $db);
        });
    }

    public static function clearInstance($key)
    {
        // BC layer for old cache keys without db id
        if (!is_array($key)) {
            $key = [1, $key];
        }

        return static::baseClearInstance($key);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return !$this->new;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasColumn($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * @param string $name
     *
     * @return null|rex_sql_column
     */
    public function getColumn($name)
    {
        if (!$this->hasColumn($name)) {
            return null;
        }

        return $this->columns[$name];
    }

    /**
     * @return rex_sql_column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param null|string $afterColumn Column name or `rex_sql_table::FIRST`
     *
     * @return $this
     */
    public function addColumn(rex_sql_column $column, $afterColumn = null)
    {
        $name = $column->getName();

        if ($this->hasColumn($name)) {
            throw new RuntimeException(sprintf('Column "%s" already exists.', $name));
        }

        $this->columns[$name] = $column;

        $this->setPosition($name, $afterColumn);

        return $this;
    }

    /**
     * @param null|string $afterColumn Column name or `rex_sql_table::FIRST`
     *
     * @return $this
     */
    public function ensureColumn(rex_sql_column $column, $afterColumn = null)
    {
        $name = $column->getName();

        if (!$this->hasColumn($name)) {
            return $this->addColumn($column, $afterColumn);
        }

        $this->setPosition($name, $afterColumn);

        if ($this->getColumn($name)->equals($column)) {
            return $this;
        }

        $this->columns[$name] = $column->setModified(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function ensurePrimaryIdColumn()
    {
        return $this
            ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'auto_increment'))
            ->setPrimaryKey('id')
        ;
    }

    /**
     * @param null|string $afterColumn Column name or `rex_sql_table::FIRST`
     *
     * @return $this
     */
    public function ensureGlobalColumns($afterColumn = null)
    {
        return $this
            ->ensureColumn(new rex_sql_column('createdate', 'datetime'), $afterColumn)
            ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'), 'createdate')
            ->ensureColumn(new rex_sql_column('updatedate', 'datetime'), 'createuser')
            ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'), 'updatedate')
        ;
    }

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @throws rex_exception
     *
     * @return $this
     */
    public function renameColumn($oldName, $newName)
    {
        if (!$this->hasColumn($oldName)) {
            throw new rex_exception(sprintf('Column with name "%s" does not exist.', $oldName));
        }

        if ($this->hasColumn($newName)) {
            throw new rex_exception(sprintf('Column with the new name "%s" already exists.', $newName));
        }

        if ($oldName === $newName) {
            return $this;
        }

        $column = $this->getColumn($oldName)->setName($newName);

        unset($this->columns[$oldName]);
        $this->columns[$newName] = $column;

        if (isset($this->columnsExisting[$oldName])) {
            $this->columnsExisting[$newName] = $this->columnsExisting[$oldName];
            unset($this->columnsExisting[$oldName]);
        }

        if (false !== $key = array_search($oldName, $this->primaryKey)) {
            $this->primaryKey[$key] = $newName;
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function removeColumn($name)
    {
        unset($this->columns[$name]);

        return $this;
    }

    /**
     * @return null|string[] Column names
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey ?: null;
    }

    /**
     * @param null|string|string[] $columns Column name(s)
     *
     * @throws rex_exception
     *
     * @return $this
     */
    public function setPrimaryKey($columns)
    {
        if (is_array($columns) && !$columns) {
            throw new rex_exception('The primary key column array can not be empty. To delete the primary key use `null` instead.');
        }

        $columns = null === $columns ? [] : (array) $columns;

        if ($this->primaryKey === $columns) {
            return $this;
        }

        $this->primaryKey = $columns;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasIndex($name)
    {
        return isset($this->indexes[$name]);
    }

    /**
     * @param string $name
     *
     * @return null|rex_sql_index
     */
    public function getIndex($name)
    {
        if (!$this->hasIndex($name)) {
            return null;
        }

        return $this->indexes[$name];
    }

    /**
     * @return rex_sql_index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @return $this
     */
    public function addIndex(rex_sql_index $index)
    {
        $name = $index->getName();

        if ($this->hasIndex($name)) {
            throw new RuntimeException(sprintf('Index "%s" already exists.', $name));
        }

        $this->indexes[$name] = $index;

        return $this;
    }

    /**
     * @return $this
     */
    public function ensureIndex(rex_sql_index $index)
    {
        $name = $index->getName();

        if (!$this->hasIndex($name)) {
            return $this->addIndex($index);
        }

        if ($this->getIndex($name)->equals($index)) {
            return $this;
        }

        $this->indexes[$name] = $index->setModified(true);

        return $this;
    }

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @throws rex_exception
     *
     * @return $this
     */
    public function renameIndex($oldName, $newName)
    {
        if (!$this->hasIndex($oldName)) {
            throw new rex_exception(sprintf('Index with name "%s" does not exist.', $oldName));
        }

        if ($this->hasIndex($newName)) {
            throw new rex_exception(sprintf('Index with the new name "%s" already exists.', $newName));
        }

        if ($oldName === $newName) {
            return $this;
        }

        $index = $this->getIndex($oldName)->setName($newName);

        unset($this->indexes[$oldName]);
        $this->indexes[$newName] = $index;

        if (isset($this->indexesExisting[$oldName])) {
            $this->indexesExisting[$newName] = $this->indexesExisting[$oldName];
            unset($this->indexesExisting[$oldName]);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function removeIndex($name)
    {
        unset($this->indexes[$name]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasForeignKey($name)
    {
        return isset($this->foreignKeys[$name]);
    }

    /**
     * @param string $name
     *
     * @return null|rex_sql_foreign_key
     */
    public function getForeignKey($name)
    {
        if (!$this->hasForeignKey($name)) {
            return null;
        }

        return $this->foreignKeys[$name];
    }

    /**
     * @return rex_sql_foreign_key[]
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * @return $this
     */
    public function addForeignKey(rex_sql_foreign_key $foreignKey)
    {
        $name = $foreignKey->getName();

        if ($this->hasForeignKey($name)) {
            throw new RuntimeException(sprintf('Foreign key "%s" already exists.', $name));
        }

        $this->foreignKeys[$name] = $foreignKey;

        return $this;
    }

    /**
     * @return $this
     */
    public function ensureForeignKey(rex_sql_foreign_key $foreignKey)
    {
        $name = $foreignKey->getName();

        if (!$this->hasForeignKey($name)) {
            return $this->addForeignKey($foreignKey);
        }

        if ($this->getForeignKey($name)->equals($foreignKey)) {
            return $this;
        }

        $this->foreignKeys[$name] = $foreignKey->setModified(true);

        return $this;
    }

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @throws rex_exception
     *
     * @return $this
     */
    public function renameForeignKey($oldName, $newName)
    {
        if (!$this->hasForeignKey($oldName)) {
            throw new rex_exception(sprintf('Foreign key with name "%s" does not exist.', $oldName));
        }

        if ($this->hasForeignKey($newName)) {
            throw new rex_exception(sprintf('Foreign key with the new name "%s" already exists.', $newName));
        }

        if ($oldName === $newName) {
            return $this;
        }

        $foreignKey = $this->getForeignKey($oldName)->setName($newName);

        unset($this->foreignKeys[$oldName]);
        $this->foreignKeys[$newName] = $foreignKey;

        if (isset($this->foreignKeysExisting[$oldName])) {
            $this->foreignKeysExisting[$newName] = $this->foreignKeysExisting[$oldName];
            unset($this->foreignKeysExisting[$oldName]);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function removeForeignKey($name)
    {
        unset($this->foreignKeys[$name]);

        return $this;
    }

    /**
     * Ensures that the table exists with the given definition.
     */
    public function ensure()
    {
        if ($this->new) {
            $this->create();

            return;
        }

        if (self::$explicitCharset) {
            $this->sql->setQuery('ALTER TABLE '.$this->sql->escapeIdentifier($this->originalName).' CONVERT TO CHARACTER SET '.self::$explicitCharset.' COLLATE '.self::$explicitCharset.'_unicode_ci;');
        }

        $positions = $this->positions;
        $this->positions = [];

        $previous = self::FIRST;
        foreach ($this->implicitOrder as $name) {
            if (isset($this->positions[$name])) {
                continue;
            }

            $this->positions[$name] = $previous;
            $previous = $name;
        }

        foreach ($positions as $name => $after) {
            // unset is necessary to add new position as last array element
            unset($this->positions[$name]);
            $this->positions[$name] = $after;
        }

        $this->alter();
    }

    /**
     * Drops the table if it exists.
     */
    public function drop()
    {
        if (!$this->new) {
            $this->sql->setQuery(sprintf('DROP TABLE %s', $this->sql->escapeIdentifier($this->name)));
        }

        $this->new = true;
        $this->originalName = $this->name;
        $this->columnsExisting = [];
        $this->implicitOrder = [];
        $this->positions = [];
        $this->primaryKeyExisting = [];
    }

    /**
     * Creates the table.
     *
     * @throws rex_exception
     */
    public function create()
    {
        if (!$this->new) {
            throw new rex_exception(sprintf('Table "%s" already exists.', $this->name));
        }
        if (!$this->columns) {
            throw new rex_exception('A table must have at least one column.');
        }

        $this->sortColumns();

        $parts = [];

        foreach ($this->columns as $column) {
            $parts[] = $this->getColumnDefinition($column);
        }

        if ($this->primaryKey) {
            $parts[] = 'PRIMARY KEY '.$this->getKeyColumnsDefintion($this->primaryKey);
        }

        foreach ($this->indexes as $index) {
            $parts[] = $this->getIndexDefinition($index);
        }

        foreach ($this->foreignKeys as $foreignKey) {
            $parts[] = $this->getForeignKeyDefinition($foreignKey);
        }

        $charset = self::$explicitCharset ?? (rex::getConfig('utf8mb4') ? 'utf8mb4' : 'utf8');

        $query = 'CREATE TABLE '.$this->sql->escapeIdentifier($this->name)." (\n    ";
        $query .= implode(",\n    ", $parts);
        $query .= "\n) ENGINE=InnoDB DEFAULT CHARSET=$charset;";

        $this->sql->setQuery($query);

        $this->resetModified();
    }

    /**
     * Alters the table.
     *
     * @throws rex_exception
     */
    public function alter()
    {
        if ($this->new) {
            throw new rex_exception(sprintf('Table "%s" does not exist.', $this->originalName));
        }

        $parts = [];
        $dropForeignKeys = [];

        if ($this->name !== $this->originalName) {
            $parts[] = 'RENAME '.$this->sql->escapeIdentifier($this->name);
        }

        if ($this->primaryKeyExisting && $this->primaryKeyExisting !== $this->primaryKey) {
            $parts[] = 'DROP PRIMARY KEY';
        }

        foreach ($this->indexesExisting as $newName => $oldName) {
            if (!isset($this->indexes[$newName]) || $this->indexes[$newName]->isModified()) {
                $parts[] = 'DROP INDEX '.$this->sql->escapeIdentifier($oldName);
            }
        }

        foreach ($this->foreignKeysExisting as $newName => $oldName) {
            if (!isset($this->foreignKeys[$newName]) || $this->foreignKeys[$newName]->isModified()) {
                $dropForeignKeys[] = 'DROP FOREIGN KEY '.$this->sql->escapeIdentifier($oldName);
            }
        }

        $columns = $this->columns;
        $columnsExisting = $this->columnsExisting;

        $handle = function ($name, $after = null) use (&$parts, &$columns, &$columnsExisting) {
            $column = $columns[$name];
            $new = !isset($columnsExisting[$name]);
            $oldName = $new ? null : $columnsExisting[$name];
            unset($columns[$name], $columnsExisting[$name]);

            if (!$new && !$column->isModified() && null === $after) {
                return;
            }

            $definition = $this->getColumnDefinition($column);

            if (self::FIRST === $after) {
                $definition .= ' FIRST';
            } elseif (null !== $after) {
                $definition .= ' AFTER '.$this->sql->escapeIdentifier($after);
            }

            if ($new) {
                $parts[] = 'ADD '.$definition;
            } else {
                $parts[] = 'CHANGE '.$this->sql->escapeIdentifier($oldName).' '.$definition;
            }
        };

        $currentOrder = [];
        $after = self::FIRST;
        foreach ($columns as $name => $column) {
            $currentOrder[$after] = $name;
            $after = $name;

            if (!isset($this->positions[$name])) {
                $handle($name);
            }
        }

        foreach ($this->positions as $name => $after) {
            if (!isset($columns[$name])) {
                continue;
            }

            if (isset($currentOrder[$after]) && $currentOrder[$after] === $name) {
                $after = null;
            } else {
                unset($currentOrder[$name]);
            }

            $handle($name, $after);
        }

        foreach ($columnsExisting as $oldName) {
            $parts[] = 'DROP '.$this->sql->escapeIdentifier($oldName);
        }

        if ($this->primaryKey && $this->primaryKey !== $this->primaryKeyExisting) {
            $parts[] = 'ADD PRIMARY KEY '.$this->getKeyColumnsDefintion($this->primaryKey);
        }

        $fulltextIndexes = [];
        $fulltextAdded = false;
        foreach ($this->indexes as $index) {
            if (!$index->isModified() && isset($this->indexesExisting[$index->getName()])) {
                continue;
            }

            if (rex_sql_index::FULLTEXT === $index->getType()) {
                if ($fulltextAdded) {
                    $fulltextIndexes[] = 'ADD '.$this->getIndexDefinition($index);

                    continue;
                }

                $fulltextAdded = true;
            }

            $parts[] = 'ADD '.$this->getIndexDefinition($index);
        }

        foreach ($this->foreignKeys as $foreignKey) {
            if ($foreignKey->isModified() || !isset($this->foreignKeysExisting[$foreignKey->getName()])) {
                $parts[] = 'ADD '.$this->getForeignKeyDefinition($foreignKey);
            }
        }

        if (!$parts && !$dropForeignKeys) {
            return;
        }

        foreach ([$dropForeignKeys, $parts] as $stepParts) {
            if ($stepParts) {
                $query = 'ALTER TABLE '.$this->sql->escapeIdentifier($this->originalName)."\n    ";
                $query .= implode(",\n    ", $stepParts);
                $query .= ';';

                $this->sql->setQuery($query);
            }
        }

        foreach ($fulltextIndexes as $fulltextIndex) {
            $this->sql->setQuery('ALTER TABLE '.$this->sql->escapeIdentifier($this->originalName).' '.$fulltextIndex.';');
        }

        $this->sortColumns();
        $this->resetModified();
    }

    private function setPosition($name, $afterColumn)
    {
        if (null === $afterColumn) {
            $this->implicitOrder[] = $name;

            return;
        }

        if (self::FIRST !== $afterColumn && !$this->hasColumn($afterColumn)) {
            throw new InvalidArgumentException(sprintf('Column "%s" can not be placed after "%s", because that column does not exist.', $name, $afterColumn));
        }

        // unset is necessary to add new position as last array element
        unset($this->positions[$name]);
        $this->positions[$name] = $afterColumn;
    }

    /**
     * @return string
     */
    private function getColumnDefinition(rex_sql_column $column)
    {
        $default = $column->getDefault();
        if (!$default) {
            $default = '';
        } elseif (
            in_array(strtolower($column->getType()), ['timestamp', 'datetime'], true) &&
            in_array(strtolower($default), ['current_timestamp', 'current_timestamp()'], true)
        ) {
            $default = 'DEFAULT '.$default;
        } else {
            $default = 'DEFAULT '.$this->sql->escape($column->getDefault());
        }

        return sprintf(
            '%s %s %s %s %s',
            $this->sql->escapeIdentifier($column->getName()),
            $column->getType(),
            $default,
            $column->isNullable() ? '' : 'NOT NULL',
            $column->getExtra()
        );
    }

    /**
     * @return string
     */
    private function getIndexDefinition(rex_sql_index $index)
    {
        return sprintf(
            '%s %s %s',
            $index->getType(),
            $this->sql->escapeIdentifier($index->getName()),
            $this->getKeyColumnsDefintion($index->getColumns())
        );
    }

    /**
     * @return string
     */
    private function getForeignKeyDefinition(rex_sql_foreign_key $foreignKey)
    {
        return sprintf(
            'CONSTRAINT %s FOREIGN KEY %s REFERENCES %s %s ON UPDATE %s ON DELETE %s',
            $this->sql->escapeIdentifier($foreignKey->getName()),
            $this->getKeyColumnsDefintion(array_keys($foreignKey->getColumns())),
            $this->sql->escapeIdentifier($foreignKey->getTable()),
            $this->getKeyColumnsDefintion($foreignKey->getColumns()),
            $foreignKey->getOnUpdate(),
            $foreignKey->getOnDelete()
        );
    }

    /**
     * @return string
     */
    private function getKeyColumnsDefintion(array $columns)
    {
        $columns = array_map([$this->sql, 'escapeIdentifier'], $columns);

        return '('.implode(', ', $columns).')';
    }

    private function sortColumns()
    {
        $columns = [];

        foreach ($this->columns as $name => $column) {
            if (!isset($this->positions[$name])) {
                $columns[$name] = $column;
            }
        }

        foreach ($this->positions as $name => $after) {
            $insert = [$name => $this->columns[$name]];

            if (self::FIRST === $after) {
                $columns = $insert + $columns;

                continue;
            }

            $offset = array_search($after, array_keys($columns)) + 1;
            $columns = array_slice($columns, 0, $offset) + $insert + array_slice($columns, $offset);
        }

        $this->columns = $columns;
    }

    private function resetModified()
    {
        $this->new = false;

        if ($this->originalName !== $this->name) {
            self::clearInstance([$this->db, $this->originalName]);
            self::addInstance([$this->db, $this->name], $this);
        }

        $this->originalName = $this->name;

        $columns = $this->columns;
        $this->columns = [];
        $this->columnsExisting = [];
        foreach ($columns as $column) {
            $column->setModified(false);
            $this->columns[$column->getName()] = $column;
            $this->columnsExisting[$column->getName()] = $column->getName();
        }

        $this->implicitOrder = [];
        $this->positions = [];

        $this->primaryKeyExisting = $this->primaryKey;

        $indexes = $this->indexes;
        $this->indexes = [];
        $this->indexesExisting = [];
        foreach ($indexes as $index) {
            $index->setModified(false);
            $this->indexes[$index->getName()] = $index;
            $this->indexesExisting[$index->getName()] = $index->getName();
        }

        $foreignKeys = $this->foreignKeys;
        $this->foreignKeys = [];
        $this->foreignKeysExisting = [];
        foreach ($foreignKeys as $foreignKey) {
            $foreignKey->setModified(false);
            $this->foreignKeys[$foreignKey->getName()] = $foreignKey;
            $this->foreignKeysExisting[$foreignKey->getName()] = $foreignKey->getName();
        }
    }

    /**
     * Method is used in redaxo setup and should not be used anywhere else.
     *
     * @internal
     */
    public static function setUtf8mb4(bool $utf8mb4): void
    {
        self::$explicitCharset = $utf8mb4 ? 'utf8mb4' : 'utf8';
    }
}
