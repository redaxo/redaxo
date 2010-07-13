<?php

class rex_input_date extends rex_input
{
  var $yearSelect;
  var $monthSelect;
  var $daySelect;
  
  function rex_input_date()
  {
    parent::rex_input();
    
    $this->yearSelect = new rex_select();
    $this->yearSelect->addOptions(range(2005,date('Y')+10), true);
    $this->yearSelect->setAttribute('class', 'rex-form-select-year');
    $this->yearSelect->setSize(1);
    
    $this->monthSelect = new rex_select();
    $this->monthSelect->addOptions(range(1,12), true);
    $this->monthSelect->setAttribute('class', 'rex-form-select-date');
    $this->monthSelect->setSize(1);
    
    $this->daySelect = new rex_select();
    $this->daySelect->addOptions(range(1,31), true);
    $this->daySelect->setAttribute('class', 'rex-form-select-date');
    $this->daySelect->setSize(1);
  }
  
  /*public*/ function setValue($value)
  {
    if(!is_array($value))
    {
      trigger_error('Expecting $value to be an array!', E_USER_ERROR);
    }

    foreach(array('year', 'month', 'day') as $reqIndex)
    {
      if(!isset($value[$reqIndex]))
      {
        trigger_error('Missing index "'. $reqIndex .'" in $value!', E_USER_ERROR);
      }
    }
    
    $this->yearSelect->setSelected($value['year']);
    $this->monthSelect->setSelected($value['month']);
    $this->daySelect->setSelected($value['day']);
    
    parent::setValue($value);
  }
  
  /*public*/ function setAttribute($name, $value)
  {
    if($name == 'name')
    {
      $this->yearSelect->setName($value.'[year]');
      $this->monthSelect->setName($value.'[month]');
      $this->daySelect->setName($value.'[day]');
    }
    else if($name == 'id')
    {
      $this->yearSelect->setId($value.'_year');
      $this->monthSelect->setId($value.'_month');
      $this->daySelect->setId($value);
    }
    else
    {
      $this->yearSelect->setAttribute($name, $value);
      $this->monthSelect->setAttribute($name, $value);
      $this->daySelect->setAttribute($name, $value);
    }
    
    parent::setAttribute($name, $value);
  }
  
  /*public*/ function &getDaySelect()
  {
    return $this->daySelect;
  }
  
  /*public*/ function &getMonthSelect()
  {
    return $this->monthSelect;
  }
  
  /*public*/ function &getYearSelect()
  {
    return $this->yearSelect;
  }
  
  function getHtml()
  {
    return $this->daySelect->get() .
           $this->monthSelect->get() .
           $this->yearSelect->get();
  }
}