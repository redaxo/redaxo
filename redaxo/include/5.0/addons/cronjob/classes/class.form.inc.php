<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_cronjob_form extends rex_form
{
  /*private*/ var $mainFieldset;
  
  /*protected*/ function rex_cronjob_form($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
  {
    parent::rex_form($tableName, $fieldset, $whereCondition, $method, $debug);
    $this->mainFieldset = $fieldset;
  }
  
  /*public*/ function &addIntervalField($name, $value = null, $attributes = array())
  {
    $attributes['internal::fieldClass'] = 'rex_cronjob_form_interval_element';
    $attributes['class'] = 'rex-form-text rex-form-select';
    $field =& $this->addField('', $name, $value, $attributes, true);
    return $field;
  }
  
  /*protected*/ function validate()
  {
    global $I18N;
    $el =& $this->getElement($this->mainFieldset,'name');
    if ($el->getValue() == '') {
      return $I18N->msg('cronjob_error_no_name');
    }
    return true;
  }
  
  /*protected*/ function save() 
  {
    $return = parent::save();
    $manager = rex_cronjob_manager::factory();
    $manager->saveNextTime();
    return $return;
  }
}

class rex_cronjob_form_interval_element extends rex_form_element
{
  
  /*public*/ function formatElement()
  {
    global $I18N;
    $name = $this->getAttribute('name').'[]';
    $value = explode('|',htmlspecialchars($this->getValue()));
    if (count($value) != 4)
      $value = array(null,1,'d');
    
    $select = new rex_select();
    $select->setAttribute('class','rex-form-select rex-a630-interval');
    $select->setStyle('width:120px');
    $select->setName($name);
    $select->setSize(1);
    $select->addOption($I18N->msg('cronjob_interval_hour'),  'h');
    $select->addOption($I18N->msg('cronjob_interval_day'),   'd');
    $select->addOption($I18N->msg('cronjob_interval_week'),  'w');
    $select->addOption($I18N->msg('cronjob_interval_month'), 'm');
    $select->addOption($I18N->msg('cronjob_interval_year'),  'y');
    $select->setSelected($value[2]);

    return '
      <input type="text" class="rex-form-text rex-a630-interval" name="'.$name.'" style="width:20px; margin-right: 5px;" value="'.$value[1].'" />
      '. $select->get() . "\n";

  }
}