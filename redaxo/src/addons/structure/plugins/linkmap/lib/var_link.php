<?php

/**
 * REX_LINK
 *
 * Attribute:
 *   - category  => Kategorie in die beim oeffnen der Linkmapw gesprungen werden soll
 *
 * @package redaxo5
 */

class rex_var_link extends rex_var
{
  protected function getOutput()
  {
    $id = $this->getArg('id', 0, true);
    if (!in_array($this->getContext(), array('module', 'action')) || !is_numeric($id) || $id < 1 || $id > 10) {
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
      $args = array();
      foreach (array('category') as $key) {
        if ($this->hasArg($key)) {
          $args[$key] = $this->getArg($key);
        }
      }
      $value = self::getWidget($id, 'REX_INPUT_LINK[' . $id . ']', $value, $args);
    } else {
      if ($this->hasArg('output') && $this->getArg('output') != 'id') {
        $value = rex_getUrl($value);
      }
    }

    return self::quote($value);
  }

  static public function getWidget($id, $name, $value, array $args = array())
  {
    $art_name = '';
    $clang = '';
    $art = rex_article::getArticleById($value);

    // Falls ein Artikel vorausgewählt ist, dessen Namen anzeigen und beim öffnen der Linkmap dessen Kategorie anzeigen
    if ($art instanceof rex_article) {
      $art_name = $art->getName();
      $category = $art->getCategoryId();
    }

    $open_params = '&clang=' . rex_clang::getCurrentId();
    if ($category || isset($args['category']) && ($category = (int) $args['category'])) {
      $open_params .= '&category_id=' . $category;
    }

    $open_class   = 'rex-ic-linkmap-open rex-inactive';
    $delete_class = 'rex-ic-link-delete rex-inactive';
    $open_func    = '';
    $delete_func  = '';
    if (rex::getUser()->getComplexPerm('structure')->hasStructurePerm()) {
      $open_class   = 'rex-ic-linkmap-open';
      $delete_class = 'rex-ic-link-delete';
      $open_func    = 'openLinkMap(\'REX_LINK_' . $id . '\', \'' . $open_params . '\');';
      $delete_func  = 'deleteREXLink(' . $id . ');';
    }

    $media = '
  <div id="rex-widget-linkmap-' . $id . '" class="rex-widget rex-widget-link">
    <input type="hidden" name="' . $name . '" id="REX_LINK_' . $id . '" value="' . $value . '" />
    <input type="text" size="30" name="REX_LINK_NAME[' . $id . ']" value="' . htmlspecialchars($art_name) . '" id="REX_LINK_' . $id . '_NAME" readonly="readonly" />
    <ul class="rex-navi-widget">
      <li><a href="#" class="' . $open_class . '" onclick="' . $open_func . 'return false;" title="' . rex_i18n::msg('var_link_open') . '">' . rex_i18n::msg('var_link_open') . '</a></li>
       <li><a href="#" class="' . $delete_class . '" onclick="' . $delete_func . 'return false;" title="' . rex_i18n::msg('var_link_delete') . '">' . rex_i18n::msg('var_link_delete') . '</a></li>
     </ul>
   </div>';

    return $media;
  }
}
