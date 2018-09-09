<?php
/**
 * Class for errormail setting to show in settings.
 *
 * @author Thomas Skerbis
 *
 * @package phpmailer
 *
 * @internal
 */
class rex_system_setting_phpmailer_errormail extends rex_system_setting
{
    public function getKey()
    {
        return 'phpmailer_errormail';
    }

    public function getField()
    {
        $field = new rex_form_select_element();
        $field->setAttribute('class', 'form-control selectpicker');
        $field->setLabel(rex_i18n::msg('system_setting_errormail'));
        $select = $field->getSelect();
        $select->addOption(rex_i18n::msg('phpmailer_errormail_disabled'), 0);
        $select->addOption(rex_i18n::msg('phpmailer_errormail_15min'), 900);
        $select->addOption(rex_i18n::msg('phpmailer_errormail_30min'), 1800);
        $select->addOption(rex_i18n::msg('phpmailer_errormail_60min'), 3600);
        $select->setSelected(rex_config::get('phpmailer', 'errormail', 1));
        return $field;
    }

    public function setValue($value)
    {
        $value = (int) $value;
        rex_config::set('phpmailer', 'errormail', $value);
        return true;
    }
}
