<?php

/**
 * Class to represent sql foreign keys.
 *
 * @author gharlan
 *
 * @package redaxo\core\sql
 */
class rex_sql_foreign_key
{
    public const RESTRICT = 'RESTRICT';
    public const CASCADE = 'CASCADE';
    public const SET_NULL = 'SET NULL';

    /** @var string */
    private $name;

    /** @var string */
    private $table;

    /** @var string[] */
    private $columns;

    /** @var string */
    private $onUpdate;

    /** @var string */
    private $onDelete;

    private $modified = false;

    /**
     * @param string   $name
     * @param string   $table
     * @param string[] $columns  Mapping of locale column to column in foreign table
     * @param string   $onUpdate One of `rex_sql_foreign_key::RESTRICT`, `rex_sql_foreign_key::CASCADE`, `rex_sql_foreign_key::SET_NULL`
     * @param string   $onDelete One of `rex_sql_foreign_key::RESTRICT`, `rex_sql_foreign_key::CASCADE`, `rex_sql_foreign_key::SET_NULL`
     */
    public function __construct($name, $table, array $columns, $onUpdate = self::RESTRICT, $onDelete = self::RESTRICT)
    {
        $this->name = $name;
        $this->table = $table;
        $this->columns = $columns;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
    }

    /**
     * @param bool $modified
     *
     * @return $this
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return bool
     */
    public function isModified()
    {
        return $this->modified;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this->setModified(true);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this->setModified(true);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string[] $columns Mapping of locale column to column in foreign table
     *
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this->setModified(true);
    }

    /**
     * @return string[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $onUpdate
     *
     * @return $this
     */
    public function setOnUpdate($onUpdate)
    {
        $this->onUpdate = $onUpdate;

        return $this->setModified(true);
    }

    /**
     * @return string
     */
    public function getOnUpdate()
    {
        return $this->onUpdate;
    }

    /**
     * @param string $onDelete
     *
     * @return $this
     */
    public function setOnDelete($onDelete)
    {
        $this->onDelete = $onDelete;

        return $this->setModified(true);
    }

    /**
     * @return string
     */
    public function getOnDelete()
    {
        return $this->onDelete;
    }

    /**
     * @return bool
     */
    public function equals(self $index)
    {
        return
            $this->name === $index->name &&
            $this->table === $index->table &&
            $this->columns === $index->columns &&
            $this->onUpdate === $index->onUpdate &&
            $this->onDelete === $index->onDelete;
    }
}
