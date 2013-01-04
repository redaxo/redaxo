<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

class rex_cronjob_form extends rex_form
{
  private $mainFieldset;

  public function __construct($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
  {
    parent::__construct($tableName, $fieldset, $whereCondition, $method, $debug);
    $this->mainFieldset = $fieldset;
  }

  public function addIntervalField($name, $value = null, $attributes = array())
  {
    $attributes['internal::fieldClass'] = 'rex_cronjob_form_interval_element';
    $attributes['class'] = 'rex-form-text rex-form-select';
    $field = $this->addField('', $name, $value, $attributes, true);
    return $field;
  }

  protected function validate()
  {
    $el = $this->getElement($this->mainFieldset, 'name');
    if ($el->getValue() == '') {
      return rex_i18n::msg('cronjob_error_no_name');
    }
    return true;
  }

  protected function save()
  {
    if ($this->isEditMode()) {
      $nexttime = $this->getElement($this->mainFieldset, 'nexttime');
      if ($nexttime->getValue() != 0) {
        $interval = $this->getElement($this->mainFieldset, 'interval');
        $nexttime->setValue(rex_cronjob_manager_sql::calculateNextTime($interval->getValue()));
      }
    }
    $return = parent::save();
    rex_cronjob_manager_sql::factory()->saveNextTime();
    return $return;
  }
}

class rex_cronjob_form_interval_element extends rex_form_element
{

  public function formatElement()
  {
    $name = $this->getAttribute('name') . '[]';
    $value = explode('|', htmlspecialchars($this->getValue()));
    if (count($value) != 4)
      $value = array(null, 1, 'd');

    $select = new rex_select();
    $select->setAttribute('class', 'rex-form-select rex-a630-interval');
    $select->setStyle('width:120px');
    $select->setName($name);
    $select->setSize(1);
    $select->addOption(rex_i18n::msg('cronjob_interval_minutes'), 'i');
    $select->addOption(rex_i18n::msg('cronjob_interval_hour'),    'h');
    $select->addOption(rex_i18n::msg('cronjob_interval_day'),     'd');
    $select->addOption(rex_i18n::msg('cronjob_interval_week'),    'w');
    $select->addOption(rex_i18n::msg('cronjob_interval_month'),   'm');
    $select->addOption(rex_i18n::msg('cronjob_interval_year'),    'y');
    $select->setSelected($value[2]);

    return '
      <input type="text" class="rex-form-text rex-a630-interval" name="' . $name . '" style="width:20px; margin-right: 5px;" value="' . $value[1] . '" />
      ' . $select->get() . "\n";

  }
}
