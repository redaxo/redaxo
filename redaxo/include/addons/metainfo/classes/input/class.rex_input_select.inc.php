<?php

class rex_input_select extends rex_input
{
  var $select;
  
  function rex_input_select()
  {
    parent::rex_input();
    
    $this->select = new rex_select();
    $this->setAttribute('class', 'rex-form-select');
  }
  
  /*public*/ function setValue($value)
  {
    $this->select->setSelected($value);
    parent::setValue($value);
  }
  
  /*public*/ function setAttribute($name, $value)
  {
    if($name == 'name')
    {
      $this->select->setName($value);
    }
    else if($name == 'id')
    {
      $this->select->setId($value);
    }
    else
    {
      $this->select->setAttribute($name, $value);
    }
    
    parent::setAttribute($name, $value);
  }
  
  /*public*/ function &getSelect()
  {
    return $this->select;
  }
  
  function getHtml()
  {
    return $this->select->get();
  }
}