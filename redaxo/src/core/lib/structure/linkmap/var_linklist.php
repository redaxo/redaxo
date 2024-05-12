<?php

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\Category;
use Redaxo\Core\Core;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Fragment;

/**
 * REX_LINKLIST[1].
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen der Linkmap gesprungen werden soll
 */
class rex_var_linklist extends rex_var
{
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('linklist' . $id);

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
            $value = self::getWidget($id, 'REX_INPUT_LINKLIST[' . $id . ']', $value, $args);
        }

        return self::quote($value);
    }

    /**
     * @param int|string $id
     * @return string
     */
    public static function getWidget($id, $name, $value, array $args = [])
    {
        $category = Category::getCurrent() ? Category::getCurrent()->getId() : 0; // Aktuelle Kategorie vorauswählen

        // Falls ein Kategorie-Parameter angegeben wurde, die Linkmap in dieser Kategorie öffnen
        if (isset($args['category'])) {
            $category = (int) $args['category'];
        }

        $openParams = '&clang=' . Language::getCurrentId() . '&category_id=' . $category;

        $options = '';
        $linklistarray = null === $value ? [] : explode(',', $value);
        foreach ($linklistarray as $link) {
            if ('' == $link) {
                continue;
            }
            if ($article = Article::get((int) $link)) {
                $options .= '<option value="' . $link . '">' . rex_escape(trim(sprintf('%s [%s]', $article->getName(), $article->getId()))) . '</option>';
            }
        }

        $disabled = ' disabled';
        $openFunc = '';
        $deleteFunc = '';
        $quotedId = "'" . rex_escape($id, 'js') . "'";
        if (Core::requireUser()->getComplexPerm('structure')->hasStructurePerm()) {
            $disabled = '';
            $openFunc = 'openREXLinklist(' . $quotedId . ', \'' . $openParams . '\');';
            $deleteFunc = 'deleteREXLinklist(' . $quotedId . ');';
        }

        $e = [];
        $e['field'] = '
                <select class="form-control" name="REX_LINKLIST_SELECT[' . $id . ']" id="REX_LINKLIST_SELECT_' . $id . '" size="10">
                    ' . $options . '
                </select>
                <input type="hidden" name="' . $name . '" id="REX_LINKLIST_' . $id . '" value="' . $value . '" />';
        $e['moveButtons'] = '
                    <a href="#" class="btn btn-popup" onclick="moveREXLinklist(' . $quotedId . ',\'top\');return false;" title="' . I18n::msg('var_linklist_move_top') . '"><i class="rex-icon rex-icon-top"></i></a>
                    <a href="#" class="btn btn-popup" onclick="moveREXLinklist(' . $quotedId . ',\'up\');return false;" title="' . I18n::msg('var_linklist_move_up') . '"><i class="rex-icon rex-icon-up"></i></a>
                    <a href="#" class="btn btn-popup" onclick="moveREXLinklist(' . $quotedId . ',\'down\');return false;" title="' . I18n::msg('var_linklist_move_down') . '"><i class="rex-icon rex-icon-down"></i></a>
                    <a href="#" class="btn btn-popup" onclick="moveREXLinklist(' . $quotedId . ',\'bottom\');return false;" title="' . I18n::msg('var_linklist_move_bottom') . '"><i class="rex-icon rex-icon-bottom"></i></a>';
        $e['functionButtons'] = '
                    <a href="#" class="btn btn-popup" onclick="' . $openFunc . 'return false;" title="' . I18n::msg('var_link_open') . '"' . $disabled . '><i class="rex-icon rex-icon-open-linkmap"></i></a>
                    <a href="#" class="btn btn-popup" onclick="' . $deleteFunc . 'return false;" title="' . I18n::msg('var_link_delete') . '"' . $disabled . '><i class="rex-icon rex-icon-delete-link"></i></a>';

        $fragment = new Fragment();
        $fragment->setVar('elements', [$e], false);

        return $fragment->parse('core/form/widget_list.php');
    }
}
