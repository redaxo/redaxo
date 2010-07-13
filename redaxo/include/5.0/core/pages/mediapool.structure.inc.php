<?php

/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// *************************************** SUBPAGE: KATEGORIEN

if ($PERMALL)
{
  $edit_id = rex_request('edit_id', 'int');
  if ($media_method == 'edit_file_cat')
  {
    $cat_name = rex_request('cat_name', 'string');
    $db = rex_sql::factory();
    $db->setTable($REX['TABLE_PREFIX'].'file_category');
    $db->setWhere('id='.$edit_id);
    $db->setValue('name',$cat_name);
    $db->addGlobalUpdateFields();

    if($db->update())
    {
      $info = $I18N->msg('pool_kat_updated',$cat_name);
      rex_deleteCacheMediaCategory($edit_id);
    }
    else
    {
      $warning = $db->getError();
    }

  } elseif ($media_method == 'delete_file_cat')
  {
    $gf = rex_sql::factory();
    $gf->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'file WHERE category_id='.$edit_id);
    $gd = rex_sql::factory();
    $gd->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'file_category WHERE re_id='.$edit_id);
    if ($gf->getRows()==0 && $gd->getRows()==0)
    {
      $gf->setQuery('DELETE FROM '.$REX['TABLE_PREFIX'].'file_category WHERE id='. $edit_id);
      rex_deleteCacheMediaCategory($edit_id);
      rex_deleteCacheMediaLists();
      $info = $I18N->msg('pool_kat_deleted');
    }else
    {
      $warning = $I18N->msg('pool_kat_not_deleted');
    }
  } elseif ($media_method == 'add_file_cat')
  {
    $db = rex_sql::factory();
    $db->setTable($REX['TABLE_PREFIX'].'file_category');
    $db->setValue('name',rex_request('catname', 'string'));
    $db->setValue('re_id', rex_request('cat_id', 'int'));
    $db->setValue('path', rex_request('catpath', 'string'));
    $db->addGlobalCreateFields();
    $db->addGlobalUpdateFields();

    if($db->insert())
    {
      $info = $I18N->msg('pool_kat_saved', stripslashes(rex_request('catname')));
      rex_deleteCacheMediaCategoryList(rex_request('cat_id', 'int'));
    }
    else
    {
      $warning = $db->getError();
    }
  }

  $link = 'index.php?page=mediapool'.$arg_url.'&amp;subpage=categories&amp;cat_id=';

  $textpath = '<li> : <a href="'.$link.'0">Start</a></li>';
  $cat_id = rex_request('cat_id', 'int');
  if ($cat_id == 0 || !($OOCat = OOMediaCategory::getCategoryById($cat_id)))
  {
    $OOCats = OOMediaCategory::getRootCategories();
    $cat_id = 0;
    $catpath = "|";
  }else
  {
    $OOCats = $OOCat->getChildren();
    // TODO getParentTree() verwenden
    $paths = explode("|",$OOCat->getPath());

    for ($i=1;$i<count($paths);$i++)
    {
      $iid = current($paths);
      if ($iid != "")
      {
        $icat = OOMediaCategory::getCategoryById($iid);
        $textpath .= '<li> : <a href="'.$link.$iid.'">'.$icat->getName().'</a></li>';
      }
      next($paths);
    }
    $textpath .= '<li> : <a href="'.$link.$cat_id.'">'.$OOCat->getName().'</a></li>';
    $catpath = $OOCat->getPath()."$cat_id|";
  }

  echo '<div id="rex-navi-path"><ul><li>'. $I18N->msg('pool_kat_path') .'</li> '. $textpath .'</ul></div>';

  if ($warning != '')
  {
    echo rex_warning($warning);
    $warning = '';
  }
  if ($info!='')
  {
    echo rex_info($info);
    $info = '';
  }

  if ($media_method == 'add_cat' || $media_method == 'update_file_cat')
  {
    $add_mode = $media_method == 'add_cat';
    $legend = $add_mode ? $I18N->msg('pool_kat_create_label') : $I18N->msg('pool_kat_edit');
    $method = $add_mode ? 'add_file_cat' : 'edit_file_cat';

    echo '
    <div class="rex-form" id="rex-form-mediapool-categories">
      <form action="index.php" method="post">
        <fieldset class="rex-form-col-1">
          <legend>'. $legend .'</legend>
          
          <div class="rex-form-wrapper">
            <input type="hidden" name="page" value="mediapool" />
            <input type="hidden" name="subpage" value="categories" />
            <input type="hidden" name="media_method" value="'. $method .'" />
            <input type="hidden" name="cat_id" value="'. $cat_id .'" />
            <input type="hidden" name="catpath" value="'. $catpath .'" />
            '. $arg_fields .'
    ';
  }

  echo '<table class="rex-table" summary="'.htmlspecialchars($I18N->msg('pool_kat_summary')).'">
          <caption class="rex-hide">'.$I18N->msg('pool_kat_caption').'</caption>
          <colgroup>
            <col width="40" />
            <col width="40" />
            <col width="*" />
            <col width="77" />
            <col width="76" />
          </colgroup>
          <thead>
            <tr>
              <th class="rex-icon"><a class="rex-i-element rex-i-mediapool-category-add" href="'. $link . $cat_id .'&amp;media_method=add_cat"'. rex_accesskey($I18N->msg('pool_kat_create'), $REX['ACKEY']['ADD']) .'><span class="rex-i-element-text">'. $I18N->msg('pool_kat_create') .'</span></a></th>
              <th class="rex-small">ID</th>
              <th>'. $I18N->msg('pool_kat_name') .'</th>
              <th colspan="2">'. $I18N->msg('pool_kat_function') .'</th>
            </tr>
          </thead>
          <tbody>';

  if ($media_method == 'add_cat')
  {
    echo '
      <tr class="rex-table-row-activ">
        <td class="rex-icon"><span class="rex-i-element rex-i-mediapool-category"><span class="rex-i-element-text">'.$I18N->msg('pool_kat_create').'</span></span></td>
        <td class="rex-small">-</td>
        <td>
          <label class="rex-form-hidden-label" for="rex-form-field-name">'. $I18N->msg('pool_kat_name') .'</label>
          <input class="rex-form-text" type="text" size="10" id="rex-form-field-name" name="catname" value="" />
        </td>
        <td colspan="2">
          <input type="submit" class="rex-form-submit" value="'. $I18N->msg('pool_kat_create'). '"'. rex_accesskey($I18N->msg('pool_kat_create'), $REX['ACKEY']['SAVE']) .' />
        </td>
      </tr>
    ';
  }

  foreach( $OOCats as $OOCat) {

    $iid = $OOCat->getId();
    $iname = $OOCat->getName();

    if ($media_method == 'update_file_cat' && $edit_id == $iid)
    {
      echo '
        <input type="hidden" name="edit_id" value="'. $edit_id .'" />
        <tr class="rex-table-row-activ">
          <td class="rex-icon"><span class="rex-i-element rex-i-mediapool-category"><span class="rex-i-element-text">'. htmlspecialchars($OOCat->getName()).'</span></span></td>
          <td class="rex-small">'. $iid .'</td>
          <td>
            <label class="rex-form-hidden-label" for="rex-form-field-name">'. $I18N->msg('pool_kat_name') .'</label>
            <input class="rex-form-text" type="text" id="rex-form-field-name" name="cat_name" value="'. htmlspecialchars($iname) .'" />
          </td>
          <td colspan="2">
            <input type="submit" class="rex-form-submit" value="'. $I18N->msg('pool_kat_update'). '"'. rex_accesskey($I18N->msg('pool_kat_update'), $REX['ACKEY']['SAVE']) .' />
          </td>
        </tr>
      ';
    }else
    {
      echo '<tr>
              <td class="rex-icon"><a class="rex-i-element rex-i-mediapool-category" href="'. $link . $iid .'"><span class="rex-i-element-text">'.htmlspecialchars($OOCat->getName()).'</span></a></td>
              <td class="rex-small">'. $iid .'</td>
              <td><a href="'. $link . $iid .'">'. htmlspecialchars($OOCat->getName()) .'</a></td>
              <td><a href="'. $link . $cat_id .'&amp;media_method=update_file_cat&amp;edit_id='. $iid .'">'. $I18N->msg('pool_kat_edit').'</a></td>
              <td><a href="'. $link . $cat_id .'&amp;media_method=delete_file_cat&amp;edit_id='. $iid .'" onclick="return confirm(\''. $I18N->msg('delete').' ?\')">'. $I18N->msg('pool_kat_delete') .'</a></td>
            </tr>';
    }
  }
  echo '
      </tbody>
    </table>';

  if ($media_method == 'add_cat' || $media_method == 'update_file_cat')
  {
    echo '
        </div>
      </fieldset>
    </form>
  </div>
    ';
  }
}