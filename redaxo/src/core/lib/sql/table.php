<?php

/**
 * Class to represent sql tables.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_sql_table
{
    use rex_instance_pool_trait;

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

    /** @var string[] */
    private $columnsExisting = [];

    /** @var string[] */
    private $primaryKey = [];

    /** @var bool */
    private $primaryKeyModified = false;

    private function __construct($name)
    {
        $this->sql = rex_sql::factory();
        $this->name = $name;
        $this->originalName = $name;

        $columns = [];

        try {
            $columns = $this->sql->showColumns($name);
            $this->new = false;
        } catch (rex_sql_exception $exception) {
            // Error code 42S02 means: Table does not exist
            if ('42S02' !== $this->sql->getErrno()) {
                throw $exception;
            }

            $this->new = true;
        }

        foreach ($columns as $column) {
            $this->columns[$column['name']] = new rex_sql_column(
                $column['name'],
                $column['type'],
                'YES' === $column['null'],
                $column['default'],
                $column['extra'] ?: null
            );

            $this->columnsExisting[] = $column['name'];

            if ('PRI' === $column['key']) {
                $this->primaryKey[] = $column['name'];
            }
        }
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public static function get($name)
    {
        return self::getInstance($name, function ($name) {
            return new self($name);
        });
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
     * @param rex_sql_column $column
     *
     * @return $this
     */
    public function addColumn(rex_sql_column $column)
    {
        $name = $column->getName();

        if ($this->hasColumn($name)) {
            throw new RuntimeException(sprintf('Column "%s" already exists.', $name));
        }

        $this->columns[$name] = $column;

        return $this;
    }

    /**
     * @param rex_sql_column $column
     *
     * @return $this
     */
    public function ensureColumn(rex_sql_column $column)
    {
        $name = $column->getName();

        if (!$this->hasColumn($name)) {
            return $this->addColumn($column);
        }

        if ($this->getColumn($name)->equals($column)) {
            return $this;
        }

        $this->columns[$name] = $column->setModified(true);

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
     * @return $this
     *
     * @throws rex_exception
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

        $this->primaryKey = (array) $columns;
        $this->primaryKeyModified = true;

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
        $this->primaryKeyModified = !empty($this->primaryKey);
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

        $parts = [];

        foreach ($this->columns as $column) {
            $parts[] = $this->getColumnDefinition($column);
        }

        if ($this->primaryKey) {
            $parts[] = 'PRIMARY KEY '.$this->getKeyColumnsDefintion($this->primaryKey);
        }

        $query = 'CREATE TABLE '.$this->sql->escapeIdentifier($this->name)." (\n    ";
        $query .= implode(",\n    ", $parts);
        $query .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

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
            throw new rex_exception(sprintf('Table "%s" does not exist.', $this->name));
        }

        $parts = [];

        if ($this->name !== $this->originalName) {
            $parts[] = 'RENAME '.$this->sql->escapeIdentifier($this->name);
        }

        if ($this->primaryKeyModified) {
            $parts[] = 'DROP PRIMARY KEY';
        }

        $columns = $this->columns;
        foreach ($this->columnsExisting as $name) {
            if (!isset($columns[$name])) {
                $parts[] = 'DROP '.$this->sql->escapeIdentifier($name);
                continue;
            }

            $column = $columns[$name];
            if ($column->isModified()) {
                $parts[] = 'CHANGE '.$this->sql->escapeIdentifier($name).' '.$this->getColumnDefinition($column);
            }
            unset($columns[$name]);
        }
        foreach ($columns as $column) {
            $parts[] = 'ADD '.$this->getColumnDefinition($column);
        }

        if ($this->primaryKeyModified && $this->primaryKey) {
            $parts[] = 'ADD PRIMARY KEY '.$this->getKeyColumnsDefintion($this->primaryKey);
        }

        if (!$parts) {
            return;
        }

        $query = 'ALTER TABLE '.$this->sql->escapeIdentifier($this->originalName)."\n    ";
        $query .= implode(",\n    ", $parts);
        $query .= ';';

        $this->sql->setQuery($query);

        $this->resetModified();
    }

    private function getColumnDefinition(rex_sql_column $column)
    {
        return sprintf(
            '%s %s %s %s %s',
            $this->sql->escapeIdentifier($column->getName()),
            $column->getType(),
            $column->getDefault() ? 'DEFAULT '.$this->sql->escape($column->getDefault()) : '',
            $column->isNullable() ? '' : 'NOT NULL',
            $column->getExtra()
        );
    }

    private function getKeyColumnsDefintion(array $columns)
    {
        $columns = array_map([$this->sql, 'escapeIdentifier'], $columns);

        return '('.implode(', ', $columns).')';
    }

    private function resetModified()
    {
        $this->new = false;

        if ($this->originalName !== $this->name) {
            self::clearInstance($this->originalName);
            self::addInstance($this->name, $this);
        }

        $this->originalName = $this->name;

        $columns = $this->columns;
        $this->columns = [];
        $this->columnsExisting = [];
        foreach ($columns as $column) {
            $column->setModified(false);
            $this->columns[$column->getName()] = $column;
            $this->columnsExisting[] = $column->getName();
        }

        $this->primaryKeyModified = false;
    }
}
