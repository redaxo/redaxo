<?php

/**
 * REX_LINKLIST[1]
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen der Linkmap gesprungen werden soll
 *
 * @package redaxo\structure
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

    public static function getWidget($id, $name, $value, array $args = [])
    {
        $open_params = '&clang=' . rex_clang::getCurrentId();
        if (isset($args['category']) && ($category = (int) $args['category'])) {
            $open_params .= '&amp;category_id=' . $category;
        }

        $options = '';
        $linklistarray = explode(',', $value);
        if (is_array($linklistarray)) {
            foreach ($linklistarray as $link) {
                if ($link != '') {
                    if ($article = rex_article::getArticleById($link)) {
                        $options .= '<option value="' . $link . '">' . htmlspecialchars($article->getName()) . '</option>';
                    }
                }
            }
        }

        $class        = ' rex-disabled';
        $open_func    = '';
        $delete_func  = '';
        if (rex::getUser()->getComplexPerm('structure')->hasStructurePerm()) {
            $class        = '';
            $open_func    = 'openREXLinklist(' . $id . ', \'' . $open_params . '\');';
            $delete_func  = 'deleteREXLinklist(' . $id . ');';
        }

        $link = '
        <div id="rex-widget-linklist-' . $id . '" class="rex-widget rex-widget-linklist">
            <input type="hidden" name="' . $name . '" id="REX_LINKLIST_' . $id . '" value="' . $value . '" />
            <select name="REX_LINKLIST_SELECT[' . $id . ']" id="REX_LINKLIST_SELECT_' . $id . '" size="8">
                    ' . $options . '
            </select>
            <span class="rex-button-vgroup">
                <a href="#" class="rex-button rex-icon rex-icon-top" onclick="moveREXLinklist(' . $id . ',\'top\');return false;" title="' . rex_i18n::msg('var_linklist_move_top') . '"></a>
                <a href="#" class="rex-button rex-icon rex-icon-up" onclick="moveREXLinklist(' . $id . ',\'up\');return false;" title="' . rex_i18n::msg('var_linklist_move_up') . '"></a>
                <a href="#" class="rex-button rex-icon rex-icon-down" onclick="moveREXLinklist(' . $id . ',\'down\');return false;" title="' . rex_i18n::msg('var_linklist_move_down') . '"></a>
                <a href="#" class="rex-button rex-icon rex-icon-bottom" onclick="moveREXLinklist(' . $id . ',\'bottom\');return false;" title="' . rex_i18n::msg('var_linklist_move_bottom') . '"></a>
            </span>
            <span class="rex-button-group">
                <a href="#" class="rex-button rex-icon rex-icon-open-linkmap' . $class . '" onclick="' . $open_func . 'return false;" title="' . rex_i18n::msg('var_link_open') . '"></a>
                <a href="#" class="rex-button rex-icon rex-icon-delete-link' . $class . '" onclick="' . $delete_func . 'return false;" title="' . rex_i18n::msg('var_link_delete') . '"></a>
            </span>
        </div>';

        return $link;
    }
}
