<?php

class rex_mediaMetainfoHandler extends rex_metainfoHandler
{
  function rex_a62_media_is_in_use($params)
  {
    global $REX;
  
    $warning = $params['subject'];
  
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT `name`, `type` FROM `'. $REX['TABLE_PREFIX'] .'62_params` WHERE `type` IN(6,7)');
  
    $rows = $sql->getRows();
    if($rows == 0)
      return $warning;
  
    $where = array(
      'articles' => array(),
      'media' => array()
    );
    $filename = addslashes($params['filename']);
    for($i = 0; $i < $rows; $i++)
    {
      $name = $sql->getValue('name');
      if (a62_meta_prefix($name) == 'med_')
        $key = 'media';
      else
        $key = 'articles';
      switch ($sql->getValue('type'))
      {
        case '6':
          $where[$key][] = $name .'="'. $filename .'"';
          break;
        case '7':
          // replace LIKE wildcards
          $likeFilename = str_replace(array('_', '%'), array('\_', '\%'), $filename);
  
          $where[$key][] = '('. $name .' = "'. $filename .'" OR '. $name .' LIKE "%,'. $likeFilename .'" OR '. $name .' LIKE "%,'. $likeFilename .',%" OR '. $name .' LIKE "'. $likeFilename .',%")';
          break;
        default :
          trigger_error ('Unexpected fieldtype "'. $sql->getValue('type') .'"!', E_USER_ERROR);
      }
      $sql->next();
    }
  
    $articles = '';
    $categories = '';
    if (!empty($where['articles']))
    {
      $sql->setQuery('SELECT id, clang, re_id, name, catname, startpage FROM '. $REX['TABLE_PREFIX'] .'article WHERE '. implode(' OR ', $where['articles']));
      if ($sql->getRows() > 0)
      {
        foreach($sql->getArray() as $art_arr)
        {
          $aid = $art_arr['id'];
          $clang = $art_arr['clang'];
          $re_id = $art_arr['re_id'];
          $name = $art_arr['startpage'] ? $art_arr['catname'] : $art_arr['name'];
          if ($art_arr['startpage'])
          {
            $categories .= '<li><a href="javascript:openPage(\'index.php?page=structure&amp;edit_id='. $aid .'&amp;function=edit_cat&amp;category_id='.$re_id.'&amp;clang='. $clang .'\')">'. $art_arr['catname'] .'</a></li>';
          }
          else
          {
            $articles .= '<li><a href="javascript:openPage(\'index.php?page=content&amp;article_id='. $aid .'&amp;mode=meta&amp;clang='. $clang .'\')">'. $art_arr['name'] .'</a></li>';
          }
        }
        if ($articles != '')
        {
          $warning[] = rex_i18n::msg('minfo_media_in_use_art').'<br /><ul>'.$articles.'</ul>';
        }
        if ($categories != '')
        {
          $warning[] = rex_i18n::msg('minfo_media_in_use_cat').'<br /><ul>'.$categories.'</ul>';
        }
      }
    }
    $media = '';
    if (!empty($where['media']))
    {
      $sql->setQuery('SELECT media_id, filename, category_id FROM '. $REX['TABLE_PREFIX'] .'media WHERE '. implode(' OR ', $where['media']));
      if ($sql->getRows() > 0)
      {
        foreach($sql->getArray() as $med_arr)
        {
          $id = $med_arr['media_id'];
          $filename = $med_arr['filename'];
          $cat_id = $med_arr['category_id'];
          $media .= '<li><a href="index.php?page=mediapool&amp;subpage=detail&amp;file_id='. $id .'&amp;rex_file_category='.$cat_id.'">'. $filename .'</a></li>';
        }
        if ($media != '')
        {
          $warning[] = rex_i18n::msg('minfo_media_in_use_med').'<br /><ul>'.$media.'</ul>';
        }
      }
    }
  
    return $warning;
  }

/**
   * Medien:
   *
   * Übernimmt die gePOSTeten werte in ein rex_sql-Objekt und speichert diese
   */
  function _rex_a62_metainfo_med_handleSave($params, $sqlFields)
  {
    if(rex_request_method() != 'post' || !isset($params['media_id'])) return $params;
  
    global $REX;
  
    $media = rex_sql::factory();
  //  $media->debugsql = true;
    $media->setTable($REX['TABLE_PREFIX']. 'media');
    $media->setWhere('media_id=:mediaid', array('mediaid' => $params['media_id']));
  
    parent::_rex_a62_metainfo_handleSave($params, $media, $sqlFields);
  
    // do the save only when metafields are defined
    if($media->hasValues())
      $media->update();
  
    return $params;
  }

	/**
   * Callback, dass ein Formular item formatiert
   */
  function rex_a62_metainfo_form_item($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
  {
    $s = '';
  
    if($typeLabel != 'legend')
      $s .= '<div class="rex-form-row">';
  
    if($tag != '')
      $s .= '<'. $tag . $tag_attr  .'>'. "\n";
  
    if($labelIt)
      $s .= '<label for="'. $id .'">'. $label .'</label>'. "\n";
  
    $s .= $field. "\n";
  
    if($tag != '')
      $s .='</'.$tag.'>'. "\n";
  
    if($typeLabel != 'legend')
      $s .= '</div>';
  
    return $s;
  }
  
  /**
   * Erweitert das Meta-Formular um die neuen Meta-Felder
   */
  function rex_a62_metainfo_form($params)
  {
    // Nur beim EDIT gibts auch ein Medium zum bearbeiten
    if($params['extension_point'] == 'MEDIA_FORM_EDIT')
    {
      $params['activeItem'] = $params['media'];
      unset($params['media']);
      // Hier die category_id setzen, damit keine Warnung entsteht (REX_LINK_BUTTON)
      // $params['activeItem']->setValue('category_id', 0);
    }
    else if($params['extension_point'] == 'MEDIA_ADDED')
    {
      global $REX;
  
      $sql = rex_sql::factory();
      $qry = 'SELECT media_id FROM '. $REX['TABLE_PREFIX'] .'media WHERE filename="'. $params['filename'] .'"';
      $sql->setQuery($qry);
      if($sql->getRows() == 1)
      {
        $params['media_id'] = $sql->getValue('media_id');
      }
      else
      {
        trigger_error('Error occured during file upload!', E_USER_ERROR);
        exit();
      }
    }
  
    return parent::_rex_a62_metainfo_form('med_', $params, array($this, '_rex_a62_metainfo_med_handleSave'));
  }  
}

$mediaHandler = new rex_mediaMetainfoHandler();

rex_extension::register('MEDIA_FORM_EDIT', array($mediaHandler, 'rex_a62_metainfo_form'));
rex_extension::register('MEDIA_FORM_ADD', array($mediaHandler, 'rex_a62_metainfo_form'));

rex_extension::register('MEDIA_ADDED', array($mediaHandler, 'rex_a62_metainfo_form'));
rex_extension::register('MEDIA_UPDATED', array($mediaHandler, 'rex_a62_metainfo_form'));

rex_extension::register('OOMEDIA_IS_IN_USE', array($mediaHandler, 'rex_a62_media_is_in_use'));
