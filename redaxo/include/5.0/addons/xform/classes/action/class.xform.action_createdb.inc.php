<?php

class rex_xform_action_createdb extends rex_xform_action_abstract
{
  
  function execute()
  {
    $table = $this->action["elements"][2];

    // Tabelle vorhanden ?
    $sql = new rex_sql;
    $sql->debug = 1;
    $sql->setQuery('show tables');
    $table_exists = FALSE;
    foreach($sql->getArray() as $k => $v)
    {
      if($table == $v)
      {
        $table_exists = TRUE;
        break;
      }
    }
    
    if(!$table_exists)
    {
    	$sql->setQuery('CREATE TABLE `'.$table.'` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY);');
    }
    
    // Welche Felder sind vorhanden ?
    $sql->setQuery('show columns from '.$table);
    $sql_cols = $sql->getArray();
    $cols = array();
    foreach($sql_cols as $k => $v)
    {
    	$cols[] = $v['Field'];
    }

    // wenn Feld nicht in Datenbank, dann als TEXT anlegen.
    foreach($this->elements_sql as $key => $value)
    {
    	if(!in_array($key,$cols))
    	{
        $sql->setQuery('ALTER TABLE `'.$table.'` ADD `'.$key.'` TEXT NOT NULL;');
    	}
    }
  	
    return;
    
  }

  function getDescription()
  {
    return "action|createdb|tblname|";
  }

}

?>