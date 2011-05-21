<?php

abstract class rex_form_options_element extends rex_form_element
{
  private $options;

  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  public function __construct($tag = '', rex_form $table = null, array $attributes = array())
  {
    parent::__construct($tag, $table, $attributes);
    $this->options = array();
  }

  public function addOption($name, $value)
  {
    $this->options[$name] = $value;
  }

  public function addOptions(array $options, $useOnlyValues = false)
  {
    if(count($options)>0)
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

  public function addArrayOptions(array $options, $use_keys = true)
  {
  	foreach($options as $key => $value)
  	{
      if(!$use_keys)
        $key = $value;

      $this->addOption($value, $key);
  	}
  }

  public function addSqlOptions($qry)
  {
    $sql = rex_sql::factory();
    $this->addOptions($sql->getArray($qry, PDO::FETCH_NUM));
  }

  public function addDBSqlOptions($qry)
  {
    $sql = rex_sql::factory();
    $this->addOptions($sql->getDBArray($qry, PDO::FETCH_NUM));
  }

  public function getOptions()
  {
    return $this->options;
  }
}