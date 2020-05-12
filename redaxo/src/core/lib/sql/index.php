<?php

/**
 * Class to represent sql indexes.
 *
 * @author gharlan
 *
 * @package redaxo\core\sql
 */
class rex_sql_index
{
    public const INDEX = 'INDEX';
    public const UNIQUE = 'UNIQUE';
    public const FULLTEXT = 'FULLTEXT';

    private $name;
    private $type;
    private $columns;

    private $modified = false;

    /**
     * @param string   $name
     * @param string[] $columns
     * @param string   $type    One of `rex_sql_index::INDEX`, `rex_sql_index::UNIQUE`, `rex_sql_index::FULLTEXT`
     */
    public function __construct($name, array $columns, $type = self::INDEX)
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->type = $type;
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
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this->setModified(true);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string[] $columns
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
     * @return bool
     */
    public function equals(self $index)
    {
        return
            $this->name === $index->name &&
            $this->type === $index->type &&
            $this->columns == $index->columns;
    }
}
