<?php

use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_system_setting_structure_package_status extends rex_system_setting
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getField(): rex_form_element
    {
        $field = new rex_form_select_element();
        $field->setAttribute('class', 'form-control');
        $field->setLabel(rex_i18n::msg('system_setting_' . $this->key));
        $select = $field->getSelect();
        $select->addOption(rex_i18n::msg('package_active'), 1);
        $select->addOption(rex_i18n::msg('package_disabled'), 0);
        $select->setSelected(Core::getConfig($this->key, false) ? 1 : 0);
        return $field;
    }

    public function setValue($value)
    {
        $value = (bool) $value;
        Core::setConfig($this->key, $value);
        return true;
    }
}
