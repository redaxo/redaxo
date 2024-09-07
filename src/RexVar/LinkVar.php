<?php

namespace Redaxo\Core\RexVar;

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\Category;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Fragment;

use function in_array;

/**
 * REX_LINK.
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen der Linkmap gesprungen werden soll
 */
class LinkVar extends RexVar
{
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('link' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if ($this->hasArg('widget') && $this->getArg('widget')) {
            if (!$this->environmentIs(self::ENV_INPUT)) {
                return false;
            }
            $args = [];
            foreach (['category'] as $key) {
                if ($this->hasArg($key)) {
                    $args[$key] = $this->getArg($key);
                }
            }
            $value = self::getWidget($id, 'REX_INPUT_LINK[' . $id . ']', $value, $args);
        } else {
            if ($value && $this->hasArg('output') && 'id' != $this->getArg('output')) {
                return '\\' . Url::class . '::article(' . self::quote($value) . ')';
            }
        }

        return self::quote($value);
    }

    /**
     * @param int|string $id
     * @return string
     */
    public static function getWidget($id, $name, $value, array $args = [])
    {
        $artName = '';
        $art = Article::get($value);
        $category = Category::getCurrent() ? Category::getCurrent()->getId() : 0; // Aktuelle Kategorie vorauswählen

        // Falls ein Artikel vorausgewählt ist, dessen Namen anzeigen und beim Öffnen der Linkmap dessen Kategorie anzeigen
        if ($art instanceof Article) {
            $artName = trim(sprintf('%s [%s]', $art->getName(), $art->getId()));
            $category = $art->getCategoryId();
        }

        // Falls ein Kategorie-Parameter angegeben wurde, die Linkmap in dieser Kategorie öffnen
        if (isset($args['category'])) {
            $category = (int) $args['category'];
        }

        $openParams = '&clang=' . Language::getCurrentId() . '&category_id=' . $category;

        $class = ' rex-disabled';
        $openFunc = '';
        $deleteFunc = '';
        if (Core::requireUser()->getComplexPerm('structure')->hasStructurePerm()) {
            $class = '';
            $escapedId = rex_escape($id, 'js');
            $openFunc = 'openLinkMap(\'REX_LINK_' . $escapedId . '\', \'' . $openParams . '\');';
            $deleteFunc = 'deleteREXLink(\'' . $escapedId . '\');';
        }

        $e = [];
        $e['field'] = '<input class="form-control" type="text" name="REX_LINK_NAME[' . $id . ']" value="' . rex_escape($artName) . '" id="REX_LINK_' . $id . '_NAME" readonly="readonly" /><input type="hidden" name="' . $name . '" id="REX_LINK_' . $id . '" value="' . $value . '" />';
        $e['functionButtons'] = '
                        <a href="#" class="btn btn-popup' . $class . '" onclick="' . $openFunc . 'return false;" title="' . I18n::msg('var_link_open') . '"><i class="rex-icon rex-icon-open-linkmap"></i></a>
                        <a href="#" class="btn btn-popup' . $class . '" onclick="' . $deleteFunc . 'return false;" title="' . I18n::msg('var_link_delete') . '"><i class="rex-icon rex-icon-delete-link"></i></a>';

        $fragment = new Fragment();
        $fragment->setVar('elements', [$e], false);

        return $fragment->parse('core/form/widget.php');
    }
}
