<?php

class rex_form_options_element extends rex_form_element
{
  var $options;

  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  function rex_form_options_element($tag = '', &$table, $attributes = array())
  {
    parent::rex_form_element($tag, $table, $attributes);
    $this->options = array();
  }

  function addOption($name, $value)
  {
    $this->options[$name] = $value;
  }

  function addOptions($options, $useOnlyValues = false)
  {
    if(is_array($options) && count($options)>0)
    {
      foreach ($options as $key => $option)
      {
        $option = (array) $option;
        if($useOnlyValues)
        {
          $this->addOption($option[0], $option[0]);
        }
        else
        {
          if(!isset($option[1]))
            $option[1] = $key;

          $this->addOption($option[0], $option[1]);
        }
      }
    }
  }
  
  function addArrayOptions($options, $use_keys = true)
  {
  	foreach($options as $key => $value)
  	{
      if(!$use_keys)
        $key = $value;

      $this->addOption($value, $key);
  	}
  }

  function addSqlOptions($qry)
  {
    $sql = rex_sql::factory();
    $this->addOptions($sql->getArray($qry, MYSQL_NUM));
  }

  function addDBSqlOptions($qry)
  {
    $sql = rex_sql::factory();
    $this->addOptions($sql->getDBArray($qry, MYSQL_NUM));
  }

  function getOptions()
  {
    return $this->options;
  }
}