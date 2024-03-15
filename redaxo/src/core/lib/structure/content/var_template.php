<?php

use Redaxo\Core\Core;
use Redaxo\Core\Util\Stream;
use Redaxo\Core\Util\Timer;

/**
 * REX_TEMPLATE[2].
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
            return self::class . '::getTemplateOutput(' . $templateId . ', new \\Redaxo\\Core\\Util\\Timer(), require ' . self::class . '::getTemplateStream(' . $templateId . ', $this))';
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
    public static function getTemplateStream($id, ?rex_article_content_base $article = null)
    {
        ob_start(); // will be closed in getTemplateOutput()

        $tmpl = new rex_template($id);
        $tmpl = $tmpl->getTemplate();
        if ($article) {
            $tmpl = $article->replaceCommonVars($tmpl, $id);
        }

        return Stream::factory('template/' . $id, $tmpl);
    }

    /**
     * @internal
     *
     * @param int|numeric-string $id
     * @param mixed $template Param is not used, but the template file is included here so that it is timed between timer param and the execution of this method
     * @return false|string
     */
    public static function getTemplateOutput($id, ?Timer $timer = null, $template = null)
    {
        if ($timer && Core::isDebugMode()) {
            $timer->stop();
            $tmpl = new rex_template($id);
            Timer::measured('Template: ' . ($tmpl->getKey() ?? $tmpl->getId()), $timer);
        }

        return ob_get_clean();
    }
}
