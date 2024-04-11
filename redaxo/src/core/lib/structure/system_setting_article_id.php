<?php

use Redaxo\Core\Content\Article;
use Redaxo\Core\Core;
use Redaxo\Core\Form\Field\ArticleField;
use Redaxo\Core\Translation\I18n;

/**
 * Class for the start_article_id and notfound_article_id settings.
 *
 * @internal
 */
class rex_system_setting_article_id extends rex_system_setting
{
    public function __construct(
        private string $key,
    ) {}

    public function getKey()
    {
        return $this->key;
    }

    public function getField()
    {
        $field = new ArticleField();
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
        $article = Article::get($value);
        if (!$article instanceof Article) {
            return I18n::msg('system_setting_' . $this->key . '_invalid');
        }
        Core::setConfig($this->key, $value);
        return true;
    }
}
