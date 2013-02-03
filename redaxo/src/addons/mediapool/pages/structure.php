<?php

/**
 *
 * @package redaxo5
 */

// *************************************** SUBPAGE: KATEGORIEN

$media_method = rex_request('media_method', 'string');

if ($PERMALL) {
  $edit_id = rex_request('edit_id', 'int');

  try {
    if ($media_method == 'edit_file_cat') {
      $cat_name = rex_request('cat_name', 'string');
      $db = rex_sql::factory();
      $db->setTable(rex::getTablePrefix() . 'media_category');
      $db->setWhere(array('id' => $edit_id));
      $db->setValue('name', $cat_name);
      $db->addGlobalUpdateFields();

      $db->update();
      $info = rex_i18n::msg('pool_kat_updated', $cat_name);
      rex_media_cache::deleteCategory($edit_id);

    } elseif ($media_method == 'delete_file_cat') {
      $gf = rex_sql::factory();
      $gf->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media WHERE category_id=' . $edit_id);
      $gd = rex_sql::factory();
      $gd->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE re_id=' . $edit_id);
      if ($gf->getRows() == 0 && $gd->getRows() == 0) {
        $gf->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'media_category WHERE id=' . $edit_id);
        rex_media_cache::deleteCategory($edit_id);
        rex_media_cache::deleteLists();
        $info = rex_i18n::msg('pool_kat_deleted');
      } else {
        $warning = rex_i18n::msg('pool_kat_not_deleted');
      }
    } elseif ($media_method == 'add_file_cat') {
      $db = rex_sql::factory();
      $db->setTable(rex::getTablePrefix() . 'media_category');
      $db->setValue('name', rex_request('catname', 'string'));
      $db->setValue('re_id', rex_request('cat_id', 'int'));
      $db->setValue('path', rex_request('catpath', 'string'));
      $db->addGlobalCreateFields();
      $db->addGlobalUpdateFields();

      $db->insert();
      $info = rex_i18n::msg('pool_kat_saved', rex_request('catname'));
      rex_media_cache::deleteCategoryList(rex_request('cat_id', 'int'));
    }
  } catch (rex_sql_exception $e) {
    $warning = $e->getMessage();
  }

  $link = rex_url::currentBackendPage(array_merge($arg_url, array('cat_id' => '')));

  $textpath = '<li> : <a href="' . $link . '0">Start</a></li>';
  $cat_id = rex_request('cat_id', 'int');
  if ($cat_id == 0 || !($OOCat = rex_media_category::getCategoryById($cat_id))) {
    $OOCats = rex_media_category::getRootCategories();
    $cat_id = 0;
    $catpath = '|';
  } else {
    $OOCats = $OOCat->getChildren();
    $paths = explode('|', $OOCat->getPath());

    for ($i = 1; $i < count($paths); $i++) {
      $iid = current($paths);
      if ($iid != '') {
        $icat = rex_media_category::getCategoryById($iid);
        $textpath .= '<li> : <a href="' . $link . $iid . '">' . $icat->getName() . '</a></li>';
      }
      next($paths);
    }
    $textpath .= '<li> : <a href="' . $link . $cat_id . '">' . $OOCat->getName() . '</a></li>';
    $catpath = $OOCat->getPath() . "$cat_id|";
  }

  echo '<div id="rex-navi-path"><ul><li>' . rex_i18n::msg('pool_kat_path') . '</li> ' . $textpath . '</ul></div>';

  if ($warning != '') {
    echo rex_view::warning($warning);
    $warning = '';
  }
  if ($info != '') {
    echo rex_view::info($info);
    $info = '';
  }

  if ($media_method == 'add_cat' || $media_method == 'update_file_cat') {
    $add_mode = $media_method == 'add_cat';
    $legend = $add_mode ? rex_i18n::msg('pool_kat_create_label') : rex_i18n::msg('pool_kat_edit');
    $method = $add_mode ? 'add_file_cat' : 'edit_file_cat';

    echo '
    <div class="rex-form" id="rex-form-mediapool-categories">
      <form action="' . rex_url::currentBackendPage() . '" method="post">
        <fieldset class="rex-form-col-1">
          <legend>' . $legend . '</legend>

          <div class="rex-form-wrapper">
            <input type="hidden" name="media_method" value="' . $method . '" />
            <input type="hidden" name="cat_id" value="' . $cat_id . '" />
            <input type="hidden" name="catpath" value="' . $catpath . '" />
            ' . $arg_fields . '
    ';
  }

  echo '<table class="rex-table" summary="' . rex_i18n::msg('pool_kat_summary') . '">
          <caption class="rex-hide">' . rex_i18n::msg('pool_kat_caption') . '</caption>
          <colgroup>
            <col width="40" />
            <col width="40" />
            <col width="*" />
            <col width="77" />
            <col width="76" />
          </colgroup>
          <thead>
            <tr>
              <th class="rex-icon"><a class="rex-i-element rex-i-mediapool-category-add" href="' . $link . $cat_id . '&amp;media_method=add_cat"' . rex::getAccesskey(rex_i18n::msg('pool_kat_create'), 'add') . '><span class="rex-i-element-text">' . rex_i18n::msg('pool_kat_create') . '</span></a></th>
              <th class="rex-small">ID</th>
              <th>' . rex_i18n::msg('pool_kat_name') . '</th>
              <th colspan="2">' . rex_i18n::msg('pool_kat_function') . '</th>
            </tr>
          </thead>
          <tbody>';

  if ($media_method == 'add_cat') {
    echo '
      <tr class="rex-table-row-activ">
        <td class="rex-icon"><span class="rex-i-element rex-i-mediapool-category"><span class="rex-i-element-text">' . rex_i18n::msg('pool_kat_create') . '</span></span></td>
        <td class="rex-small">-</td>
        <td>
          <label class="rex-form-hidden-label" for="rex-form-field-name">' . rex_i18n::msg('pool_kat_name') . '</label>
          <input class="rex-form-text" type="text" size="10" id="rex-form-field-name" name="catname" value="" />
        </td>
        <td colspan="2">
          <input type="submit" class="rex-form-submit" value="' . rex_i18n::msg('pool_kat_create') . '"' . rex::getAccesskey(rex_i18n::msg('pool_kat_create'), 'save') . ' />
        </td>
      </tr>
    ';
  }

  foreach ( $OOCats as $OOCat) {

    $iid = $OOCat->getId();
    $iname = $OOCat->getName();

    if ($media_method == 'update_file_cat' && $edit_id == $iid) {
      echo '
        <input type="hidden" name="edit_id" value="' . $edit_id . '" />
        <tr class="rex-table-row-activ">
          <td class="rex-icon"><span class="rex-i-element rex-i-mediapool-category"><span class="rex-i-element-text">' . htmlspecialchars($OOCat->getName()) . '</span></span></td>
          <td class="rex-small">' . $iid . '</td>
          <td>
            <label class="rex-form-hidden-label" for="rex-form-field-name">' . rex_i18n::msg('pool_kat_name') . '</label>
            <input class="rex-form-text" type="text" id="rex-form-field-name" name="cat_name" value="' . htmlspecialchars($iname) . '" />
          </td>
          <td colspan="2">
            <input type="submit" class="rex-form-submit" value="' . rex_i18n::msg('pool_kat_update') . '"' . rex::getAccesskey(rex_i18n::msg('pool_kat_update'), 'save') . ' />
          </td>
        </tr>
      ';
    } else {
      echo '<tr>
              <td class="rex-icon"><a class="rex-i-element rex-i-mediapool-category" href="' . $link . $iid . '"><span class="rex-i-element-text">' . htmlspecialchars($OOCat->getName()) . '</span></a></td>
              <td class="rex-small">' . $iid . '</td>
              <td><a href="' . $link . $iid . '">' . htmlspecialchars($OOCat->getName()) . '</a></td>
              <td><a href="' . $link . $cat_id . '&amp;media_method=update_file_cat&amp;edit_id=' . $iid . '">' . rex_i18n::msg('pool_kat_edit') . '</a></td>
              <td><a href="' . $link . $cat_id . '&amp;media_method=delete_file_cat&amp;edit_id=' . $iid . '" data-confirm="' . rex_i18n::msg('delete') . ' ?">' . rex_i18n::msg('pool_kat_delete') . '</a></td>
            </tr>';
    }
  }
  echo '
      </tbody>
    </table>';

  if ($media_method == 'add_cat' || $media_method == 'update_file_cat') {
    echo '
        </div>
      </fieldset>
    </form>
  </div>
    ';
  }
}
