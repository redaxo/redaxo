<?php

/**
 * REX_TEMPLATE[2].
 *
 * @package redaxo\structure\content
 */
class rex_var_template extends rex_var
{
    protected function getOutput()
    {
        $templateId = $this->getParsedArg('id', 0, true);
        $templateKey = $this->getArg('key', null, true);

        if (0 === $templateId && $templateKey) {
            $template = rex_template::forKey($templateKey);

            if ($template) {
                $templateId = $template->getId();
            }
        }

        if ($templateId > 0) {
            // the `require` statement must be in outer context, so that the included template uses the same variable scope
            return self::class . '::getTemplateOutput(' . $templateId . ', new rex_timer(), require ' . self::class . '::getTemplateStream(' . $templateId . ', $this))';
        }

        return false;
    }

    /**
     * @internal
     *
     * @param int|numeric-string $id
     *
     * @return string
     */
    public static function getTemplateStream($id, rex_article_content_base $article = null)
    {
        ob_start();
        $tmpl = new rex_template($id);
        $tmpl = $tmpl->getTemplate();
        if ($article) {
            $tmpl = $article->replaceCommonVars($tmpl, $id);
        }

        return rex_stream::factory('template/' . $id, $tmpl);
    }

    /**
     * @internal
     *
     * @param int|numeric-string $id
     *
     * @return false|string
     */
    public static function getTemplateOutput($id, ?rex_timer $timer = null)
    {
        if ($timer && rex::isDebugMode()) {
            $timer->stop();
            $tmpl = new rex_template($id);
            rex_timer::measured('Template: '.($tmpl->getKey() ?? $tmpl->getId()), $timer);
        }

        return ob_get_clean();
    }
}
