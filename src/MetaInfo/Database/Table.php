<?php

namespace Redaxo\Core\MetaInfo\Database;

use Redaxo\Core\Database\Exception\SqlException;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\InvalidArgumentException;

/**
 * @internal
 */
class Table
{
    public const FIELD_TEXT = 1;
    public const FIELD_TEXTAREA = 2;
    public const FIELD_SELECT = 3;
    public const FIELD_RADIO = 4;
    public const FIELD_CHECKBOX = 5;
    public const FIELD_REX_MEDIA_WIDGET = 6;
    public const FIELD_REX_LINK_WIDGET = 8;
    public const FIELD_DATE = 10;
    public const FIELD_DATETIME = 11;
    public const FIELD_LEGEND = 12;
    public const FIELD_TIME = 13;
    public const FIELD_COUNT = 13;

    /** @param positive-int $DBID */
    public function __construct(
        private string $tableName,
        private int $DBID = 1,
    ) {}

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $name
     * @param string $type
     * @param int|null $length
     * @param string|null $default
     * @param bool $nullable
     * @return bool
     */
    public function addColumn($name, $type, $length, $default = null, $nullable = true)
    {
        $sql = Sql::factory($this->DBID);

        $qry = 'ALTER TABLE ' . $sql->escapeIdentifier($this->getTableName()) . ' ADD ';
        $qry .= $sql->escapeIdentifier($name);

        if (!ctype_alpha($type)) {
            throw new InvalidArgumentException('Invalid column type "' . $type . '"');
        }
        /** @psalm-taint-escape sql */
        $qry .= ' ' . $type;

        if (0 != $length) {
            $qry .= '(' . (int) $length . ')';
        }

        // `text` columns in mysql can not have default values
        if ('text' !== $type && null !== $default) {
            $qry .= ' DEFAULT ' . $sql->escape($default);
        }

        if (!$nullable) {
            $qry .= ' NOT NULL';
        }

        try {
            $sql->setQuery($qry);
            return true;
        } catch (SqlException) {
            return false;
        }
    }

    /**
     * @param string $oldname
     * @param string $name
     * @param string $type
     * @param int|null $length
     * @param string|null $default
     * @param bool $nullable
     * @return bool
     */
    public function editColumn($oldname, $name, $type, $length, $default = null, $nullable = true)
    {
        $sql = Sql::factory($this->DBID);

        $qry = 'ALTER TABLE ' . $sql->escapeIdentifier($this->getTableName()) . ' CHANGE ';
        $qry .= $sql->escapeIdentifier($oldname) . ' ' . $sql->escapeIdentifier($name);

        if (!ctype_alpha($type)) {
            throw new InvalidArgumentException('Invalid column type "' . $type . '"');
        }
        /** @psalm-taint-escape sql */
        $qry .= ' ' . $type;

        if (0 != $length) {
            $qry .= '(' . (int) $length . ')';
        }

        // `text` columns in mysql can not have default values
        if ('text' !== $type && null !== $default) {
            $qry .= ' DEFAULT ' . $sql->escape($default);
        }

        if (!$nullable) {
            $qry .= ' NOT NULL';
        }

        try {
            $sql->setQuery($qry);
            return true;
        } catch (SqlException) {
            return false;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function deleteColumn($name)
    {
        $sql = Sql::factory($this->DBID);

        $qry = 'ALTER TABLE ' . $sql->escapeIdentifier($this->getTableName()) . ' DROP ';
        $qry .= $sql->escapeIdentifier($name);

        try {
            $sql->setQuery($qry);
            return true;
        } catch (SqlException) {
            return false;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasColumn($name)
    {
        $columns = Sql::showColumns($this->getTableName(), $this->DBID);

        foreach ($columns as $column) {
            if ($column['name'] == $name) {
                return true;
            }
        }
        return false;
    }
}
