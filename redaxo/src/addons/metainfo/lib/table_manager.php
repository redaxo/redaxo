<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\metainfo
 *
 * @internal
 */

class rex_metainfo_table_manager
{
    public const FIELD_TEXT = 1;
    public const FIELD_TEXTAREA = 2;
    public const FIELD_SELECT = 3;
    public const FIELD_RADIO = 4;
    public const FIELD_CHECKBOX = 5;
    public const FIELD_REX_MEDIA_WIDGET = 6;
    public const FIELD_REX_MEDIALIST_WIDGET = 7;
    public const FIELD_REX_LINK_WIDGET = 8;
    public const FIELD_REX_LINKLIST_WIDGET = 9;
    public const FIELD_DATE = 10;
    public const FIELD_DATETIME = 11;
    public const FIELD_LEGEND = 12;
    public const FIELD_TIME = 13;
    public const FIELD_COUNT = 13;

    private $tableName;
    private $DBID;

    public function __construct($tableName, $DBID = 1)
    {
        $this->tableName = $tableName;
        $this->DBID = $DBID;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return bool
     */
    public function addColumn($name, $type, $length, $default = null, $nullable = true)
    {
        $qry = 'ALTER TABLE `' . $this->getTableName() . '` ADD ';
        $qry .= '`' . $name . '` ' . $type;

        if (0 != $length) {
            $qry .= '(' . $length . ')';
        }

        // `text` columns in mysql can not have default values
        if ('text' !== $type && null !== $default) {
            $qry .= ' DEFAULT \'' . str_replace("'", "\'", $default) . '\'';
        }

        if (true !== $nullable) {
            $qry .= ' NOT NULL';
        }

        try {
            $this->setQuery($qry);
            return true;
        } catch (rex_sql_exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function editColumn($oldname, $name, $type, $length, $default = null, $nullable = true)
    {
        $qry = 'ALTER TABLE `' . $this->getTableName() . '` CHANGE ';
        $qry .= '`' . $oldname . '` `' . $name . '` ' . $type;

        if (0 != $length) {
            $qry .= '(' . $length . ')';
        }

        // `text` columns in mysql can not have default values
        if ('text' !== $type && null !== $default) {
            $qry .= ' DEFAULT \'' . str_replace("'", "\'", $default) . '\'';
        }

        if (true !== $nullable) {
            $qry .= ' NOT NULL';
        }

        try {
            $this->setQuery($qry);
            return true;
        } catch (rex_sql_exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function deleteColumn($name)
    {
        $qry = 'ALTER TABLE `' . $this->getTableName() . '` DROP ';
        $qry .= '`' . $name . '`';

        try {
            $this->setQuery($qry);
            return true;
        } catch (rex_sql_exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function hasColumn($name)
    {
        $columns = rex_sql::showColumns($this->getTableName(), $this->DBID);

        foreach ($columns as $column) {
            if ($column['name'] == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function setQuery($qry)
    {
        try {
            $sql = rex_sql::factory($this->DBID);
            $sql->setQuery($qry);
            return true;
        } catch (rex_sql_exception $e) {
            return false;
        }
    }
}
