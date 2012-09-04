<?php

/**
 * REX_MEDIA[1]
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen des Medienpools gesprungen werden soll
 *   - types     => Filter für Dateiendungen die im Medienpool zur Auswahl stehen sollen
 *   - preview   => Bei Bildertypen ein Vorschaubild einblenden
 *   - output    => "mimetype": Mimetype des Bildes ausgeben
 *
 * @package redaxo5
 */

class rex_var_media extends rex_var
{
  protected function getOutput()
  {
    $id = $this->getArg('id', 0, true);
    if (!in_array($this->getContext(), array('module', 'action')) || !is_numeric($id) || $id < 1 || $id > 10) {
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
      $args = array();
      foreach (array('category', 'preview', 'types') as $key) {
        if ($this->hasArg($key)) {
          $args[$key] = $this->getArg($key);
        }
      }
      $value = self::getWidget($id, 'REX_INPUT_MEDIA[' . $id . ']', $value, $args);
    } else {
      if ($this->hasArg('output') && $this->getArg('output') == 'mimetype') {
        $media = rex_media::getMediaByName($value);
        if ($media) {
          $value = $media->getType();
        }
      }
    }

    return self::quote($value);
  }

  static public function getWidget($id, $name, $value, array $args = array())
  {
    $open_params = '';
    if (isset($args['category']) && ($category = (int) $args['category'])) {
      $open_params .= '&amp;rex_file_category=' . $category;
    }

    foreach ($args as $aname => $avalue) {
      $open_params .= '&amp;args[' . urlencode($aname) . ']=' . urlencode($avalue);
    }

    $wdgtClass = ' rex-widget-media';
    if (isset($args['preview']) && $args['preview']) {
      $wdgtClass .= ' rex-widget-preview';
      if (rex_addon::get('image_manager')->isAvailable())
        $wdgtClass .= ' rex-widget-preview-image-manager';
      elseif (rex_addon::get('image_resize')->isAvailable())
        $wdgtClass .= ' rex-widget-preview-image-resize';
    }

    $open_class   = 'rex-ic-mediapool-open rex-inactive';
    $add_class    = 'rex-ic-media-add rex-inactive';
    $delete_class = 'rex-ic-media-delete rex-inactive';
    $view_class   = 'rex-ic-media-view rex-inactive';
    $open_func    = '';
    $add_func     = '';
    $delete_func  = '';
    $view_func    = '';
    if (rex::getUser()->getComplexPerm('media')->hasMediaPerm()) {
      $open_class   = 'rex-ic-mediapool-open';
      $add_class    = 'rex-ic-media-add';
      $delete_class = 'rex-ic-media-delete';
      $view_class   = 'rex-ic-media-view';
      $open_func    = 'openREXMedia(' . $id . ',\'' . $open_params . '\');';
      $add_func     = 'addREXMedia(' . $id . ');';
      $delete_func  = 'deleteREXMedia(' . $id . ');';
      $view_func    = 'viewREXMedia(' . $id . ');';
    }

    $media = '
    <div id="rex-widget-media-' . $id . '" class="rex-widget' . $wdgtClass . '">
      <input type="text" name="' . $name . '" value="' . $value . '" id="REX_MEDIA_' . $id . '" readonly="readonly" />
      <ul class="rex-navi-widget">
        <li><a href="#" class="' . $open_class . '" onclick="' . $open_func . 'return false;" title="' . rex_i18n::msg('var_media_open') . '">' . rex_i18n::msg('var_media_open') . '</a></li>
        <li><a href="#" class="' . $add_class . '" onclick="' . $add_func . 'return false;" title="' . rex_i18n::msg('var_media_new') . '">' . rex_i18n::msg('var_media_new') . '</a></li>
        <li><a href="#" class="' . $delete_class . '" onclick="' . $delete_func . 'return false;" title="' . rex_i18n::msg('var_media_remove') . '">' . rex_i18n::msg('var_media_remove') . '</a></li>
        <li><a href="#" class="' . $view_class . '" onclick="' . $view_func . 'return false;" title="' . rex_i18n::msg('var_media_view') . '">' . rex_i18n::msg('var_media_view') . '</a></li>
      </ul>
      <div class="rex-media-preview"></div>
    </div>
    ';

    return $media;
  }
}
