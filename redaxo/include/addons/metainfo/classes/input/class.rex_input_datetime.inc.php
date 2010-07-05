<?php

class rex_input_datetime extends rex_input
{
  var $dateInput;
  var $timeInput;
  
  function rex_input_datetime()
  {
    parent::rex_input();
    
    $this->dateInput = rex_input::factory('date');
    $this->timeInput = rex_input::factory('time');
  }
  
  /*public*/ function setValue($value)
  {
    if(!is_array($value))
    {
      trigger_error('Expecting $value to be an array!', E_USER_ERROR);
    }
    
    $this->dateInput->setValue($value);
    $this->timeInput->setValue($value);
    
    parent::setValue($value);
  }
  
  /*public*/ function getValue()
  {
    return array_merge($this->dateInput->setValue($value), $this->timeInput->setValue($value));
  }
  
  /*public*/ function setAttribute($name, $value)
  {
    $this->dateInput->setAttribute($name, $value);
    $this->timeInput->setAttribute($name, $value);
    
    parent::setAttribute($name, $value);
  }
  
  /*public*/ function &getDaySelect()
  {
    return $this->dateInput->daySelect;
  }
  
  /*public*/ function &getMonthSelect()
  {
    return $this->dateInput->monthSelect;
  }
  
  /*public*/ function &getYearSelect()
  {
    return $this->dateInput->yearSelect;
  }
  
  /*public*/ function &getHourSelect()
  {
    return $this->hourSelect;
  }
  
  /*public*/ function &getMinuteSelect()
  {
    return $this->minuteSelect;
  }
  
  function getHtml()
  {
    return $this->dateInput->getHtml() . '<span class="rex-form-select-separator">-</span>' . $this->timeInput->getHTML();
  }
}