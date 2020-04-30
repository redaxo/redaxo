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
        $template_id = $this->getParsedArg('id', 0, true);
        $template_key = $this->getArg('key', null, true);

        if (0 === $template_id && $template_key) {
            $template = rex_template::forKey($template_key);

            if ($template) {
                $template_id = $template->getId();
            }
        }

        if ($template_id > 0) {
            // the `require` statement must be in outer context, so that the included template uses the same variable scope
            return self::class . '::getTemplateOutput(' . $template_id . ', new rex_timer(), require ' . self::class . '::getTemplateStream(' . $template_id . ', $this))';
        }

        return false;
    }

    /**
     * @internal
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
     * @param int|string $id
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
