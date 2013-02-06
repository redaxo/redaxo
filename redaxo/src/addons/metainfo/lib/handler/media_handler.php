<?php

class rex_metainfo_media_handler extends rex_metainfo_handler
{
  const PREFIX = 'med_';

  /**
   * Extension to check whether the given media is still in use.
   *
   * @param array $params EP Params
   * @return string
   */
  static public function isMediaInUse(array $params)
  {
    $warning = $params['subject'];

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT `name`, `type` FROM `' . rex::getTablePrefix() . 'metainfo_params` WHERE `type` IN(6,7)');

    $rows = $sql->getRows();
    if ($rows == 0)
      return $warning;

    $where = array(
      'articles' => array(),
      'media' => array()
    );
    $filename = addslashes($params['filename']);
    for ($i = 0; $i < $rows; $i++) {
      $name = $sql->getValue('name');
      if (rex_metainfo_meta_prefix($name) == self::PREFIX)
        $key = 'media';
      else
        $key = 'articles';
      switch ($sql->getValue('type')) {
        case '6':
          $where[$key][] = $name . '="' . $filename . '"';
          break;
        case '7':
          $where[$key][] = 'FIND_IN_SET("' . $filename . '", ' . $name . ')';
          break;
        default :
          trigger_error('Unexpected fieldtype "' . $sql->getValue('type') . '"!', E_USER_ERROR);
      }
      $sql->next();
    }

    $articles = '';
    $categories = '';
    if (!empty($where['articles'])) {
      $sql->setQuery('SELECT id, clang, re_id, name, catname, startpage FROM ' . rex::getTablePrefix() . 'article WHERE ' . implode(' OR ', $where['articles']));
      if ($sql->getRows() > 0) {
        foreach ($sql->getArray() as $art_arr) {
          $aid = $art_arr['id'];
          $clang = $art_arr['clang'];
          $re_id = $art_arr['re_id'];
          $name = $art_arr['startpage'] ? $art_arr['catname'] : $art_arr['name'];
          if ($art_arr['startpage']) {
            $categories .= '<li><a href="javascript:openPage(\'' . rex_url::backendPage('structure', array('edit_id' => $aid, 'function' => 'edit_cat', 'category_id' => $re_id, 'clang' => $clang)) . '\')">' . $art_arr['catname'] . '</a></li>';
          } else {
            $articles .= '<li><a href="javascript:openPage(\'' . rex_url::backendPage('content', array('article_id' => $aid, 'mode' => 'meta', 'clang' => $clang)) . '\')">' . $art_arr['name'] . '</a></li>';
          }
        }
        if ($articles != '') {
          $warning[] = rex_i18n::msg('minfo_media_in_use_art') . '<br /><ul>' . $articles . '</ul>';
        }
        if ($categories != '') {
          $warning[] = rex_i18n::msg('minfo_media_in_use_cat') . '<br /><ul>' . $categories . '</ul>';
        }
      }
    }
    $media = '';
    if (!empty($where['media'])) {
      $sql->setQuery('SELECT media_id, filename, category_id FROM ' . rex::getTablePrefix() . 'media WHERE ' . implode(' OR ', $where['media']));
      if ($sql->getRows() > 0) {
        foreach ($sql->getArray() as $med_arr) {
          $id = $med_arr['media_id'];
          $filename = $med_arr['filename'];
          $cat_id = $med_arr['category_id'];
          $media .= '<li><a href="' . rex_url::backendPage('mediapool/detail', array('file_id' => $id, 'rex_file_category' => $cat_id)) . '">' . $filename . '</a></li>';
        }
        if ($media != '') {
          $warning[] = rex_i18n::msg('minfo_media_in_use_med') . '<br /><ul>' . $media . '</ul>';
        }
      }
    }

    return $warning;
  }

  protected function buildFilterCondition(array $params)
  {
    $restrictionsCondition = '';

    $catId = rex_session('media[rex_file_category]', 'int');
    if (isset($params['activeItem'])) {
      $catId = $params['activeItem']->getValue('category_id');
    }

    if ($catId !== '') {
      $s = '';
      if ($catId != 0) {
        $OOCat = rex_media_category::getCategoryById($catId);

        // Alle Metafelder des Pfades sind erlaubt
        foreach ($OOCat->getPathAsArray() as $pathElement) {
          if ($pathElement != '') {
            $s .= ' OR `p`.`restrictions` LIKE "%|' . $pathElement . '|%"';
          }
        }
      }

      // Auch die Kategorie selbst kann Metafelder haben
      $s .= ' OR `p`.`restrictions` LIKE "%|' . $catId . '|%"';

      $restrictionsCondition = 'AND (`p`.`restrictions` = "" OR `p`.`restrictions` IS NULL ' . $s . ')';
    }

    return $restrictionsCondition;
  }

  protected function handleSave(array $params, rex_sql $sqlFields)
  {
    if (rex_request_method() != 'post' || !isset($params['media_id'])) return $params;

    $media = rex_sql::factory();
  //  $media->debugsql = true;
    $media->setTable(rex::getTablePrefix() . 'media');
    $media->setWhere('media_id=:mediaid', array('mediaid' => $params['media_id']));

    parent::fetchRequestValues($params, $media, $sqlFields);

    // do the save only when metafields are defined
    if ($media->hasValues())
      $media->update();

    return $params;
  }

  protected function renderFormItem($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
  {
    $s = '';

    if ($typeLabel != 'legend')
      $s .= '<div class="rex-form-row">';

    if ($tag != '')
      $s .= '<' . $tag . $tag_attr  . '>' . "\n";

    if ($labelIt)
      $s .= '<label for="' . $id . '">' . $label . '</label>' . "\n";

    $s .= $field . "\n";

    if ($tag != '')
      $s .= '</' . $tag . '>' . "\n";

    if ($typeLabel != 'legend')
      $s .= '</div>';

    return $s;
  }

  public function extendForm(array $params)
  {
    // Nur beim EDIT gibts auch ein Medium zum bearbeiten
    if ($params['extension_point'] == 'MEDIA_FORM_EDIT') {
      $params['activeItem'] = $params['media'];
      unset($params['media']);
      // Hier die category_id setzen, damit keine Warnung entsteht (REX_LINK_BUTTON)
      // $params['activeItem']->setValue('category_id', 0);
    } elseif ($params['extension_point'] == 'MEDIA_ADDED') {
      $sql = rex_sql::factory();
      $qry = 'SELECT media_id FROM ' . rex::getTablePrefix() . 'media WHERE filename="' . $params['filename'] . '"';
      $sql->setQuery($qry);
      if ($sql->getRows() == 1) {
        $params['media_id'] = $sql->getValue('media_id');
      } else {
        trigger_error('Error occured during file upload!', E_USER_ERROR);
        exit();
      }
    }

    return parent::renderFormAndSave(self::PREFIX, $params);
  }
}

$mediaHandler = new rex_metainfo_media_handler();

rex_extension::register('MEDIA_FORM_EDIT', array($mediaHandler, 'extendForm'));
rex_extension::register('MEDIA_FORM_ADD', array($mediaHandler, 'extendForm'));

rex_extension::register('MEDIA_ADDED', array($mediaHandler, 'extendForm'));
rex_extension::register('MEDIA_UPDATED', array($mediaHandler, 'extendForm'));

rex_extension::register('MEDIA_IS_IN_USE', array('rex_metainfo_media_handler', 'isMediaInUse'));
