<?php

/**
 * Class for the start_article_id and notfound_article_id settings
 *
 * @author gharlan
 */
class rex_system_setting_article_id extends rex_system_setting
{
    private $key;

    /**
     * Constructor
     *
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
        $field->setLabel(rex_i18n::msg('system_setting_' . $this->key));
        return $field;
    }

    public function isValid($value)
    {
        $article = rex_article::getArticleById($value);
        if (!$article instanceof rex_article) {
            return rex_i18n::msg('system_setting_' . $this->key . '_invalid');
        }
        return true;
    }

    public function cast($value)
    {
        return (integer) $value;
    }
}
