<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Form\Field\SelectField;
use Redaxo\Core\Translation\I18n;

/**
 * Class for the default_template_id setting.
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
        $field = new SelectField();
        $field->setAttribute('class', 'form-control selectpicker');
        $field->setLabel(I18n::msg('system_setting_default_template_id'));
        $select = $field->getSelect();
        $select->setSize(1);
        $select->setSelected(rex_template::getDefaultId());

        $templates = rex_template::getTemplatesForCategory(0);
        if (empty($templates)) {
            $select->addOption(I18n::msg('option_no_template'), 0);
        } else {
            $select->addArrayOptions(array_map(I18n::translate(...), $templates));
        }
        return $field;
    }

    /**
     * @return string|true
     */
    public function setValue($value)
    {
        $value = (int) $value;

        $sql = Sql::factory();
        $sql->setQuery('SELECT * FROM ' . Core::getTablePrefix() . 'template WHERE id=? AND active=1', [$value]);
        if (1 != $sql->getRows() && 0 != $value) {
            return I18n::msg('system_setting_default_template_id_invalid');
        }

        Core::setConfig('default_template_id', $value);
        return true;
    }
}
