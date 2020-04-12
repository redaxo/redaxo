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
            // and it must be executed after passing the $__timer variable to getTemplateOutput
            // (otherwise the included template could overwrite the variable)
            return self::class . '::getTemplateOutput($__path = ' . self::class . '::getTemplateStream(' . $template_id . ', $this, $__timer), ' . $template_id . ', $__timer, require $__path)';
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getTemplateStream($id, rex_article_content_base $article = null, &$timer = null)
    {
        ob_start();
        $tmpl = new rex_template($id);
        $tmpl = $tmpl->getTemplate();
        if ($article) {
            $tmpl = $article->replaceCommonVars($tmpl, $id);
        }

        if (rex::isDebugMode()) {
            $timer = new rex_timer();
        } else {
            $timer = null;
        }

        return rex_stream::factory('template/' . $id, $tmpl);
    }

    /**
     * @param int|string $id
     * @return false|string
     */
    public static function getTemplateOutput(string $path, $id, ?rex_timer $timer)
    {
        if ($timer) {
            $timer->stop();
            $tmpl = new rex_template($id);
            rex_timer::measured('Template: '.($tmpl->getKey() ?? $tmpl->getId()), $timer);
        }

        return ob_get_clean();
    }
}
