<?php

/**
 * REX_FILE[1],
 * REX_FILELIST[1],
 * REX_FILE_BUTTON[1],
 * REX_FILELIST_BUTTON[1],
 * REX_MEDIA[1],
 * REX_MEDIALIST[1],
 * REX_MEDIA_BUTTON[1],
 * REX_MEDIALIST_BUTTON[1]
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen des Medienpools gesprungen werden soll
 *   - types     => Filter für Dateiendungen die im Medienpool zur Auswahl stehen sollen
 *   - preview   => Bei Bildertypen ein Vorschaubild einblenden
 *   - mimetype  => Mimetype des Bildes ausgeben
 *
 * Alle Variablen die mit REX_FILE beginnnen sind als deprecated anzusehen!
 * @package redaxo5
 */

class rex_var_media extends rex_var
{
  // --------------------------------- Actions

  public function getACRequestValues(array $REX_ACTION)
  {
    $values     = rex_request('MEDIA', 'array');
    $listvalues = rex_request('MEDIALIST', 'array');

    for ($i = 1; $i < 11; $i++) {
      $media     = isset($values[$i]) ? $values[$i] : '';
      $medialist = isset($listvalues[$i]) ? $listvalues[$i] : '';

      $REX_ACTION['MEDIA'][$i]     = $media;
      $REX_ACTION['MEDIALIST'][$i] = $medialist;
    }

    return $REX_ACTION;
  }

  public function getACDatabaseValues(array $REX_ACTION, rex_sql $sql)
  {
    for ($i = 1; $i < 11; $i++) {
      $REX_ACTION['MEDIA'][$i]     = $this->getValue($sql, 'file' . $i);
      $REX_ACTION['MEDIALIST'][$i] = $this->getValue($sql, 'filelist' . $i);
    }

    return $REX_ACTION;
  }

  public function setACValues(rex_sql $sql, array $REX_ACTION)
  {
    for ($i = 1; $i < 11; $i++) {
      $this->setValue($sql, 'file' . $i    , $REX_ACTION['MEDIA'][$i]    );
      $this->setValue($sql, 'filelist' . $i, $REX_ACTION['MEDIALIST'][$i]);
    }
  }

  // --------------------------------- Output

  public function getBEInput(rex_sql $sql, $content)
  {
    $content = $this->matchMediaButton($sql, $content);
    $content = $this->matchMediaListButton($sql, $content);
    $content = $this->getOutput($sql, $content);
    return $content;
  }

  public function getBEOutput(rex_sql $sql, $content)
  {
    $content = $this->getOutput($sql, $content);
    return $content;
  }

  /**
   * Ersetzt die Value Platzhalter
   */
  private function getOutput(rex_sql $sql, $content)
  {
    $content = $this->matchMedia($sql, $content);
    $content = $this->matchMediaList($sql, $content);
    return $content;
  }

  static public function handleDefaultParam($varname, array $args, $name, $value)
  {
    switch ($name) {
      case '0' :
        $args['id'] = (int) $value;
        break;
      case '1' :
      case 'category' :
        $args['category'] = (int) $value;
        break;
      case 'types' :
        $args[$name] = (string) $value;
        break;
      case 'preview' :
        $args[$name] = (boolean) $value;
        break;
      case 'mimetype' :
        $args[$name] = (string) $value;
        break;
    }
    return parent::handleDefaultParam($varname, $args, $name, $value);
  }

  /**
   * MediaButton für die Eingabe
   */
  private function matchMediaButton(rex_sql $sql, $content)
  {
    $vars = array (
      'REX_FILE_BUTTON',
      'REX_MEDIA_BUTTON'
    );
    foreach ($vars as $var) {
      $matches = $this->getVarParams($content, $var);
      foreach ($matches as $match) {
        list ($param_str, $args) = $match;
        $id = $this->getArg('id', $args, 0);

        if ($id < 11 && $id > 0) {
          $category = $this->getArg('category', $args, '');

          $replace = $this->getMediaButton($id, $category, $args);
          $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
          $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
        }
      }
    }

    return $content;
  }

  /**
   * MediaListButton für die Eingabe
   */
  private function matchMediaListButton(rex_sql $sql, $content)
  {
    $vars = array (
      'REX_FILELIST_BUTTON',
      'REX_MEDIALIST_BUTTON'
    );
    foreach ($vars as $var) {
      $matches = $this->getVarParams($content, $var);
      foreach ($matches as $match) {
        list ($param_str, $args) = $match;
        $id = $this->getArg('id', $args, 0);

        if ($id < 11 && $id > 0) {
          $category = '';
          if (isset($args['category'])) {
            $category = $args['category'];
            unset($args['category']);
          }

          $replace = $this->getMedialistButton($id, $this->getValue($sql, 'filelist' . $id), $category, $args);
          $replace = $this->handleGlobalWidgetParams($var, $args, $replace);
          $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
        }
      }
    }

    return $content;
  }

