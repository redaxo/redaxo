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
            return self::class . '::getTemplateOutput(require ' . self::class . '::getTemplateStream(' . $template_id . ', $this))';
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getTemplateStream($id, rex_article_content_base $article = null)
    {
        ob_start();
        $template = new rex_template($id);
        $content = $template->getTemplate();
        if ($article) {
            $content = $article->replaceCommonVars($content, $id);
        }
        return rex_stream::factory('template/' . ($template->getKey() ?? $template->getId()), $content);
    }

    /**
     * @return false|string
     */
    public static function getTemplateOutput()
    {
        return ob_get_clean();
    }
}
