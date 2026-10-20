<?php

namespace Redaxo\Core\Backend\Form;

use Redaxo\Core\Content\Article;
use Redaxo\Core\Form\Field\ArticlePicker;
use Redaxo\Core\Form\Field\Checkbox;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Translation\Translatable;

use function asort;

/** @internal */
final class SystemSettings
{
    #[Choice(new Translatable('backend_language'), choices: self::locales(...), required: true, key: 'lang')]
    public string $language = 'en_gb';

    #[ArticlePicker(new Translatable('system_setting_start_article_id'), required: true, key: 'start_article_id')]
    public ?Article $startArticle = null;

    #[ArticlePicker(new Translatable('system_setting_notfound_article_id'), required: true, key: 'notfound_article_id')]
    public ?Article $notFoundArticle = null;

    #[Checkbox(new Translatable('system_setting_article_history'), key: 'article_history')]
    public bool $articleHistory = false;

    #[Checkbox(new Translatable('system_setting_article_work_version'), key: 'article_work_version')]
    public bool $articleWorkVersion = false;

    /** @return array<string, string> */
    public static function locales(): array
    {
        $locales = [];
        foreach (I18n::getLocales() as $locale) {
            $locales[$locale] = I18n::rawMsgInLocale('lang', $locale) . ' (' . $locale . ')';
        }
        asort($locales);

        return $locales;
    }
}
