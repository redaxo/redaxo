<?php

/**
 * Verwaltung der Inhalte. EditierModul / Metadaten ...
 * @package redaxo5
 */

/*
// TODOS:
// - alles vereinfachen
// - <? ?> $ Problematik bei REX_ACTION
*/

$content = '';

require dirname(__FILE__) .'/../functions/function_rex_content.inc.php';


unset ($REX_ACTION);

$article_id  = rex_request('article_id',  'rex-article-id');
$clang       = rex_request('clang',       'rex-clang-id', rex::getProperty('start_clang_id'));
$slice_id    = rex_request('slice_id',    'rex-slice-id', '');
$function    = rex_request('function',    'string');

$article_revision = 0;
$slice_revision = 0;
$template_attributes = '';

$warning = '';
$global_warning = '';
$info = '';
$global_info = '';

$article = rex_sql::factory();
$article->setQuery("
    SELECT
      article.*, template.attributes as template_attributes
    FROM
      " . rex::getTablePrefix() . "article as article
    LEFT JOIN " . rex::getTablePrefix() . "template as template
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

  $ctypes = rex_getAttributes('ctype', $template_attributes, array ()); // ctypes - aus dem template

  $ctype = rex_request('ctype', 'rex-ctype-id', 1);
  if (!array_key_exists($ctype, $ctypes))
    $ctype = 1; // default = 1

  // ----- Artikel wurde gefunden - Kategorie holen
  $OOArt = rex_ooArticle::getArticleById($article_id, $clang);
  $category_id = $OOArt->getCategoryId();

  // ----- category pfad und rechte
  require rex_path::addon('structure', 'functions/function_rex_category.inc.php');
  // $KATout kommt aus dem include

  if (rex::getProperty('page') == 'content' && $article_id > 0)
  {
		$term = ($article->getValue('startpage') == 1) ? rex_i18n::msg('start_article') : rex_i18n::msg('article');
    	$catname = str_replace(' ', '&nbsp;', htmlspecialchars($article->getValue('name')));
    	// TODO: if admin or recht advanced -> $KATout .= " [$article_id]";

		$navigation = array();
		$navigation[] = array(
					"href" => 'index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=edit&amp;clang=' . $clang,
					"title" => $catname
				);
		$blocks = array();
		$blocks[] = array(
			"headline" => array ( "title" => $term),
			"navigation" => $navigation
			);

		$fragment = new rex_fragment();
		$fragment->setVar('type','path');
		$fragment->setVar('blocks', $blocks, false);
		$KATout .= $fragment->parse('navigation.tpl');
		unset($fragment);

  }

  // ----- Titel anzeigen
  echo rex_view::title(rex_i18n::msg('content'), $KATout);

  // ----- Request Parameter
  $mode     = rex_request('mode', 'string');
  $function = rex_request('function', 'string');
  $warning  = rex_request('warning', 'string');
  $info     = rex_request('info', 'string');

  // ----- mode defs
  if ($mode != 'meta' && $mode != 'metafuncs')
    $mode = 'edit';

  // ----- Sprachenblock
  $sprachen_add = '&amp;mode='. $mode .'&amp;category_id=' . $category_id . '&amp;article_id=' . $article_id;
  require rex_path::addon('structure', 'functions/function_rex_languages.inc.php');

  // ----- EXTENSION POINT
  echo rex_extension::registerPoint('PAGE_CONTENT_HEADER', '',
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
  if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id))
  {
    // ----- hat keine rechte an diesem artikel
    echo rex_view::warning(rex_i18n::msg('no_rights_to_edit'));
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
        $CM->setQuery("SELECT * FROM " . rex::getTablePrefix() . "article_slice LEFT JOIN " . rex::getTablePrefix() . "module ON " . rex::getTablePrefix() . "article_slice.modultyp_id=" . rex::getTablePrefix() . "module.id WHERE " . rex::getTablePrefix() . "article_slice.id='$slice_id' AND clang=$clang");
        if ($CM->getRows() == 1)
          $module_id = $CM->getValue("" . rex::getTablePrefix() . "article_slice.modultyp_id");
      }else
      {
        // add
        $module_id = rex_post('module_id', 'int');
        $CM->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'module WHERE id='.$module_id);
      }

      if ($CM->getRows() != 1)
      {
        // ------------- START: MODUL IST NICHT VORHANDEN
        $global_warning = rex_i18n::msg('module_not_found');
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
          $global_warning = rex_i18n::msg('no_rights_to_this_function');
          $slice_id = '';
          $function = '';

        }elseif (!(rex::getUser()->isAdmin() || rex::getUser()->getComplexPerm('modules')->hasPerm($module_id)))
        {
          // ----- RECHTE AM MODUL: NEIN
          $global_warning = rex_i18n::msg('no_rights_to_this_function');
          $slice_id = '';
          $function = '';
        }else
        {
          // ----- RECHTE AM MODUL: JA

          // ***********************  daten einlesen
          $REX_ACTION = array ();
          $REX_ACTION['SAVE'] = true;

          foreach (rex_var::getVars() as $obj)
          {
            $REX_ACTION = $obj->getACRequestValues($REX_ACTION);
          }

          // ----- PRE SAVE ACTION [ADD/EDIT/DELETE]
          list($action_message, $REX_ACTION) = rex_execPreSaveAction($module_id, $function, $REX_ACTION);
          // ----- / PRE SAVE ACTION

          // Statusspeicherung für die rex_article Klasse
          rex_plugin::get('structure', 'content')->setProperty('rex_action', $REX_ACTION);

          // Werte werden aus den REX_ACTIONS übernommen wenn SAVE=true
          if (!$REX_ACTION['SAVE'])
          {
            // ----- DONT SAVE/UPDATE SLICE
            if ($action_message != '')
              $warning = $action_message;
            elseif ($function == 'delete')
              $warning = rex_i18n::msg('slice_deleted_error');
            else
              $warning = rex_i18n::msg('slice_saved_error');

          }
          else
          {
            // ----- SAVE/UPDATE SLICE
            if ($function == 'add' || $function == 'edit')
            {

              $newsql = rex_sql::factory();
              // $newsql->debugsql = true;
              $sliceTable = rex::getTablePrefix() . 'article_slice';
              $newsql->setTable($sliceTable);

              if ($function == 'edit')
              {
                $newsql->setWhere(array('id' => $slice_id));
              }
              elseif ($function == 'add')
              {
                // determine prior value to get the new slice into the right order
                $prevSlice = rex_sql::factory();
                // $prevSlice->debugsql = true;
                if($slice_id == -1) // -1 is used when adding after the last article-slice
                  $prevSlice->setQuery('SELECT IFNULL(MAX(prior),0)+1 as prior FROM '. $sliceTable . ' WHERE article_id='. $article_id . ' AND clang='. $clang .' AND ctype='. $ctype . ' AND revision='. $slice_revision);
                else
                  $prevSlice->setQuery('SELECT * FROM '. $sliceTable . ' WHERE id='. $slice_id);

                $prior = $prevSlice->getValue('prior');

                $newsql->setValue('article_id', $article_id);
                $newsql->setValue('modultyp_id', $module_id);
                $newsql->setValue('clang', $clang);
                $newsql->setValue('ctype', $ctype);
                $newsql->setValue('revision', $slice_revision);
                $newsql->setValue('prior', $prior);
              }

              // ****************** SPEICHERN FALLS NOETIG
              foreach (rex_var::getVars() as $obj)
              {
                $obj->setACValues($newsql, $REX_ACTION);
              }

              if ($function == 'edit')
              {
                $newsql->addGlobalUpdateFields();
                try {
                  $newsql->update();
                  $info = $action_message . rex_i18n::msg('block_updated');
                } catch (rex_sql_exception $e)
                {
                  $warning = $action_message . $e->getMessage();
                }

              }
              elseif ($function == 'add')
              {
                $newsql->addGlobalUpdateFields();
                $newsql->addGlobalCreateFields();

                try {
                  $newsql->insert();

                  rex_organize_priorities(
                    rex::getTablePrefix() . 'article_slice',
                    'prior',
                    'article_id=' . $article_id . ' AND clang=' . $clang .' AND ctype='. $ctype .' AND revision='. $slice_revision,
                    'prior, updatedate DESC'
                  );

                  $info = $action_message . rex_i18n::msg('block_added');
                  $slice_id = $newsql->getLastId();
                  $function = "";
                } catch (rex_sql_exception $e)
                {
                  $warning = $action_message . $e->getMessage();
                }
              }
            }
            else
            {
              // make delete
              if(rex_content_service::deleteSlice($slice_id))
              {
                $global_info = rex_i18n::msg('block_deleted');
              }
              else
              {
                $global_warning = rex_i18n::msg('block_not_deleted');
              }
            }
            // ----- / SAVE SLICE

            // ----- artikel neu generieren
            $EA = rex_sql::factory();
            $EA->setTable(rex::getTablePrefix() . 'article');
            $EA->setWhere(array('id' => $article_id, 'clang' => $clang));
            $EA->addGlobalUpdateFields();
            $EA->update();
            rex_article_cache::delete($article_id, $clang);

            rex_extension::registerPoint('ART_CONTENT_UPDATED', '',
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

    // ------------------------------------------ START: COPY LANG CONTENT
    if (rex_post('copycontent', 'boolean'))
    {
      $clang_a = rex_post('clang_a', 'rex-clang-id');
      $clang_b = rex_post('clang_b', 'rex-clang-id');
      $user = rex::getUser();
      if ($user->isAdmin() || ($user->hasPerm('copyContent[]') && $user->getComplexPerm('clang')->hasPerm($clang_a) && $user->getComplexPerm('clang')->hasPerm($clang_b)))
      {
        if (rex_content_service::copyContent($article_id, $article_id, $clang_a, $clang_b, 0, $slice_revision))
          $info = rex_i18n::msg('content_contentcopy');
        else
          $warning = rex_i18n::msg('content_errorcopy');
      }
      else
      {
        $warning = rex_i18n::msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: COPY LANG CONTENT

    // ------------------------------------------ START: MOVE ARTICLE
    if (rex_post('movearticle', 'boolean') && $category_id != $article_id)
    {
      $category_id_new = rex_post('category_id_new', 'rex-category-id');
      if (rex::getUser()->isAdmin() || (rex::getUser()->hasPerm('moveArticle[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id_new)))
      {
        if (rex_article_service::moveArticle($article_id, $category_id, $category_id_new))
        {
          $info = rex_i18n::msg('content_articlemoved');
          ob_end_clean();
          header('Location: index.php?page=content&article_id=' . $article_id . '&mode=meta&clang=' . $clang . '&ctype=' . $ctype . '&info=' . urlencode($info));
          exit;
        }
        else
        {
          $warning = rex_i18n::msg('content_errormovearticle');
        }
      }
      else
      {
        $warning = rex_i18n::msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: MOVE ARTICLE

    // ------------------------------------------ START: COPY ARTICLE
    if (rex_post('copyarticle', 'boolean'))
    {
      $category_copy_id_new = rex_post('category_copy_id_new', 'rex-category-id');
      if (rex::getUser()->isAdmin() || (rex::getUser()->hasPerm('copyArticle[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_copy_id_new)))
      {
        if (($new_id = rex_article_service::copyArticle($article_id, $category_copy_id_new)) !== false)
        {
          $info = rex_i18n::msg('content_articlecopied');
          ob_end_clean();
          header('Location: index.php?page=content&article_id=' . $new_id . '&mode=meta&clang=' . $clang . '&ctype=' . $ctype . '&info=' . urlencode($info));
          exit;
        }
        else
        {
          $warning = rex_i18n::msg('content_errorcopyarticle');
        }
      }
      else
      {
        $warning = rex_i18n::msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: COPY ARTICLE

    // ------------------------------------------ START: MOVE CATEGORY
    if (rex_post('movecategory', 'boolean'))
    {
      $category_id_new = rex_post('category_id_new', 'rex-category-id');
      if (rex::getUser()->isAdmin() || (rex::getUser()->hasPerm('moveCategory[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getValue('re_id')) && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id_new)))
      {
        if ($category_id != $category_id_new && rex_category_service::moveCategory($category_id, $category_id_new))
        {
          $info = rex_i18n::msg('category_moved');
          ob_end_clean();
          header('Location: index.php?page=content&article_id=' . $category_id . '&mode=meta&clang=' . $clang . '&ctype=' . $ctype . '&info=' . urlencode($info));
          exit;
        }
        else
        {
          $warning = rex_i18n::msg('content_error_movecategory');
        }
      }
      else
      {
        $warning = rex_i18n::msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: MOVE CATEGORY

    // ------------------------------------------ START: SAVE METADATA
    if (rex_post('savemeta', 'boolean'))
    {
      $meta_article_name = rex_post('meta_article_name', 'string');

      $meta_sql = rex_sql::factory();
      $meta_sql->setTable(rex::getTablePrefix() . "article");
      // $meta_sql->debugsql = 1;
      $meta_sql->setWhere(array('id' => $article_id, 'clang' => $clang));
      $meta_sql->setValue('name', $meta_article_name);
      $meta_sql->addGlobalUpdateFields();

      try {
        $meta_sql->update();

        $article->setQuery("SELECT * FROM " . rex::getTablePrefix() . "article WHERE id='$article_id' AND clang='$clang'");
        $info = rex_i18n::msg("metadata_updated");

        rex_article_cache::delete($article_id, $clang);

        // ----- EXTENSION POINT
        $info = rex_extension::registerPoint('ART_META_UPDATED', $info, array (
          'id' => $article_id,
          'clang' => $clang,
          'name' => $meta_article_name,
        ));
      } catch (rex_sql_exception $e)
      {
        $warning = $e->getMessage();
      }
    }
    // ------------------------------------------ END: SAVE METADATA

    // ------------------------------------------ START: CONTENT HEAD MENUE
    $num_ctypes = count($ctypes);

	$listElements = array();

    $ctype_menu = '';
    if ($num_ctypes > 0)
    {
      
      foreach ($ctypes as $key => $val)
      {
        
   		$n = array();
		$n["title"] = rex_i18n::translate($val);
		$n["href"] = 'index.php?page=content&amp;mode=edit&amp;clang=' . $clang . '&amp;ctype=' . $key . '&amp;category_id=' . $category_id . '&amp;article_id=' . $article_id;
		if ($key == $ctype && $mode == 'edit')
        {
			$n["linkClasses"] = array('rex-active');
			$n["itemClasses"] = array('rex-active');
	    }
		$listElements[] = $n;
        
      }

      // ----- EXTENSION POINT
      $listElements = rex_extension::registerPoint('PAGE_CONTENT_CTYPE_MENU', $listElements,
        array(
          'article_id' => $article_id,
          'clang' => $clang,
          'function' => $function,
          'mode' => $mode,
          'slice_id' => $slice_id
        )
      );

      if ($num_ctypes > 1)
        $ctype_menu .= rex_i18n::msg('content_types');
      else
        $ctype_menu .= rex_i18n::msg('content_type');

    }

    // $listElements = array();

	$n = array();
	$n["title"] = rex_i18n::msg('show');
	$n["href"] = rex_getUrl($article_id, $clang);
	$n["itemClasses"] = array('rex-misc');
	$n["linkAttr"] = array("onClick" => 'window.open(this.href); return false;');
	$listElements[] = $n;
	
	$n = array();
	$n["title"] = rex_i18n::msg('metafuncs');
	$n["href"] = 'index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=metafuncs&amp;clang=' . $clang . '&amp;ctype=' . $ctype;
	$n["itemClasses"] = array('rex-misc');
    if ($mode == 'metafuncs') {
		$n["linkClasses"] = array('rex-active');
		$n["itemClasses"] = array('rex-active','rex-misc');
    }
	$listElements[] = $n;

	$n = array();
	$n["title"] = rex_i18n::msg('metadata');
	$n["href"] = 'index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=meta&amp;clang=' . $clang . '&amp;ctype=' . $ctype;
	$n["itemClasses"] = array('rex-misc');
    if ($mode == 'meta') {
		$n["linkClasses"] = array('rex-active');
		$n["itemClasses"] = array('rex-active','rex-misc');
    }
	$listElements[] = $n;
	
	$n = array();
	$n["title"] = rex_i18n::msg('edit_mode');
	$n["href"] = 'index.php?page=content&amp;article_id=' . $article_id . '&amp;mode=edit&amp;clang=' . $clang . '&amp;ctype=' . $ctype;
	$n["itemClasses"] = array('rex-misc');
    if ($mode != 'meta' && $mode != 'metafuncs') {
		$n["linkClasses"] = array('rex-active');
		$n["itemClasses"] = array('rex-active','rex-misc');
    }
	$listElements[] = $n;

    // ----- EXTENSION POINT
    $listElements = rex_extension::registerPoint('PAGE_CONTENT_MENU', $listElements,
      array(
        'article_id' => $article_id,
        'clang' => $clang,
        'function' => $function,
        'mode' => $mode,
        'slice_id' => $slice_id
      )
    );
	
	$blocks = array();
	$blocks[] = array(
				"headline" => array("title" => "meeeta"), 
				"navigation" => $listElements
				);
	
	$fragment = new rex_fragment();
	$fragment->setVar('type','tab');
	$fragment->setVar('blocks', $blocks, false);
	echo $fragment->parse('navigation.tpl');


    // ------------------------------------------ END: CONTENT HEAD MENUE

    // ------------------------------------------ WARNING
    if($global_warning != '')
    {
      echo rex_view::warning($global_warning);
    }
    if($global_info != '')
    {
      echo rex_view::success($global_info);
    }

    // --------------------------------------------- API MESSAGES
    echo rex_api_function::getMessage();

    if($warning != '')
    {
      echo rex_view::warning($warning);
    }
    if($info != '')
    {
      echo rex_view::success($info);
    }


    // ------------------------------------------ START: MODULE EDITIEREN/ADDEN ETC.
    if ($mode == 'edit')
    {

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
      $content .= $CONT->getArticle($ctype);
      
      echo rex_view::contentBlock($content,'','block');            
    
    // ------------------------------------------ START: META VIEW
    }elseif ($mode == 'meta')
    {

      $content .= '
        <div class="rex-form" id="rex-form-content-metamode">
          <form action="index.php" method="post" enctype="multipart/form-data" id="REX_FORM">
            <fieldset>
              <h2>' . rex_i18n::msg('general') . '</h2>

                <input type="hidden" name="page" value="content" />
                <input type="hidden" name="article_id" value="' . $article_id . '" />
                <input type="hidden" name="mode" value="meta" />
                <input type="hidden" name="save" value="1" />
                <input type="hidden" name="clang" value="' . $clang . '" />
                <input type="hidden" name="ctype" value="' . $ctype . '" />
                ';
                
                $formElements = array();
                
                $n = array();
                $n['label'] = '<label for="rex-form-meta-article-name">' . rex_i18n::msg("name_description") . '</label>';
                $n['field'] = '<input type="text" id="rex-form-meta-article-name" name="meta_article_name" value="' . htmlspecialchars($article->getValue("name")) . '" />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $content .= $fragment->parse('form.tpl');


      // ----- EXTENSION POINT
      $content .= rex_extension::registerPoint('ART_META_FORM', '', array (
        'id' => $article_id,
        'clang' => $clang,
        'article' => $article
      ));
                
      $content .= '
             </fieldset>
             <fieldset class="rex-form-action">';
                $formElements = array();
                
                $n = array();
                $n['field'] = '<input type="submit" name="savemeta" value="' . rex_i18n::msg("update_metadata") . '"'. rex::getAccesskey(rex_i18n::msg('update_metadata'), 'save') .' />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $content .= $fragment->parse('form.tpl');

      $content .= '
             </fieldset>';

      // ----- EXTENSION POINT
      $content .= rex_extension::registerPoint('ART_META_FORM_SECTION', '', array (
        'id' => $article_id,
        'clang' => $clang
      ));

      $content .= '
                  </form>
                </div>';

	  echo rex_view::contentBlock($content, '', 'block');

    // ------------------------------------------ START: META FUNCS
    }elseif ($mode == 'metafuncs')
    {

      $content .= '
        <div class="rex-form" id="rex-form-content-metamode">
          <form action="index.php" method="post" enctype="multipart/form-data" id="REX_FORM">
                <input type="hidden" name="page" value="content" />
                <input type="hidden" name="article_id" value="' . $article_id . '" />
                <input type="hidden" name="mode" value="metafuncs" />
                <input type="hidden" name="save" value="1" />
                <input type="hidden" name="clang" value="' . $clang . '" />
                <input type="hidden" name="ctype" value="' . $ctype . '" />
                <input type="hidden" name="rex-api-call" id="apiField">
                ';


      $isStartpage = $article->getValue('startpage') == 1;
      $out = '';

      // --------------------------------------------------- ZUM STARTARTICLE MACHEN START
      if (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('article2startpage[]'))
      {
        $out .= '
            <fieldset>
              <h2>' . rex_i18n::msg('content_startarticle') . '</h2>';
    
                $formElements = array();
                
                $n = array();
                if (!$isStartpage && $article->getValue('re_id')==0)
                  $n['field'] = '<span class="rex-form-read">'.rex_i18n::msg('content_nottostartarticle').'</span>';
                else if ($isStartpage)
                  $n['field'] = '<span class="rex-form-read">'.rex_i18n::msg('content_isstartarticle').'</span>';
                else
                  $n['field'] = '<input type="submit" name="article2startpage" value="' . rex_i18n::msg('content_tostartarticle') . '" onclick="return confirm(\'' . rex_i18n::msg('content_tostartarticle') . '?\') && jQuery(\'#apiField\').val(\'article2startpage\');" />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $out .= $fragment->parse('form.tpl');
                


        $out .= '</fieldset>';
      }
      
      // --------------------------------------------------- ZUM STARTARTICLE MACHEN END

      // --------------------------------------------------- IN KATEGORIE UMWANDELN START
      if (!$isStartpage && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('article2category[]')))
      {
        $out .= '
            <fieldset>
              <h2>' . rex_i18n::msg('content_category') . '</h2>';
              
    
                $formElements = array();
                
                $n = array();
                $n['field'] = '<input type="submit" name="article2category" value="' . rex_i18n::msg('content_tocategory') . '" onclick="return confirm(\'' . rex_i18n::msg('content_tocategory') . '?\') && jQuery(\'#apiField\').val(\'article2category\');" />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $out .= $fragment->parse('form.tpl');

                
        $out .= '</fieldset>';
      }
      // --------------------------------------------------- IN KATEGORIE UMWANDELN END

      // --------------------------------------------------- IN ARTIKEL UMWANDELN START
      if ($isStartpage && (rex::getUser()->isAdmin() || (rex::getUser()->hasPerm('category2article[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getValue('re_id')))))
      {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT pid FROM '. rex::getTablePrefix() .'article WHERE re_id='. $article_id .' LIMIT 1');
        $emptyCategory = $sql->getRows() == 0;

        $out .= '
            <fieldset>
              <h2>' . rex_i18n::msg('content_article') . '</h2>';
              
    
                $formElements = array();
                
                $n = array();
                if (!$emptyCategory)
                  $n['field'] = '<span class="rex-form-read">'.rex_i18n::msg('content_nottoarticle').'</span>';
                else
                  $n['field'] = '<input type="submit" name="category2article" value="' . rex_i18n::msg('content_toarticle') . '" onclick="return confirm(\'' . rex_i18n::msg('content_toarticle') . '?\') && jQuery(\'#apiField\').val(\'category2article\');" />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $out .= $fragment->parse('form.tpl');

        $out .= '</fieldset>';
      }
      // --------------------------------------------------- IN ARTIKEL UMWANDELN END

      // --------------------------------------------------- INHALTE KOPIEREN START
      $user = rex::getUser();
      if (($user->isAdmin() || $user->hasPerm('copyContent[]')) && $user->getComplexPerm('clang')->count() > 1)
      {
        $clang_perm = $user->getComplexPerm('clang')->getClangs();

        $lang_a = new rex_select;
        $lang_a->setId('clang_a');
        $lang_a->setName('clang_a');
        $lang_a->setSize('1');
        foreach ($clang_perm as $key)
        {
          $val = rex_i18n::translate(rex_clang::getName($key));
          $lang_a->addOption($val, $key);
        }

        $lang_b = new rex_select;
        $lang_b->setId('clang_b');
        $lang_b->setName('clang_b');
        $lang_b->setSize('1');
        foreach ($clang_perm as $key)
        {
          $val = rex_i18n::translate(rex_clang::getName($key));
          $lang_b->addOption($val, $key);
        }

        $lang_a->setSelected(rex_request('clang_a', 'rex-clang-id', null));
        $lang_b->setSelected(rex_request('clang_b', 'rex-clang-id', null));

        $out .= '
              <fieldset>
                <h2>' . rex_i18n::msg('content_submitcopycontent') . '</h2>';
   
                $formElements = array();
                
                $n = array();
                $n['label'] = '<label for="clang_a">' . rex_i18n::msg('content_contentoflang') . '</label>';
                $n['field'] = $lang_a->get();
                $formElements[] = $n;
                
                $n = array();
                $n['label'] = '<label for="clang_b">' . rex_i18n::msg('content_to') . '</label>';
                $n['field'] = $lang_b->get();
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('columns', 2, false);
                $fragment->setVar('elements', $formElements, false);
                $out .= $fragment->parse('form.tpl');
   
   
                $formElements = array();
                
                $n = array();
                $n['field'] = '<input type="submit" name="copycontent" value="' . rex_i18n::msg('content_submitcopycontent') . '" onclick="return confirm(\'' . rex_i18n::msg('content_submitcopycontent') . '?\')" />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $out .= $fragment->parse('form.tpl');
                
                
        $out .= '</fieldset>';

      }
      // --------------------------------------------------- INHALTE KOPIEREN ENDE

      // --------------------------------------------------- ARTIKEL VERSCHIEBEN START
      if (!$isStartpage && (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('moveArticle[]')))
      {

        // Wenn Artikel kein Startartikel dann Selectliste darstellen, sonst...
        $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
        $move_a->setId('category_id_new');
        $move_a->setName('category_id_new');
        $move_a->setSize('1');
        $move_a->setSelected($category_id);

        $out .= '
              <fieldset>
                <h2>' . rex_i18n::msg('content_submitmovearticle') . '</h2>';

   
                $formElements = array();
                
                $n = array();
                $n['label'] = '<label for="category_id_new">' . rex_i18n::msg('move_article') . '</label>';
                $n['field'] = $move_a->get();
                $formElements[] = $n;
                
                $n = array();
                $n['field'] = '<input type="submit" name="movearticle" value="' . rex_i18n::msg('content_submitmovearticle') . '" onclick="return confirm(\'' . rex_i18n::msg('content_submitmovearticle') . '?\')" />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $out .= $fragment->parse('form.tpl');
         
        $out .= '</fieldset>';

      }
      // ------------------------------------------------ ARTIKEL VERSCHIEBEN ENDE

      // -------------------------------------------------- ARTIKEL KOPIEREN START
      if (rex::getUser()->isAdmin() || rex::getUser()->hasPerm('copyArticle[]'))
      {
        $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
        $move_a->setName('category_copy_id_new');
        $move_a->setId('category_copy_id_new');
        $move_a->setSize('1');
        $move_a->setSelected($category_id);

        $out .= '
              <fieldset>
                <h2>' . rex_i18n::msg('content_submitcopyarticle') . '</h2>';

   
                $formElements = array();
                
                $n = array();
                $n['label'] = '<label for="category_copy_id_new">' . rex_i18n::msg('copy_article') . '</label>';
                $n['field'] = $move_a->get();
                $formElements[] = $n;
                
                $n = array();
                $n['field'] = '<input class="rex-form-submit" type="submit" name="copyarticle" value="' . rex_i18n::msg('content_submitcopyarticle') . '" onclick="return confirm(\'' . rex_i18n::msg('content_submitcopyarticle') . '?\')" />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $out .= $fragment->parse('form.tpl');
                
        $out .= '</fieldset>';

      }
      // --------------------------------------------------- ARTIKEL KOPIEREN ENDE

      // --------------------------------------------------- KATEGORIE/STARTARTIKEL VERSCHIEBEN START
      if ($isStartpage && (rex::getUser()->isAdmin() || (rex::getUser()->hasPerm('moveCategory[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getValue('re_id')))))
      {
        $move_a = new rex_category_select(false, false, true, !rex::getUser()->getComplexPerm('structure')->hasMountPoints());
        $move_a->setId('category_id_new');
        $move_a->setName('category_id_new');
        $move_a->setSize('1');
        $move_a->setSelected($article_id);

        $out .= '
              <fieldset>
                <h2>' . rex_i18n::msg('content_submitmovecategory') . '</h2>';

   
                $formElements = array();
                
                $n = array();
                $n['label'] = '<label for="category_id_new">' . rex_i18n::msg('move_category') . '</label>';
                $n['field'] = $move_a->get();
                $formElements[] = $n;
                
                $n = array();
                $n['field'] = '<input class="rex-form-submit" type="submit" name="movecategory" value="' . rex_i18n::msg('content_submitmovecategory') . '" onclick="return confirm(\'' . rex_i18n::msg('content_submitmovecategory') . '?\')" />';
                $formElements[] = $n;
                
                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $out .= $fragment->parse('form.tpl');
            
        $out .= '</fieldset>';

      }
      // ------------------------------------------------ KATEGROIE/STARTARTIKEL VERSCHIEBEN ENDE

      $content .= $out;
      $content .= '
                  </form>
                </div>';

	  echo rex_view::contentBlock($content, '', 'block');

    }


    // ------------------------------------------ END: AUSGABE

  }
}