  /**
   * Wert für die Ausgabe
   */
  private function matchMedia(rex_sql $sql, $content)
  {
    $vars = array (
      'REX_FILE',
      'REX_MEDIA'
    );
    foreach ($vars as $var) {
      $matches = $this->getVarParams($content, $var);
      foreach ($matches as $match) {
        list ($param_str, $args) = $match;
        $id = $this->getArg('id', $args, 0);

        if ($id > 0 && $id < 11) {
          // Mimetype ausgeben
          if (isset($args['mimetype'])) {
            $OOM = rex_media::getMediaByName($this->getValue($sql, 'file' . $id));
            if ($OOM) {
              $replace = $OOM->getType();
            }
          }
          // "normale" ausgabe
          else {
            $replace = $this->getValue($sql, 'file' . $id);
          }

          $replace = $this->handleGlobalVarParams($var, $args, $replace);
          $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
        }
      }
    }
    return $content;
  }

  /**
   * Wert für die Ausgabe
   */
  private function matchMediaList(rex_sql $sql, $content)
  {
    $vars = array (
      'REX_FILELIST',
      'REX_MEDIALIST'
    );
    foreach ($vars as $var) {
      $matches = $this->getVarParams($content, $var);
      foreach ($matches as $match) {
        list ($param_str, $args) = $match;
        $id = $this->getArg('id', $args, 0);

        if ($id > 0 && $id < 11) {
          $replace = $this->getValue($sql, 'filelist' . $id);
          $replace = $this->handleGlobalVarParams($var, $args, $replace);
          $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
        }
      }
    }
    return $content;
  }

  /**
   * Gibt das Button Template zurück
   */
  static public function getMediaButton($id, $category = '', array $args = array())
  {
    $open_params = '';
    if ($category != '') {
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
      <input type="text" name="MEDIA[' . $id . ']" value="REX_MEDIA[' . $id . ']" id="REX_MEDIA_' . $id . '" readonly="readonly" />
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

  /**
   * Gibt das ListButton Template zurück
   */
  static public function getMedialistButton($id, $value, $category = '', array $args = array())
  {
    $open_params = '';
    if ($category != '') {
      $open_params .= '&amp;rex_file_category=' . $category;
    }

    foreach ($args as $aname => $avalue) {
      $open_params .= '&amp;args[' . $aname . ']=' . urlencode($avalue);
    }

    $wdgtClass = ' rex-widget-medialist';
    if (isset($args['preview']) && $args['preview']) {
      $wdgtClass .= ' rex-widget-preview';
      if (rex_addon::get('image_manager')->isAvailable())
        $wdgtClass .= ' rex-widget-preview-image-manager';
      elseif (rex_addon::get('image_resize')->isAvailable())
        $wdgtClass .= ' rex-widget-preview-image-resize';
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
      $open_func    = 'openREXMedialist(' . $id . ',\'' . $open_params . '\');';
      $add_func     = 'addREXMedialist(' . $id . ');';
      $delete_func  = 'deleteREXMedialist(' . $id . ');';
      $view_func    = 'viewREXMedialist(' . $id . ');';
    }

    $media = '
    <div id="rex-widget-medialist-' . $id . '" class="rex-widget' . $wdgtClass . '">
      <input type="hidden" name="MEDIALIST[' . $id . ']" id="REX_MEDIALIST_' . $id . '" value="' . $value . '" />
      <select name="MEDIALIST_SELECT[' . $id . ']" id="REX_MEDIALIST_SELECT_' . $id . '" size="8">
        ' . $options . '
      </select>
      <ul class="rex-navi-widget">
        <li><a href="#" class="rex-ic-top" onclick="moveREXMedialist(' . $id . ',\'top\');return false;" title="' . rex_i18n::msg('var_medialist_move_top') . '">' . rex_i18n::msg('var_medialist_move_top') . '</a></li>
        <li><a href="#" class="rex-ic-up" onclick="moveREXMedialist(' . $id . ',\'up\');return false;" title="' . rex_i18n::msg('var_medialist_move_up') . '">' . rex_i18n::msg('var_medialist_move_up') . '</a></li>
        <li><a href="#" class="rex-ic-down" onclick="moveREXMedialist(' . $id . ',\'down\');return false;" title="' . rex_i18n::msg('var_medialist_move_down') . '">' . rex_i18n::msg('var_medialist_move_down') . '</a></li>
        <li><a href="#" class="rex-ic-bottom" onclick="moveREXMedialist(' . $id . ',\'bottom\');return false;" title="' . rex_i18n::msg('var_medialist_move_bottom') . '">' . rex_i18n::msg('var_medialist_move_bottom') . '</a></li>
      </ul>
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
