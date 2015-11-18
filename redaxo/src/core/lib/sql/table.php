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

    /** @var string */
    private $name;

    /** @var rex_sql_column[] */
    private $columns = [];

    /** @var string[] */
    private $existing = [];

    private function __construct($name)
    {
        $this->name = $name;

        foreach (rex_sql::factory()->showColumns($name) as $column) {
            $this->columns[$column['name']] = new rex_sql_column(
                $column['name'],
                $column['type'],
                'YES' === $column['null'],
                $column['default'],
                $column['extra']
            );

            $this->existing[] = $column['name'];
        }
    }

    /**
     * @param string $name
     *
     * @return null|self
     */
    public static function get($name)
    {
        return self::getInstance($name, function ($name) {
            return new self($name);
        });
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
     * @return $this|rex_sql_table
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
     * @return $this|rex_sql_table
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
     * @return $this|rex_sql_table
     */
    public function removeColumn($name)
    {
        unset($this->columns[$name]);

        return $this;
    }

    public function alter()
    {
        $queries = [];
        $sql = rex_sql::factory();

        $columnDefinition = function (rex_sql_column $column) use ($sql) {
            return sprintf(
                '%s %s %s %s',
                $sql->escapeIdentifier($column->getName()),
                $column->getType(),
                $column->getDefault() ? 'DEFAULT '.$sql->escape($column->getDefault()) : '',
                $column->isNullable() ? '' : 'NOT NULL',
                $column->getExtra()
            );
        };

        $columns = $this->columns;
        foreach ($this->existing as $name) {
            if (!isset($columns[$name])) {
                $queries[] = 'DROP '.$sql->escapeIdentifier($name);
                continue;
            }

            $column = $columns[$name];
            if ($column->isModified()) {
                $queries[] = 'CHANGE '.$sql->escapeIdentifier($name).' '.$columnDefinition($column);
            }
            unset($columns[$name]);
        }
        foreach ($columns as $column) {
            $queries[] = 'ADD '.$columnDefinition($column);
        }

        $query = 'ALTER TABLE '.$sql->escapeIdentifier($this->name)."\n    ";
        $query .= implode(",\n    ", $queries);
        $query .= ';';

        $sql->setQuery($query);

        $columns = $this->columns;
        $this->columns = [];
        $this->existing = [];
        foreach ($columns as $column) {
            $column->setModified(false);
            $this->columns[$column->getName()] = $column;
            $this->existing[] = $column->getName();
        }
    }
}
