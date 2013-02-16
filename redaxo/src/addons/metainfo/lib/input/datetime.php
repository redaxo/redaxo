<?php

class rex_input_datetime extends rex_input
{
  private
    $dateInput,
    $timeInput;

  public function __construct()
  {
    parent::__construct();

    $this->dateInput = rex_input::factory('date');
    $this->timeInput = rex_input::factory('time');
  }

  public function setValue($value)
  {
    if (!is_array($value)) {
      throw new rex_exception('Expecting $value to be an array!');
    }

    $this->dateInput->setValue($value);
    $this->timeInput->setValue($value);

    parent::setValue($value);
  }

  public function getValue()
  {
    return array_merge($this->dateInput->getValue(), $this->timeInput->getValue());
  }

  public function setAttribute($name, $value)
  {
    $this->dateInput->setAttribute($name, $value);
    $this->timeInput->setAttribute($name, $value);

    parent::setAttribute($name, $value);
  }

  public function getDaySelect()
  {
    return $this->dateInput->daySelect;
  }

  public function getMonthSelect()
  {
    return $this->dateInput->monthSelect;
  }

  public function getYearSelect()
  {
    return $this->dateInput->yearSelect;
  }

  public function getHourSelect()
  {
    return $this->hourSelect;
  }

  public function getMinuteSelect()
  {
    return $this->minuteSelect;
  }

  public function getHtml()
  {
    return $this->dateInput->getHtml() . '<span class="rex-form-select-separator">-</span>' . $this->timeInput->getHTML();
  }
}
