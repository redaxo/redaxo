<?php

/**
* Class for the default_template_id setting
*
* @author gharlan
*/
class rex_system_setting_default_template_id extends rex_system_setting
{
  public function __construct()
  {
  }

  public function getKey()
  {
    return 'default_template_id';
  }

  public function getClass()
  {
    return 'rex-form-select';
  }

  public function getLabel()
  {
    return rex_i18n::msg('system_setting_default_template_id');
  }

  public function getField()
  {
    $sel_template = new rex_select();
    $sel_template->setStyle('class="rex-form-select"');
    $sel_template->setName($this->getName());
    $sel_template->setId($this->getId());
    $sel_template->setSize(1);
    $sel_template->setSelected(rex::getProperty('default_template_id'));

    $templates = rex_ooCategory::getTemplates(0);
    if (empty($templates))
      $sel_template->addOption(rex_i18n::msg('option_no_template'), 0);
    else
      $sel_template->addArrayOptions($templates);
    return $sel_template->get();
  }

  public function isValid($value)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM '. rex::getTablePrefix() .'template WHERE id='. $value .' AND active=1');
    if($sql->getRows() != 1 && $value != 0)
    {
      return rex_i18n::msg('system_setting_default_template_id_invalid');
    }
    return true;
  }

  public function cast($value)
  {
    return (integer) $value;
  }
}