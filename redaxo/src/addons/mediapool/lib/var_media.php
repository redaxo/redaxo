<?php

/**
 * REX_MEDIA[1].
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen des Medienpools gesprungen werden soll
 *   - types     => Filter für Dateiendungen die im Medienpool zur Auswahl stehen sollen
 *   - preview   => Bei Bildertypen ein Vorschaubild einblenden
 *   - output    => "mimetype": Mimetype des Bildes ausgeben
 *
 * @package redaxo\mediapool
 */
class rex_var_media extends rex_var
{
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 10) {
            return false;
        }

        $value = $this->getContextData()->getValue('media' . $id);

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
            $value = self::getWidget($id, 'REX_INPUT_MEDIA[' . $id . ']', $value, $args);
        } else {
            if ($this->hasArg('output') && $this->getArg('output') == 'mimetype') {
                $media = rex_media::get($value);
                if ($media) {
                    $value = $media->getType();
                }
            } elseif ($this->hasArg('field') && $field = $this->getParsedArg('field')) {
                return 'htmlspecialchars(rex_media::get(' . self::quote($value) . ')->getValue(' . $field . '))';
            }
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
            $open_params .= '&amp;args[' . urlencode($aname) . ']=' . urlencode($avalue);
        }

        $wdgtClass = ' rex-js-widget-media';
        if (isset($args['preview']) && $args['preview']) {
            $wdgtClass .= ' rex-js-widget-preview';
            if (rex_addon::get('media_manager')->isAvailable()) {
                $wdgtClass .= ' rex-js-widget-preview-media-manager';
            }
        }

        $disabled = ' disabled';
        $open_func = '';
        $add_func = '';
        $delete_func = '';
        $view_func = '';
        if (rex::getUser()->getComplexPerm('media')->hasMediaPerm()) {
            $disabled = '';
            $open_func = 'openREXMedia(' . $id . ',\'' . $open_params . '\');';
            $add_func = 'addREXMedia(' . $id . ',\'' . $open_params . '\');';
            $delete_func = 'deleteREXMedia(' . $id . ');';
            $view_func = 'viewREXMedia(' . $id . ',\'' . $open_params . '\');';
        }

        $e = [];
        $e['before'] = '<div class="rex-js-widget' . $wdgtClass . '">';
        $e['field'] = '<input class="form-control" type="text" name="' . $name . '" value="' . $value . '" id="REX_MEDIA_' . $id . '" readonly />';
        $e['functionButtons'] = '
                <a href="#" class="btn btn-popup" onclick="' . $open_func . 'return false;" title="' . rex_i18n::msg('var_media_open') . '"' . $disabled . '><i class="rex-icon rex-icon-open-mediapool"></i></a>
                <a href="#" class="btn btn-popup" onclick="' . $add_func . 'return false;" title="' . rex_i18n::msg('var_media_new') . '"' . $disabled . '><i class="rex-icon rex-icon-add-media"></i></a>
                <a href="#" class="btn btn-popup" onclick="' . $delete_func . 'return false;" title="' . rex_i18n::msg('var_media_remove') . '"' . $disabled . '><i class="rex-icon rex-icon-delete-media"></i></a>
                <a href="#" class="btn btn-popup" onclick="' . $view_func . 'return false;" title="' . rex_i18n::msg('var_media_view') . '"' . $disabled . '><i class="rex-icon rex-icon-view-media"></i></a>';
        $e['after'] = '<div class="rex-js-media-preview"></div></div>';

        $fragment = new rex_fragment();
        $fragment->setVar('elements', [$e], false);
        $media = $fragment->parse('core/form/widget.php');

        return $media;
    }
}
