<?php

/**
 * Verwaltung der Inhalte. EditierModul / Metadaten ...
 * @package redaxo4
 * @version svn:$Id$
 */

/*
// TODOS:
// - alles vereinfachen
// - <? ?> $ Problematik bei REX_ACTION
*/









unset ($REX_ACTION);

$category_id = rex_request('category_id', 'rex-category-id');
$article_id  = rex_request('article_id',  'rex-article-id');
$clang       = rex_request('clang',       'rex-clang-id', $REX['START_CLANG_ID']);
$slice_id    = rex_request('slice_id',    'rex-slice-id', '');
$function    = rex_request('function',    'string');

$article_revision = 0;
$slice_revision = 0;
$template_attributes = '';

$warning = '';
$global_warning = '';
$info = '';
$global_info = '';

require $REX['INCLUDE_PATH'].'/functions/function_rex_content.inc.php';

$article = rex_sql::factory();
$article->setQuery("
		SELECT
			article.*, template.attributes as template_attributes
		FROM
			" . $REX['TABLE_PREFIX'] . "article as article
		LEFT JOIN " . $REX['TABLE_PREFIX'] . "template as template
      ON template.id=article.template_id
		WHERE
			article.id='$article_id'
			AND clang=$clang");


if ($article->getRows() == 1)
{  
  // ----- ctype holen
  $template_attributes = $article->getValue('template_attributes');

  // Für Artikel ohne Template
  if($template_attributes === null)
  	$template_attributes = '';

  $REX['CTYPE'] = rex_getAttributes('ctype', $template_attributes, array ()); // ctypes - aus dem template
	
  $ctype = rex_request('ctype', 'rex-ctype-id', 1);
  if (!array_key_exists($ctype, $REX['CTYPE']))
    $ctype = 1; // default = 1

  // ----- Artikel wurde gefunden - Kategorie holen
  $OOArt = OOArticle::getArticleById($article_id, $clang);
  $category_id = $OOArt->getCategoryId();

  // ----- category pfad und rechte
  require $REX['INCLUDE_PATH'] . '/functions/function_rex_category.inc.php';
  // $KATout kommt aus dem include
  // $KATPERM

  if ($REX['PAGE'] == 'content' && $article_id > 0)
  {
    $KATout .= "\n" . '<p>';

    if ($article->getValue('startpage') == 1)
      $KATout .= $I18N->msg('start_article') . ' : ';
    else
      $KATout .= $I18N->msg('article') . ' : ';

    $catname = str_replace(' ', '&nbsp;', htmlspecialchars($article->getValue('name')));

    $KATout .= '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=edit&amp;clang=' . $clang . '"'. rex_tabindex() .'>' . $catname . '</a>';
    // $KATout .= " [$article_id]";
    $KATout .= '</p>';
  }

  // ----- Titel anzeigen
  rex_title($I18N->msg('content'), $KATout);

  // ----- Request Parameter
  $mode     = rex_request('mode', 'string');
  $function = rex_request('function', 'string');
  $warning  = rex_request('warning', 'string');
  $info     = rex_request('info', 'string');
  
  // ----- mode defs
  if ($mode != 'meta')
    $mode = 'edit';

  // ----- Sprachenblock
  $sprachen_add = '&amp;mode='. $mode .'&amp;category_id=' . $category_id . '&amp;article_id=' . $article_id;
  require $REX['INCLUDE_PATH'] . '/functions/function_rex_languages.inc.php';

  // ----- EXTENSION POINT
  echo rex_register_extension_point('PAGE_CONTENT_HEADER', '',
    array(
      'article_id' => $article_id,
      'clang' => $clang,
      'function' => $function,
      'mode' => $mode,
      'slice_id' => $slice_id,
      'page' => 'content', 
      'ctype' => $ctype,
      'category_id' => $category_id,
      'article_revision' => &$article_revision,
      'slice_revision' => &$slice_revision,
    )
  );

  // ----------------- HAT USER DIE RECHTE AN DIESEM ARTICLE ODER NICHT
  if (!($KATPERM || $REX['USER']->hasPerm('article[' . $article_id . ']')))
  {
    // ----- hat keine rechte an diesem artikel
    echo rex_warning($I18N->msg('no_rights_to_edit'));
  }
  else
  {
    // ----- hat rechte an diesem artikel

    // ------------------------------------------ Slice add/edit/delete
    if (rex_request('save', 'boolean') && in_array($function, array('add', 'edit', 'delete')))
    {
      // ----- check module

      $CM = rex_sql::factory();
      if ($function == 'edit' || $function == 'delete')
      {
        // edit/ delete
        $CM->setQuery("SELECT * FROM " . $REX['TABLE_PREFIX'] . "article_slice LEFT JOIN " . $REX['TABLE_PREFIX'] . "module ON " . $REX['TABLE_PREFIX'] . "article_slice.modultyp_id=" . $REX['TABLE_PREFIX'] . "module.id WHERE " . $REX['TABLE_PREFIX'] . "article_slice.id='$slice_id' AND clang=$clang");
        if ($CM->getRows() == 1)
          $module_id = $CM->getValue("" . $REX['TABLE_PREFIX'] . "article_slice.modultyp_id");
      }else
      {
        // add
        $module_id = rex_post('module_id', 'int');
        $CM->setQuery('SELECT * FROM ' . $REX['TABLE_PREFIX'] . 'module WHERE id='.$module_id);
      }

      if ($CM->getRows() != 1)
      {
        // ------------- START: MODUL IST NICHT VORHANDEN
        $global_warning = $I18N->msg('module_not_found');
        $slice_id = '';
        $function = '';
        // ------------- END: MODUL IST NICHT VORHANDEN
      }
      else
      {
        // ------------- MODUL IST VORHANDEN

        // ----- RECHTE AM MODUL ?
        if($function != 'delete' && !rex_template::hasModule($template_attributes,$ctype,$module_id))
        {
          $global_warning = $I18N->msg('no_rights_to_this_function');
          $slice_id = '';
          $function = '';
        
        }elseif (!($REX['USER']->isAdmin() || $REX['USER']->hasPerm('module[' . $module_id . ']') || $REX['USER']->hasPerm('module[0]')))
        {
          // ----- RECHTE AM MODUL: NEIN
          $global_warning = $I18N->msg('no_rights_to_this_function');
          $slice_id = '';
          $function = '';
        }else
        {
          // ----- RECHTE AM MODUL: JA

          // ***********************  daten einlesen
          $REX_ACTION = array ();
          $REX_ACTION['SAVE'] = true;

          foreach ($REX['VARIABLES'] as $obj)
          {
            $REX_ACTION = $obj->getACRequestValues($REX_ACTION);
          }

          // ----- PRE SAVE ACTION [ADD/EDIT/DELETE]
          list($action_message, $REX_ACTION) = rex_execPreSaveAction($module_id, $function, $REX_ACTION);
          // ----- / PRE SAVE ACTION

          // Statusspeicherung für die rex_article Klasse
          $REX['ACTION'] = $REX_ACTION;

          // Werte werden aus den REX_ACTIONS übernommen wenn SAVE=true
          if (!$REX_ACTION['SAVE'])
          {
            // ----- DONT SAVE/UPDATE SLICE
            if ($action_message != '')
              $warning = $action_message;
            elseif ($function == 'delete')
            	$warning = $I18N->msg('slice_deleted_error');
            else
              $warning = $I18N->msg('slice_saved_error');

          }
          else
          {
            // ----- SAVE/UPDATE SLICE
            if ($function == 'add' || $function == 'edit')
            {
              $newsql = rex_sql::factory();
              // $newsql->debugsql = true;
              $sliceTable = $REX['TABLE_PREFIX'] . 'article_slice';
              $newsql->setTable($sliceTable);

              if ($function == 'edit')
              {
                $newsql->setWhere('id=' . $slice_id);
              }
              elseif ($function == 'add')
              {
                $newsql->setValue($sliceTable .'.re_article_slice_id', $slice_id);
                $newsql->setValue($sliceTable .'.article_id', $article_id);
                $newsql->setValue($sliceTable .'.modultyp_id', $module_id);
                $newsql->setValue($sliceTable .'.clang', $clang);
                $newsql->setValue($sliceTable .'.ctype', $ctype);
                $newsql->setValue($sliceTable .'.revision', $slice_revision);
              }

              // ****************** SPEICHERN FALLS NOETIG
              foreach ($REX['VARIABLES'] as $obj)
              {
                $obj->setACValues($newsql, $REX_ACTION, true);
              }

              if ($function == 'edit')
              {
                $newsql->addGlobalUpdateFields();
                if ($newsql->update())
                  $info = $action_message . $I18N->msg('block_updated');
                else
                  $warning = $action_message . $newsql->getError();

              }
              elseif ($function == 'add')
              {
                $newsql->addGlobalUpdateFields();
                $newsql->addGlobalCreateFields();
                if ($newsql->insert())
                {
                  $last_id = $newsql->getLastId();
                  if ($newsql->setQuery('UPDATE ' . $REX['TABLE_PREFIX'] . 'article_slice SET re_article_slice_id=' . $last_id . ' WHERE re_article_slice_id=' . $slice_id . ' AND id<>' . $last_id . ' AND article_id=' . $article_id . ' AND clang=' . $clang .' AND revision='.$slice_revision))
                  {
                    $info = $action_message . $I18N->msg('block_added');
                    $slice_id = $last_id;
                  }
                  $function = "";
                }
                else
                {
                  $warning = $action_message . $newsql->getError();
                }
              }
            }
            else
            {
              // make delete
              if(rex_deleteSlice($slice_id))
              {
                $global_info = $I18N->msg('block_deleted');
              }
              else
              {
                $global_warning = $I18N->msg('block_not_deleted');
              }
            }
            // ----- / SAVE SLICE

            // ----- artikel neu generieren
            $EA = rex_sql::factory();
            $EA->setTable($REX['TABLE_PREFIX'] . 'article');
            $EA->setWhere('id='. $article_id .' AND clang='. $clang);
            $EA->addGlobalUpdateFields();
            $EA->update();
            rex_deleteCacheArticle($article_id, $clang);

            rex_register_extension_point('ART_CONTENT_UPDATED', '',
              array (
                'id' => $article_id,
                'clang' => $clang
              )
            );
            
            // ----- POST SAVE ACTION [ADD/EDIT/DELETE]
            $info .= rex_execPostSaveAction($module_id, $function, $REX_ACTION);
            // ----- / POST SAVE ACTION

            // Update Button wurde gedrückt?
            // TODO: Workaround, da IE keine Button Namen beim
            // drücken der Entertaste übermittelt
            if (rex_post('btn_save', 'string'))
            {
              $function = '';
            }
          }
        }
      }
    }
    // ------------------------------------------ END: Slice add/edit/delete

    // ------------------------------------------ START: Slice move up/down
    if ($function == 'moveup' || $function == 'movedown')
    {
      if ($REX['USER']->hasPerm('moveSlice[]'))
      {
        // modul und rechte vorhanden ?

        $CM = rex_sql::factory();
        $CM->setQuery("select * from " . $REX['TABLE_PREFIX'] . "article_slice left join " . $REX['TABLE_PREFIX'] . "module on " . $REX['TABLE_PREFIX'] . "article_slice.modultyp_id=" . $REX['TABLE_PREFIX'] . "module.id where " . $REX['TABLE_PREFIX'] . "article_slice.id='$slice_id' and clang=$clang");
        if ($CM->getRows() != 1)
        {
          // ------------- START: MODUL IST NICHT VORHANDEN
          $warning = $I18N->msg('module_not_found');
          $slice_id = "";
          $function = "";
          // ------------- END: MODUL IST NICHT VORHANDEN
        }
        else
        {
        	$module_id = (int) $CM->getValue($REX['TABLE_PREFIX']."article_slice.modultyp_id");

          // ----- RECHTE AM MODUL ?
          if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm("module[$module_id]") || $REX['USER']->hasPerm("module[0]"))
          {
            // rechte sind vorhanden
            if ($function == "moveup" || $function == "movedown")
            {
              list($success, $message) = rex_moveSlice($slice_id, $clang, $function);
              
              if($success)
                $info = $message;
              else
                $warning = $message;
            }
          }
          else
          {
            $warning = $I18N->msg('no_rights_to_this_function');
          }
        }
      }
      else
      {
        $warning = $I18N->msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: Slice move up/down

		// ------------------------------------------ START: ARTICLE2STARTARTICLE
    if (rex_post('article2startpage', 'boolean'))
    {
      if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('article2startpage[]'))
      {
        if (rex_article2startpage($article_id))
        {
          // ----- EXTENSION POINT
          $info = $I18N->msg('content_tostartarticle_ok');
          header("Location:index.php?page=content&mode=meta&clang=$clang&ctype=$ctype&article_id=$article_id&info=".urlencode($info));
          exit;
        }
        else
        {
          $warning = $I18N->msg('content_tostartarticle_failed');
        }
      }
      else
      {
        $warning = $I18N->msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: ARTICLE2STARTARTICLE

    // ------------------------------------------ START: ARTICLE2CATEGORY
    if (rex_post('article2category', 'boolean'))
    {
      // article2category und category2article verwenden das gleiche Recht: article2category
      if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('article2category[]'))
      {
        if (rex_article2category($article_id))
        {
          // ----- EXTENSION POINT
          $info = $I18N->msg('content_tocategory_ok');
          header("Location:index.php?page=content&mode=meta&clang=$clang&ctype=$ctype&article_id=$article_id&info=".urlencode($info));
          exit;
        }
        else
        {
          $warning = $I18N->msg('content_tocategory_failed');
        }
      }
      else
      {
        $warning = $I18N->msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: ARTICLE2CATEGORY

    // ------------------------------------------ START: CATEGORY2ARTICLE
    if (rex_post('category2article', 'boolean'))
    {
      // article2category und category2article verwenden das gleiche Recht: article2category
      if ($REX['USER']->isAdmin() || ($REX['USER']->hasPerm('article2category[]') && $REX['USER']->hasCategoryPerm($article->getValue('re_id'))))
      {
        if (rex_category2article($article_id))
        {
          // ----- EXTENSION POINT
          $info = $I18N->msg('content_toarticle_ok');
          header("Location:index.php?page=content&mode=meta&clang=$clang&ctype=$ctype&article_id=$article_id&info=".urlencode($info));
          exit;
        }
        else
        {
          $warning = $I18N->msg('content_toarticle_failed');
        }
      }
      else
      {
        $warning = $I18N->msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: CATEGORY2ARTICLE

    // ------------------------------------------ START: COPY LANG CONTENT
    if (rex_post('copycontent', 'boolean'))
    {
      $clang_perm = $REX['USER']->getClangPerm();
      $clang_a = rex_post('clang_a', 'rex-clang-id');
      $clang_b = rex_post('clang_b', 'rex-clang-id');
      if ($REX['USER']->isAdmin() || ($REX['USER']->hasPerm('copyContent[]') && count($clang_perm) > 0 && in_array($clang_a, $clang_perm) && in_array($clang_b, $clang_perm)))
      {
        if (rex_copyContent($article_id, $article_id, $clang_a, $clang_b, 0, $slice_revision))
          $info = $I18N->msg('content_contentcopy');
        else
          $warning = $I18N->msg('content_errorcopy');
      }
      else
      {
        $warning = $I18N->msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: COPY LANG CONTENT

    // ------------------------------------------ START: MOVE ARTICLE
    if (rex_post('movearticle', 'boolean') && $category_id != $article_id)
    {
      $category_id_new = rex_post('category_id_new', 'rex-category-id');
      if ($REX['USER']->isAdmin() || ($REX['USER']->hasPerm('moveArticle[]') && $REX['USER']->hasCategoryPerm($category_id_new)))
      {
        if (rex_moveArticle($article_id, $category_id, $category_id_new))
        {
          $info = $I18N->msg('content_articlemoved');
          ob_end_clean();
          header('Location: index.php?page=content&article_id=' . $article_id . '&mode=meta&clang=' . $clang . '&ctype=' . $ctype . '&info=' . urlencode($info));
          exit;
        }
        else
        {
          $warning = $I18N->msg('content_errormovearticle');
        }
      }
      else
      {
        $warning = $I18N->msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: MOVE ARTICLE

    // ------------------------------------------ START: COPY ARTICLE
    if (rex_post('copyarticle', 'boolean'))
    {
    	$category_copy_id_new = rex_post('category_copy_id_new', 'rex-category-id');
      if ($REX['USER']->isAdmin() || ($REX['USER']->hasPerm('copyArticle[]') && $REX['USER']->hasCategoryPerm($category_copy_id_new)))
      {
        if (($new_id = rex_copyArticle($article_id, $category_copy_id_new)) !== false)
        {
          $info = $I18N->msg('content_articlecopied');
          ob_end_clean();
          header('Location: index.php?page=content&article_id=' . $new_id . '&mode=meta&clang=' . $clang . '&ctype=' . $ctype . '&info=' . urlencode($info));
          exit;
        }
        else
        {
          $warning = $I18N->msg('content_errorcopyarticle');
        }
      }
      else
      {
        $warning = $I18N->msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: COPY ARTICLE

    // ------------------------------------------ START: MOVE CATEGORY
    if (rex_post('movecategory', 'boolean'))
    {
    	$category_id_new = rex_post('category_id_new', 'rex-category-id');
      if ($REX['USER']->isAdmin() || ($REX['USER']->hasPerm('moveCategory[]') && $REX['USER']->hasCategoryPerm($article->getValue('re_id')) && $REX['USER']->hasCategoryPerm($category_id_new)))
      {
        if ($category_id != $category_id_new && rex_moveCategory($category_id, $category_id_new))
        {
          $info = $I18N->msg('category_moved');
          ob_end_clean();
          header('Location: index.php?page=content&article_id=' . $category_id . '&mode=meta&clang=' . $clang . '&ctype=' . $ctype . '&info=' . urlencode($info));
          exit;
        }
        else
        {
          $warning = $I18N->msg('content_error_movecategory');
        }
      }
      else
      {
        $warning = $I18N->msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: MOVE CATEGORY

    // ------------------------------------------ START: SAVE METADATA
    if (rex_post('savemeta', 'boolean'))
    {
      $meta_article_name = rex_post('meta_article_name', 'string');
      
      $meta_sql = rex_sql::factory();
      $meta_sql->setTable($REX['TABLE_PREFIX'] . "article");
      // $meta_sql->debugsql = 1;
      $meta_sql->setWhere("id='$article_id' AND clang=$clang");
      $meta_sql->setValue('name', $meta_article_name);
      $meta_sql->addGlobalUpdateFields();

      if($meta_sql->update())
      {
        $article->setQuery("SELECT * FROM " . $REX['TABLE_PREFIX'] . "article WHERE id='$article_id' AND clang='$clang'");
        $info = $I18N->msg("metadata_updated");

        rex_deleteCacheArticle($article_id, $clang);

        // ----- EXTENSION POINT
        $info = rex_register_extension_point('ART_META_UPDATED', $info, array (
          'id' => $article_id,
          'clang' => $clang,
          'name' => $meta_article_name,
        ));
      }
      else
      {
        $warning = $meta_sql->getError();
      }
    }
    // ------------------------------------------ END: SAVE METADATA

    // ------------------------------------------ START: CONTENT HEAD MENUE
    $num_ctypes = count($REX['CTYPE']);

    $ctype_menu = '';
    if ($num_ctypes > 0)
    {
      $listElements = array();

      if ($num_ctypes > 1)
        $listElements[] = $I18N->msg('content_types').': ';
      else
        $listElements[] = $I18N->msg('content_type').': ';

      $i = 1;
      foreach ($REX['CTYPE'] as $key => $val)
      {
        $s = '';
        $class = '';

        if ($key == $ctype && $mode == 'edit')
        {
        	$class = ' class="rex-active"';
        }

        $val = rex_translate($val);
        $s .= '<a href="index.php?page=content&amp;mode=edit&amp;clang=' . $clang . '&amp;ctype=' . $key . '&amp;category_id=' . $category_id . '&amp;article_id=' . $article_id . '"'. $class .''. rex_tabindex() .'>' . $val . '</a>';

        $listElements[] = $s;
        $i++;
      }

      // ----- EXTENSION POINT
      $listElements = rex_register_extension_point('PAGE_CONTENT_CTYPE_MENU', $listElements,
        array(
          'article_id' => $article_id,
          'clang' => $clang,
          'function' => $function,
          'mode' => $mode,
          'slice_id' => $slice_id
        )
      );

      $ctype_menu .= "\n".'<ul id="rex-navi-ctype">';
      $menu_counter = 0;
      foreach($listElements as $listElement)
      {
        $menu_counter++;
        
        $class = '';
        if($menu_counter == 2)
          $class = ' class="rex-navi-first"';
          
        $ctype_menu .= '<li'.$class.'>'.$listElement.'</li>';
  
      }
      $ctype_menu .= '</ul>';
    }

    $menu = $ctype_menu;
    $listElements = array();

    if ($mode == 'edit')
    {
      $listElements[] = '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=edit&amp;clang=' . $clang . '&amp;ctype=' . $ctype . '" class="rex-active"'. rex_tabindex() .'>' . $I18N->msg('edit_mode') . '</a>';
      $listElements[] = '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=meta&amp;clang=' . $clang . '&amp;ctype=' . $ctype . '"'. rex_tabindex() .'>' . $I18N->msg('metadata') . '</a>';
    }
    else
    {
      $listElements[] = '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=edit&amp;clang=' . $clang . '&amp;ctype=' . $ctype . '"'. rex_tabindex() .'>' . $I18N->msg('edit_mode') . '</a>';
      $listElements[] = '<a href="index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=meta&amp;clang=' . $clang . '&amp;ctype=' . $ctype . '" class="rex-active"'. rex_tabindex() .'>' . $I18N->msg('metadata') . '</a>';
    }

    $listElements[] = '<a href="../' . rex_getUrl($article_id,$clang) . '" onclick="window.open(this.href); return false;" '. rex_tabindex() .'>' . $I18N->msg('show') . '</a>';

    // ----- EXTENSION POINT
    $listElements = rex_register_extension_point('PAGE_CONTENT_MENU', $listElements,
      array(
        'article_id' => $article_id,
        'clang' => $clang,
        'function' => $function,
        'mode' => $mode,
        'slice_id' => $slice_id
      )
    );

    $menu .= "\n".'<ul class="rex-navi-content">';
    $num_elements = count($listElements);
    $menu_first = true;
    for($i = 0; $i < $num_elements; $i++)
    {
      $class = '';
      if($menu_first)
        $class = ' class="rex-navi-first"';
        
      $menu .= '<li'.$class.'>'. $listElements[$i] .'</li>';
      
      $menu_first = false;
    }
    $menu .= '</ul>';

    // ------------------------------------------ END: CONTENT HEAD MENUE

    // ------------------------------------------ START: AUSGABE
    echo '
            <!-- *** OUTPUT OF ARTICLE-CONTENT - START *** -->
            <div class="rex-content-header">
            <div class="rex-content-header-2">
              ' . $menu . '
              <div class="rex-clearer"></div>
            </div>
            </div>
            ';

    // ------------------------------------------ WARNING
    if($global_warning != '')
    {
      echo rex_warning($global_warning);
    }
    if($global_info != '')
    {
      echo rex_info($global_info);
    }
    if ($mode != 'edit')
    {
      if($warning != '')
      {
        echo rex_warning($warning);
      }
      if($info != '')
      {
        echo rex_info($info);
      }
    }

    echo '
            <div class="rex-content-body">
            <div class="rex-content-body-2">
            ';

    if ($mode == 'edit')
    {
      // ------------------------------------------ START: MODULE EDITIEREN/ADDEN ETC.

      echo '
                  <!-- *** OUTPUT OF ARTICLE-CONTENT-EDIT-MODE - START *** -->
                  <div class="rex-content-editmode">
                  ';
      $CONT = new rex_article_editor();
      $CONT->getContentAsQuery();
      $CONT->info = $info;
      $CONT->warning = $warning;
      $CONT->template_attributes = $template_attributes;
      $CONT->setArticleId($article_id);
      $CONT->setSliceId($slice_id);
      $CONT->setMode($mode);
      $CONT->setCLang($clang);
      $CONT->setEval(TRUE);
      $CONT->setSliceRevision($slice_revision);
      $CONT->setFunction($function);
      echo $CONT->getArticle($ctype);

      echo '
                  </div>
                  <!-- *** OUTPUT OF ARTICLE-CONTENT-EDIT-MODE - END *** -->
                  ';
      // ------------------------------------------ END: MODULE EDITIEREN/ADDEN ETC.

    }
    elseif ($mode == 'meta')
    {
      // ------------------------------------------ START: META VIEW

      echo '
    	  <div class="rex-form" id="rex-form-content-metamode">
          <form action="index.php" method="post" enctype="multipart/form-data" id="REX_FORM">
          	<div class="rex-form-section">
            <fieldset class="rex-form-col-1">
              <legend><span>' . $I18N->msg('general') . '</span></legend>

								<input type="hidden" name="page" value="content" />
								<input type="hidden" name="article_id" value="' . $article_id . '" />
								<input type="hidden" name="mode" value="meta" />
								<input type="hidden" name="save" value="1" />
								<input type="hidden" name="clang" value="' . $clang . '" />
								<input type="hidden" name="ctype" value="' . $ctype . '" />

				      	<div class="rex-form-wrapper">

									<div class="rex-form-row">
										<p class="rex-form-col-a rex-form-text">
						  				<label for="rex-form-meta-article-name">' . $I18N->msg("name_description") . '</label>
						  				<input class="rex-form-text" type="text" id="rex-form-meta-article-name" name="meta_article_name" value="' . htmlspecialchars($article->getValue("name")) . '" size="30"'. rex_tabindex() .' />
										</p>
									<div class="rex-clearer"></div>
									</div>
									<div class="rex-clearer"></div>';

      // ----- EXTENSION POINT
      echo rex_register_extension_point('ART_META_FORM', '', array (
        'id' => $article_id,
        'clang' => $clang,
        'article' => $article
      ));

      echo '

									<div class="rex-form-row">
										<p class="rex-form-col-a rex-form-submit">
								  		<input class="rex-form-submit" type="submit" name="savemeta" value="' . $I18N->msg("update_metadata") . '"'. rex_accesskey($I18N->msg('update_metadata'), $REX['ACKEY']['SAVE']) . rex_tabindex() .' />
										</p>
									</div>
									<div class="rex-clearer"></div>
								</div>
	           </fieldset>';

      // ----- EXTENSION POINT
      echo rex_register_extension_point('ART_META_FORM_SECTION', '', array (
        'id' => $article_id,
        'clang' => $clang
      ));
      
      echo '</div>';
      
      $isStartpage = $article->getValue('startpage') == 1;

      // ------------------------------------------------------------- SONSTIGES START
      
			$out = '';

			// --------------------------------------------------- ZUM STARTARTICLE MACHEN START
			if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('article2startpage[]'))
			{
				$out .= '
         		<fieldset class="rex-form-col-1">
         			<legend>' . $I18N->msg('content_startarticle') . '</legend>
         			<div class="rex-form-wrapper">
         				
         				<div class="rex-form-row">
         					<p class="rex-form-col-a';

				if (!$isStartpage && $article->getValue('re_id')==0)
					$out .= ' rex-form-read"><span class="rex-form-read">'.$I18N->msg('content_nottostartarticle').'</span>';
				else if ($isStartpage)
					$out .= ' rex-form-read"><span class="rex-form-read">'.$I18N->msg('content_isstartarticle').'</span>';
				else
					$out .= ' rex-form-submit"><input class="rex-form-submit" type="submit" name="article2startpage" value="' . $I18N->msg('content_tostartarticle') . '"'. rex_tabindex() .' onclick="return confirm(\'' . $I18N->msg('content_tostartarticle') . '?\')" />';

				$out .= '
									</p>
								</div>
							</div>
						</fieldset>';
			}
			// --------------------------------------------------- ZUM STARTARTICLE MACHEN END

      // --------------------------------------------------- IN KATEGORIE UMWANDELN START
			if (!$isStartpage && ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('article2category[]')))
			{
				$out .= '
         		<fieldset class="rex-form-col-1">
         			<legend>' . $I18N->msg('content_category') . '</legend>
         			<div class="rex-form-wrapper">
         				
         				<div class="rex-form-row">
         					<p class="rex-form-col-a rex-form-submit">
         					   <input class="rex-form-submit" type="submit" name="article2category" value="' . $I18N->msg('content_tocategory') . '"'. rex_tabindex() .' onclick="return confirm(\'' . $I18N->msg('content_tocategory') . '?\')" />
									</p>
								</div>
							</div>
						</fieldset>';
			}
			// --------------------------------------------------- IN KATEGORIE UMWANDELN END

      // --------------------------------------------------- IN ARTIKEL UMWANDELN START
			if ($isStartpage && ($REX['USER']->isAdmin() || ($REX['USER']->hasPerm('category2article[]') && $REX['USER']->hasCategoryPerm($article->getValue('re_id')))))
			{
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT pid FROM '. $REX['TABLE_PREFIX'] .'article WHERE re_id='. $article_id .' LIMIT 1');
        $emptyCategory = $sql->getRows() == 0;

				$out .= '
         		<fieldset class="rex-form-col-1">
         			<legend>' . $I18N->msg('content_article') . '</legend>
         			<div class="rex-form-wrapper">
         				
         				<div class="rex-form-row">
         					<p class="rex-form-col-a';

				if (!$emptyCategory)
					$out .= ' rex-form-read"><span class="rex-form-read">'.$I18N->msg('content_nottoarticle').'</span>';
				else
					$out .= ' rex-form-submit"><input class="rex-form-submit" type="submit" name="category2article" value="' . $I18N->msg('content_toarticle') . '"'. rex_tabindex() .' onclick="return confirm(\'' . $I18N->msg('content_toarticle') . '?\')" />';

				$out .= '
									</p>
								</div>
							</div>
						</fieldset>';
			}
			// --------------------------------------------------- IN ARTIKEL UMWANDELN END

      // --------------------------------------------------- INHALTE KOPIEREN START
      if (($REX['USER']->isAdmin() || $REX['USER']->hasPerm('copyContent[]')) && count($REX['USER']->getClangPerm()) > 1)
      {
        $clang_perm = $REX['USER']->getClangPerm();
        
        $lang_a = new rex_select;
				$lang_a->setStyle('class="rex-form-select"');
        $lang_a->setId('clang_a');
        $lang_a->setName('clang_a');
        $lang_a->setSize('1');
        $lang_a->setAttribute('tabindex', rex_tabindex(false));
        foreach ($clang_perm as $key)
        {
          $val = rex_translate($REX['CLANG'][$key]);
          $lang_a->addOption($val, $key);
        }

        $lang_b = new rex_select;
				$lang_b->setStyle('class="rex-form-select"');
        $lang_b->setId('clang_b');
        $lang_b->setName('clang_b');
        $lang_b->setSize('1');
        $lang_b->setAttribute('tabindex', rex_tabindex(false));
        foreach ($clang_perm as $key)
        {
          $val = rex_translate($REX['CLANG'][$key]);
          $lang_b->addOption($val, $key);
        }

        $lang_a->setSelected(rex_request('clang_a', 'rex-clang-id', null));
        $lang_b->setSelected(rex_request('clang_b', 'rex-clang-id', null));

        $out .= '
              <fieldset class="rex-form-col-2">
                <legend>' . $I18N->msg('content_submitcopycontent') . '</legend>
							  <div class="rex-form-wrapper">
							  
							  	<div class="rex-form-row">
									  <p class="rex-form-col-a rex-form-select">
											<label for="clang_a">' . $I18N->msg('content_contentoflang') . '</label>
											' . $lang_a->get() . '
										</p>
									  <p class="rex-form-col-b rex-form-select">
											<label for="clang_b">' . $I18N->msg('content_to') . '</label>
											' . $lang_b->get() . '
									  </p>
									 </div>
									 <div class="rex-form-row">
										 <p class="rex-form-col-a rex-form-submit">
											<input class="rex-form-submit" type="submit" name="copycontent" value="' . $I18N->msg('content_submitcopycontent') . '"'. rex_tabindex() .' onclick="return confirm(\'' . $I18N->msg('content_submitcopycontent') . '?\')" />
									  </p>
									 </div>
									 <div class="rex-clearer"></div>
							  </div>
              </fieldset>';

      }
      // --------------------------------------------------- INHALTE KOPIEREN ENDE

      // --------------------------------------------------- ARTIKEL VERSCHIEBEN START
      if (!$isStartpage && ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('moveArticle[]')))
      {

        // Wenn Artikel kein Startartikel dann Selectliste darstellen, sonst...
        $move_a = new rex_category_select(false, false, true, !$REX['USER']->hasMountPoints());
				$move_a->setStyle('class="rex-form-select"');
        $move_a->setId('category_id_new');
        $move_a->setName('category_id_new');
        $move_a->setSize('1');
        $move_a->setAttribute('tabindex', rex_tabindex(false));
        $move_a->setSelected($category_id);

        $out .= '
              <fieldset class="rex-form-col-1">
                <legend>' . $I18N->msg('content_submitmovearticle') . '</legend>

					      <div class="rex-form-wrapper">
					      
					      	<div class="rex-form-row">
								  	<p class="rex-form-col-a rex-form-select">
											<label for="category_id_new">' . $I18N->msg('move_article') . '</label>
											' . $move_a->get() . '
										</p>
									</div>
									
					      	<div class="rex-form-row">
									  <p class="rex-form-col-a rex-form-submit">
											<input class="rex-form-submit" type="submit" name="movearticle" value="' . $I18N->msg('content_submitmovearticle') . '"'. rex_tabindex() .' onclick="return confirm(\'' . $I18N->msg('content_submitmovearticle') . '?\')" />
									  </p>
									</div>
									
									<div class="rex-clearer"></div>
							  </div>
              </fieldset>';

      }
      // ------------------------------------------------ ARTIKEL VERSCHIEBEN ENDE

      // -------------------------------------------------- ARTIKEL KOPIEREN START
      if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm('copyArticle[]'))
      {
        $move_a = new rex_category_select(false, false, true, !$REX['USER']->hasMountPoints());
				$move_a->setStyle('class="rex-form-select"');
        $move_a->setName('category_copy_id_new');
        $move_a->setId('category_copy_id_new');
        $move_a->setSize('1');
        $move_a->setSelected($category_id);
        $move_a->setAttribute('tabindex', rex_tabindex(false));

        $out .= '
              <fieldset class="rex-form-col-1">
                <legend>' . $I18N->msg('content_submitcopyarticle') . '</legend>

					      <div class="rex-form-wrapper">
					      
					      	<div class="rex-form-row">
								  	<p class="rex-form-col-a rex-form-select">
											<label for="category_copy_id_new">' . $I18N->msg('copy_article') . '</label>
											' . $move_a->get() . '
									  </p>
									</div>
									
					      	<div class="rex-form-row">
									  <p class="rex-form-col-a rex-form-submit">
											<input class="rex-form-submit" type="submit" name="copyarticle" value="' . $I18N->msg('content_submitcopyarticle') . '"'. rex_tabindex() .' onclick="return confirm(\'' . $I18N->msg('content_submitcopyarticle') . '?\')" />
									  </p>
								  </div>
								  
								  <div class="rex-clearer"></div>
								</div>
              </fieldset>';

      }
      // --------------------------------------------------- ARTIKEL KOPIEREN ENDE

      // --------------------------------------------------- KATEGORIE/STARTARTIKEL VERSCHIEBEN START
      if ($isStartpage && ($REX['USER']->isAdmin() || ($REX['USER']->hasPerm('moveCategory[]') && $REX['USER']->hasCategoryPerm($article->getValue('re_id')))))
      {
        $move_a = new rex_category_select(false, false, true, !$REX['USER']->hasMountPoints());
				$move_a->setStyle('class="rex-form-select"');
        $move_a->setId('category_id_new');
        $move_a->setName('category_id_new');
        $move_a->setSize('1');
        $move_a->setSelected($article_id);
        $move_a->setAttribute('tabindex', rex_tabindex(false));

        $out .= '
              <fieldset class="rex-form-col-1">
                <legend>' . $I18N->msg('content_submitmovecategory') . '</legend>

					      <div class="rex-form-wrapper">
					      
					      	<div class="rex-form-row">
								  	<p class="rex-form-col-a rex-form-select">
											<label for="category_id_new">' . $I18N->msg('move_category') . '</label>
											' . $move_a->get() . '
									  </p>
									</div>
									
					      	<div class="rex-form-row">
									  <p class="rex-form-col-a rex-form-submit">
											<input class="rex-form-submit" type="submit" name="movecategory" value="' . $I18N->msg('content_submitmovecategory') . '"'. rex_tabindex() .' onclick="return confirm(\'' . $I18N->msg('content_submitmovecategory') . '?\')" />
									  </p>
									</div>

									<div class="rex-clearer"></div>
							  </div>
              </fieldset>';

      }
      // ------------------------------------------------ KATEGROIE/STARTARTIKEL VERSCHIEBEN ENDE
			
			if ($out != '')
			{	
				echo '<div class="rex-form-section">';
				echo $out;
				echo '</div>';
      }
      // ------------------------------------------------------------- SONSTIGES ENDE

      echo '
                  </form>
            	  </div>';

      // ------------------------------------------ END: META VIEW

    }

    echo '
            </div>
            </div>
            <!-- *** OUTPUT OF ARTICLE-CONTENT - END *** -->
            ';

    // ------------------------------------------ END: AUSGABE

  }
}