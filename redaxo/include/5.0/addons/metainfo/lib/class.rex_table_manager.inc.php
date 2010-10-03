<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_a62_tableManager
{
  var $tableName;
  var $DBID;

  function rex_a62_tableManager($tableName, $DBID =1)
  {
    $this->tableName = $tableName;
    $this->DBID = $DBID;
  }

  function getTableName()
  {
    return $this->tableName;
  }

  function addColumn($name, $type, $length, $default = null, $nullable = true)
  {
    $qry = 'ALTER TABLE `'. $this->getTableName() .'` ADD ';
    $qry .= '`'. $name .'` '. $type;

    if($length != 0)
       $qry .= '('. $length .')';

    if($default !== null)
      $qry .= ' DEFAULT \''. str_replace("'", "\'", $default) .'\'';

    if($nullable !== true)
      $qry .= ' NOT NULL';

    return $this->setQuery($qry);
  }

  function editColumn($oldname, $name, $type, $length, $default = null, $nullable = true)
  {
    $qry = 'ALTER TABLE `'. $this->getTableName() .'` CHANGE ';
    $qry .= '`'. $oldname .'` `'. $name .'` '. $type;

    if($length != 0)
       $qry .= '('. $length .')';

    if($default !== null)
      $qry .= ' DEFAULT \''. str_replace("'", "\'", $default) .'\'';

    if($nullable !== true)
      $qry .= ' NOT NULL';

    return $this->setQuery($qry);
  }

  function deleteColumn($name)
  {
    $qry = 'ALTER TABLE `'. $this->getTableName() .'` DROP ';
    $qry .= '`'. $name .'`';

    return $this->setQuery($qry);
  }

  function setQuery($qry)
  {
    $sql = rex_sql::factory($this->DBID);
    return $sql->setQuery($qry);
  }
}