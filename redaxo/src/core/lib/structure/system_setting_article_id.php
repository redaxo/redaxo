<?php

use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * Class for the start_article_id and notfound_article_id settings.
 *
 * @internal
 */
class rex_system_setting_article_id extends rex_system_setting
{
    /** @var string */
    private $key;

    /**
     * @param string $key Key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getField()
    {
        $field = new rex_form_widget_linkmap_element();
        $field->setAttribute('class', 'rex-form-widget');
        $field->setLabel(I18n::msg('system_setting_' . $this->key));
        $field->setValue(Core::getConfig($this->key, 1));
        return $field;
    }

    /**
     * @return string|bool
     */
    public function setValue($value)
    {
        $value = (int) $value;
        $article = rex_article::get($value);
        if (!$article instanceof rex_article) {
            return I18n::msg('system_setting_' . $this->key . '_invalid');
        }
        Core::setConfig($this->key, $value);
        return true;
    }
}