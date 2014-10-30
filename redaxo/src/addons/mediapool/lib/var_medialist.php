<?php

/**
 * REX_MEDIALIST[1]
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen des Medienpools gesprungen werden soll
 *   - types     => Filter für Dateiendungen die im Medienpool zur Auswahl stehen sollen
 *   - preview   => Bei Bildertypen ein Vorschaubild einblenden
 *
 * @package redaxo\mediapool
 */
class rex_var_medialist extends rex_var
{
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('medialist' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if ($this->hasArg('widget') && $this->getArg('widget')) {
            if (!$this->environmentIs(self::ENV_INPUT)) {
                return false;
            }
            $args = [];
            foreach (['category', 'preview', 'types'] as $key) {
                if ($this->hasArg($key)) {
                    $args[$key] = $this->getArg($key);
                }
            }
            $value = self::getWidget($id, 'REX_INPUT_MEDIALIST[' . $id . ']', $value, $args);
        }

        return self::quote($value);
    }

    public static function getWidget($id, $name, $value, array $args = [])
    {
        $open_params = '';
        if (isset($args['category']) && ($category = (int) $args['category'])) {
            $open_params .= '&amp;rex_file_category=' . $category;
        }

        foreach ($args as $aname => $avalue) {
            $open_params .= '&amp;args[' . $aname . ']=' . urlencode($avalue);
        }

        $wdgtClass = ' rex-widget-medialist';
        if (isset($args['preview']) && $args['preview']) {
            $wdgtClass .= ' rex-widget-preview';
            if (rex_addon::get('media_manager')->isAvailable()) {
                $wdgtClass .= ' rex-widget-preview-media-manager';
            }
        }

        $options = '';
        $medialistarray = explode(',', $value);
        if (is_array($medialistarray)) {
            foreach ($medialistarray as $file) {
                if ($file != '') {
                    $options .= '<option value="' . $file . '">' . $file . '</option>';
                }
            }
        }

        $class        = 'rex-disabled';
        $open_func    = '';
        $add_func     = '';
        $delete_func  = '';
        $view_func    = '';
        if (rex::getUser()->getComplexPerm('media')->hasMediaPerm()) {
            $class        = '';
            $open_func    = 'openREXMedialist(' . $id . ',\'' . $open_params . '\');';
            $add_func     = 'addREXMedialist(' . $id . ',\'' . $open_params . '\');';
            $delete_func  = 'deleteREXMedialist(' . $id . ');';
            $view_func    = 'viewREXMedialist(' . $id . ',\'' . $open_params . '\');';
        }

        $media = '
        <div id="rex-widget-medialist-' . $id . '" class="rex-widget' . $wdgtClass . '">
            <input type="hidden" name="' . $name . '" id="REX_MEDIALIST_' . $id . '" value="' . $value . '" />
            <select name="REX_MEDIALIST_SELECT[' . $id . ']" id="REX_MEDIALIST_SELECT_' . $id . '" size="8">
                ' . $options . '
            </select>
            <span class="rex-button-vgroup">
                <a href="#" class="rex-button rex-button-primary" onclick="moveREXMedialist(' . $id . ',\'top\');return false;" title="' . rex_i18n::msg('var_medialist_move_top') . '"><span class="rex-icon rex-icon-top"></span></a>
                <a href="#" class="rex-button rex-button-primary" onclick="moveREXMedialist(' . $id . ',\'up\');return false;" title="' . rex_i18n::msg('var_medialist_move_up') . '"><span class="rex-icon rex-icon-up"></span></a>
                <a href="#" class="rex-button rex-button-primary" onclick="moveREXMedialist(' . $id . ',\'down\');return false;" title="' . rex_i18n::msg('var_medialist_move_down') . '"><span class="rex-icon rex-icon-down"></span></a>
                <a href="#" class="rex-button rex-button-primary" onclick="moveREXMedialist(' . $id . ',\'bottom\');return false;" title="' . rex_i18n::msg('var_medialist_move_bottom') . '"><span class="rex-icon rex-icon-bottom"></span></a>
            </span>
            <span class="rex-button-group">
                <a href="#" class="rex-button' . $class . '" onclick="' . $open_func . 'return false;" title="' . rex_i18n::msg('var_media_open') . '"><span class="rex-icon rex-icon-open-mediapool"></span></a>
                <a href="#" class="rex-button' . $class . '" onclick="' . $add_func . 'return false;" title="' . rex_i18n::msg('var_media_new') . '"><span class="rex-icon rex-icon-add-media"></span></a>
                <a href="#" class="rex-button' . $class . '" onclick="' . $delete_func . 'return false;" title="' . rex_i18n::msg('var_media_remove') . '"><span class="rex-icon rex-icon-delete-media"></span></a>
                <a href="#" class="rex-button' . $class . '" onclick="' . $view_func . 'return false;" title="' . rex_i18n::msg('var_media_view') . '"><span class="rex-icon rex-icon-view-media"></span></a>
            </span>
            <div class="rex-media-preview"></div>
        </div>
        ';

        return $media;
    }
}
