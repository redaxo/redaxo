<?php

/**
 * Class for the default_template_id setting
 *
 * @author gharlan
 * @package redaxo\structure\content
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

    public function getField()
    {
        $field = new rex_form_select_element();
        $field->setAttribute('class', 'rex-form-select');
        $field->setLabel(rex_i18n::msg('system_setting_default_template_id'));
        $select = $field->getSelect();
        $select->setSize(1);
        $select->setSelected(rex::getProperty('default_template_id'));

        $templates = rex_template::getTemplatesForCategory(0);
        if (empty($templates))
            $select->addOption(rex_i18n::msg('option_no_template'), 0);
        else
            $select->addArrayOptions($templates);
        return $field;
    }

    public function isValid($value)
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'template WHERE id=' . $value . ' AND active=1');
        if ($sql->getRows() != 1 && $value != 0) {
            return rex_i18n::msg('system_setting_default_template_id_invalid');
        }
        return true;
    }

    public function cast($value)
    {
        return (integer) $value;
    }
}
