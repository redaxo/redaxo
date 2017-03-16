<?php

/**
 * Class for the default_template_id setting.
 *
 * @author gharlan
 *
 * @package redaxo\structure\content
 *
 * @internal
 */
class rex_system_setting_default_template_id extends rex_system_setting
{
    public function getKey()
    {
        return 'default_template_id';
    }

    public function getField()
    {
        $field = new rex_form_select_element();
        $field->setAttribute('class', 'form-control');
        $field->setLabel(rex_i18n::msg('system_setting_default_template_id'));
        $select = $field->getSelect();
        $select->setSize(1);
        $select->setSelected(rex_template::getDefaultId());

        $templates = rex_template::getTemplatesForCategory(0);
        if (empty($templates)) {
            $select->addOption(rex_i18n::msg('option_no_template'), 0);
        } else {
            $select->addArrayOptions($templates);
        }
        return $field;
    }

    public function setValue($value)
    {
        $value = (int) $value;

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'template WHERE id=? AND active=1', [$value]);
        if ($sql->getRows() != 1 && $value != 0) {
            return rex_i18n::msg('system_setting_default_template_id_invalid');
        }

        rex_config::set('structure/content', 'default_template_id', $value);
        return true;
    }
}
