<?php

/**
 * Verwaltung der Inhalte. EditierModul / Metadaten ...
 * @package redaxo5
 */

/*
// TODOS:
// - alles vereinfachen
// - <?php ?> $ Problematik bei REX_ACTION
*/

$content = '';

$article_id  = rex_request('article_id',  'int');
$clang       = rex_request('clang',       'int');
$slice_id    = rex_request('slice_id',    'int', '');
$function    = rex_request('function',    'string');

$article_id = rex_article::getArticleById($article_id) instanceof rex_article ? $article_id : 0;
$clang = rex_clang::exists($clang) ? $clang : rex::getProperty('start_clang_id');

$article_revision = 0;
$slice_revision = 0;
$template_attributes = array();

$warning = '';
$global_warning = '';
$info = '';
$global_info = '';

$article = rex_sql::factory();
$article->setQuery('
    SELECT
      article.*, template.attributes as template_attributes
    FROM
      ' . rex::getTablePrefix() . 'article as article
    LEFT JOIN ' . rex::getTablePrefix() . "template as template
      ON template.id=article.template_id
    WHERE
      article.id='$article_id'
      AND clang=$clang");


if ($article->getRows() == 1) {
  // ----- ctype holen
  $template_attributes = $article->getArrayValue('template_attributes');

  // Für Artikel ohne Template
  if (!is_array($template_attributes))
    $template_attributes = array();

  $ctypes = isset($template_attributes['ctype']) ? $template_attributes['ctype'] : array(); // ctypes - aus dem template

  $ctype = rex_request('ctype', 'int', 1);
  if (!array_key_exists($ctype, $ctypes))
    $ctype = 1; // default = 1

  // ----- Artikel wurde gefunden - Kategorie holen
  $OOArt = rex_article::getArticleById($article_id, $clang);
  $category_id = $OOArt->getCategoryId();


  // ----- Request Parameter
  $subpage  = rex_be_controller::getCurrentPagePart(2);
  $function = rex_request('function', 'string');
  $warning  = htmlspecialchars(rex_request('warning', 'string'));
  $info     = htmlspecialchars(rex_request('info', 'string'));

  $context = new rex_context(array(
    'page' => rex_be_controller::getCurrentPage(),
    'article_id' => $article_id,
    'clang' => $clang,
    'ctype' => $ctype
  ));

  // ----- Languages
  echo rex_view::clangSwitch($context);

  // ----- Titel anzeigen
  echo rex_view::title(rex_i18n::msg('content'), '');

  // ----- category pfad und rechte
  require rex_path::addon('structure', 'functions/function_rex_category.php');


  if (rex_be_controller::getCurrentPagePart(1) == 'content' && $article_id > 0) {

    $term = ($article->getValue('startpage') == 1) ? rex_i18n::msg('start_article') : rex_i18n::msg('article');
    $catname = str_replace(' ', '&nbsp;', htmlspecialchars($article->getValue('name')));
    // TODO: if admin or recht advanced -> $KATout .= " [$article_id]";

    $navigation = array();
    $navigation[] = array(
      'href' => $context->getUrl(),
      'title' => $catname
    );

    $fragment = new rex_fragment();
    $fragment->setVar('title', $term);
    $fragment->setVar('items', $navigation, false);
    echo $fragment->parse('core/navigations/path.tpl');
    unset($fragment);

  }



  // ----- EXTENSION POINT
  echo rex_extension::registerPoint('PAGE_CONTENT_HEADER', '',
    array(
      'article_id' => $article_id,
      'clang' => $clang,
      'function' => $function,
      'slice_id' => $slice_id,
      'page' => rex_be_controller::getCurrentPage(),
      'ctype' => $ctype,
      'category_id' => $category_id,
      'article_revision' => &$article_revision,
      'slice_revision' => &$slice_revision,
    )
  );

  // --------------------- SEARCH BAR

  require_once $this->getAddon()->getPath('functions/function_rex_searchbar.php');
  echo rex_structure_searchbar($context);

  // ----------------- HAT USER DIE RECHTE AN DIESEM ARTICLE ODER NICHT
  if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
    // ----- hat keine rechte an diesem artikel
    echo rex_view::warning(rex_i18n::msg('no_rights_to_edit'));
  } else {
    // ----- hat rechte an diesem artikel

    // ------------------------------------------ Slice add/edit/delete
    if (rex_request('save', 'boolean') && in_array($function, array('add', 'edit', 'delete'))) {
      // ----- check module

      $CM = rex_sql::factory();
      if ($function == 'edit' || $function == 'delete') {
        // edit/ delete
        $CM->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article_slice LEFT JOIN ' . rex::getTablePrefix() . 'module ON ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id WHERE ' . rex::getTablePrefix() . "article_slice.id='$slice_id' AND clang=$clang");
        if ($CM->getRows() == 1)
          $module_id = $CM->getValue('' . rex::getTablePrefix() . 'article_slice.module_id');
      } else {
        // add
        $module_id = rex_post('module_id', 'int');
        $CM->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'module WHERE id=' . $module_id);
      }

      if ($CM->getRows() != 1) {
        // ------------- START: MODUL IST NICHT VORHANDEN
        $global_warning = rex_i18n::msg('module_not_found');
        $slice_id = '';
        $function = '';
        // ------------- END: MODUL IST NICHT VORHANDEN
      } else {
        // ------------- MODUL IST VORHANDEN

        // ----- RECHTE AM MODUL ?
        if ($function != 'delete' && !rex_template::hasModule($template_attributes, $ctype, $module_id)) {
          $global_warning = rex_i18n::msg('no_rights_to_this_function');
          $slice_id = '';
          $function = '';

        } elseif (!rex::getUser()->getComplexPerm('modules')->hasPerm($module_id)) {
          // ----- RECHTE AM MODUL: NEIN
          $global_warning = rex_i18n::msg('no_rights_to_this_function');
          $slice_id = '';
          $function = '';
        } else {
          // ----- RECHTE AM MODUL: JA

          // ***********************  daten einlesen

          $newsql = rex_sql::factory();
          // $newsql->setDebug();

          // ----- PRE SAVE ACTION [ADD/EDIT/DELETE]
          $action = new rex_article_action($module_id, $function, $newsql);
          $action->setRequestValues();
          $action->exec(rex_article_action::PRESAVE);
          $action_message = implode('<br />', $action->getMessages());
          // ----- / PRE SAVE ACTION

          // Werte werden aus den REX_ACTIONS übernommen wenn SAVE=true
          if (!$action->getSave()) {
            // ----- DONT SAVE/UPDATE SLICE
            if ($action_message != '')
              $warning = $action_message;
            elseif ($function == 'delete')
              $warning = rex_i18n::msg('slice_deleted_error');
            else
              $warning = rex_i18n::msg('slice_saved_error');

          } else {
            if ($action_message)
              $action_message .= '<br />';

            // ----- SAVE/UPDATE SLICE
            if ($function == 'add' || $function == 'edit') {

              $sliceTable = rex::getTablePrefix() . 'article_slice';
              $newsql->setTable($sliceTable);

              if ($function == 'edit') {
                $newsql->setWhere(array('id' => $slice_id));
              } elseif ($function == 'add') {
                // determine prior value to get the new slice into the right order
                $prevSlice = rex_sql::factory();
                // $prevSlice->setDebug();
                if ($slice_id == -1) // -1 is used when adding after the last article-slice
                  $prevSlice->setQuery('SELECT IFNULL(MAX(prior),0)+1 as prior FROM ' . $sliceTable . ' WHERE article_id=' . $article_id . ' AND clang=' . $clang . ' AND ctype=' . $ctype . ' AND revision=' . $slice_revision);
                else
                  $prevSlice->setQuery('SELECT * FROM ' . $sliceTable . ' WHERE id=' . $slice_id);

                $prior = $prevSlice->getValue('prior');

                $newsql->setValue('article_id', $article_id);
                $newsql->setValue('module_id', $module_id);
                $newsql->setValue('clang', $clang);
                $newsql->setValue('ctype', $ctype);
                $newsql->setValue('revision', $slice_revision);
                $newsql->setValue('prior', $prior);
              }

              if ($function == 'edit') {
                $newsql->addGlobalUpdateFields();
                try {
                  $newsql->update();
                  $info = $action_message . rex_i18n::msg('block_updated');

                  // ----- EXTENSION POINT
                  $info = rex_extension::registerPoint('SLICE_UPDATED', $info,
                    array(
                      'article_id' => $article_id,
                      'clang' => $clang,
                      'function' => $function,
                      'slice_id' => $slice_id,
                      'page' => rex_be_controller::getCurrentPage(),
                      'ctype' => $ctype,
                      'category_id' => $category_id,
                      'module_id' => $module_id,
                      'article_revision' => &$article_revision,
                      'slice_revision' => &$slice_revision,
                    )
                  );
                } catch (rex_sql_exception $e) {
                  $warning = $action_message . $e->getMessage();
                }

              } elseif ($function == 'add') {
                $newsql->addGlobalUpdateFields();
                $newsql->addGlobalCreateFields();

                try {
                  $newsql->insert();

                  rex_sql_util::organizePriorities(
                    rex::getTable('article_slice'),
                    'prior',
                    'article_id=' . $article_id . ' AND clang=' . $clang . ' AND ctype=' . $ctype . ' AND revision=' . $slice_revision,
                    'prior, updatedate DESC'
                  );

                  $info = $action_message . rex_i18n::msg('block_added');
                  $slice_id = $newsql->getLastId();
                  $function = '';

                  // ----- EXTENSION POINT
                  $info = rex_extension::registerPoint('SLICE_ADDED', $info,
                    array(
                      'article_id' => $article_id,
                      'clang' => $clang,
                      'function' => $function,
                      'slice_id' => $slice_id,
                      'page' => rex_be_controller::getCurrentPage(),
                      'ctype' => $ctype,
                      'category_id' => $category_id,
                      'module_id' => $module_id,
                      'article_revision' => &$article_revision,
                      'slice_revision' => &$slice_revision,
                    )
                  );
                } catch (rex_sql_exception $e) {
                  $warning = $action_message . $e->getMessage();
                }
              }
            } else {
              // make delete
              if (rex_content_service::deleteSlice($slice_id)) {
                $global_info = rex_i18n::msg('block_deleted');

                // ----- EXTENSION POINT
                $global_info = rex_extension::registerPoint('SLICE_DELETED', $global_info,
                  array(
                    'article_id' => $article_id,
                    'clang' => $clang,
                    'function' => $function,
                    'slice_id' => $slice_id,
                    'page' => rex_be_controller::getCurrentPage(),
                    'ctype' => $ctype,
                    'category_id' => $category_id,
                    'module_id' => $module_id,
                    'article_revision' => &$article_revision,
                    'slice_revision' => &$slice_revision,
                  )
                );
              } else {
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
              array(
                'id' => $article_id,
                'clang' => $clang
              )
            );

            // ----- POST SAVE ACTION [ADD/EDIT/DELETE]
            $action->exec(rex_article_action::POSTSAVE);
            if ($messages = $action->getMessages()) {
              $info .= '<br />' . implode('<br />', $messages);
            }
            // ----- / POST SAVE ACTION

            // Update Button wurde gedrückt?
            // TODO: Workaround, da IE keine Button Namen beim
            // drücken der Entertaste übermittelt
            if (rex_post('btn_save', 'string')) {
              $function = '';
            }
          }
        }
      }
    }
    // ------------------------------------------ END: Slice add/edit/delete

    // ------------------------------------------ START: COPY LANG CONTENT
    if (rex_post('copycontent', 'boolean')) {
      $clang_a = rex_post('clang_a', 'int');
      $clang_b = rex_post('clang_b', 'int');
      $user = rex::getUser();
      if ($user->hasPerm('copyContent[]') && $user->getComplexPerm('clang')->hasPerm($clang_a) && $user->getComplexPerm('clang')->hasPerm($clang_b)) {
        if (rex_content_service::copyContent($article_id, $article_id, $clang_a, $clang_b, 0, $slice_revision))
          $info = rex_i18n::msg('content_contentcopy');
        else
          $warning = rex_i18n::msg('content_errorcopy');
      } else {
        $warning = rex_i18n::msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: COPY LANG CONTENT

    // ------------------------------------------ START: MOVE ARTICLE
    if (rex_post('movearticle', 'boolean') && $category_id != $article_id) {
      $category_id_new = rex_post('category_id_new', 'int');
      if (rex::getUser()->hasPerm('moveArticle[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id_new)) {
        if (rex_article_service::moveArticle($article_id, $category_id, $category_id_new)) {
          $info = rex_i18n::msg('content_articlemoved');
          ob_end_clean();
          rex_response::sendRedirect(htmlspecialchars_decode($context->getUrl(array('page' => 'content', 'info' => $info))));
        } else {
          $warning = rex_i18n::msg('content_errormovearticle');
        }
      } else {
        $warning = rex_i18n::msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: MOVE ARTICLE

    // ------------------------------------------ START: COPY ARTICLE
    if (rex_post('copyarticle', 'boolean')) {
      $category_copy_id_new = rex_post('category_copy_id_new', 'int');
      if (rex::getUser()->hasPerm('copyArticle[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_copy_id_new)) {
        if (($new_id = rex_article_service::copyArticle($article_id, $category_copy_id_new)) !== false) {
          $info = rex_i18n::msg('content_articlecopied');
          ob_end_clean();
          rex_response::sendRedirect(htmlspecialchars_decode($context->getUrl(array('page' => 'content', 'article_id' => $new_id, 'info' => $info))));
        } else {
          $warning = rex_i18n::msg('content_errorcopyarticle');
        }
      } else {
        $warning = rex_i18n::msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: COPY ARTICLE

    // ------------------------------------------ START: MOVE CATEGORY
    if (rex_post('movecategory', 'boolean')) {
      $category_id_new = rex_post('category_id_new', 'int');
      if (rex::getUser()->hasPerm('moveCategory[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article->getValue('re_id')) && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id_new)) {
        if ($category_id != $category_id_new && rex_category_service::moveCategory($category_id, $category_id_new)) {
          $info = rex_i18n::msg('category_moved');
          ob_end_clean();
          rex_response::sendRedirect(htmlspecialchars_decode($context->getUrl(array('page' => 'content', 'info' => $info))));
        } else {
          $warning = rex_i18n::msg('content_error_movecategory');
        }
      } else {
        $warning = rex_i18n::msg('no_rights_to_this_function');
      }
    }
    // ------------------------------------------ END: MOVE CATEGORY

    // ------------------------------------------ START: SAVE METADATA
    if (rex_post('savemeta', 'boolean')) {
      $meta_article_name = rex_post('meta_article_name', 'string');

      $meta_sql = rex_sql::factory();
      $meta_sql->setTable(rex::getTablePrefix() . 'article');
      // $meta_sql->setDebug();
      $meta_sql->setWhere(array('id' => $article_id, 'clang' => $clang));
      $meta_sql->setValue('name', $meta_article_name);
      $meta_sql->addGlobalUpdateFields();

      try {
        $meta_sql->update();

        $article->setQuery('SELECT * FROM ' . rex::getTablePrefix() . "article WHERE id='$article_id' AND clang='$clang'");
        $info = rex_i18n::msg('metadata_updated');

        rex_article_cache::delete($article_id, $clang);

        // ----- EXTENSION POINT
        $info = rex_extension::registerPoint('ART_META_UPDATED', $info, array(
          'id' => $article_id,
          'clang' => $clang,
          'name' => $meta_article_name,
        ));
      } catch (rex_sql_exception $e) {
        $warning = $e->getMessage();
      }
    }
    // ------------------------------------------ END: SAVE METADATA

    // ------------------------------------------ START: CONTENT HEAD MENUE

    $editPage = rex_be_controller::getPageObject('content/edit');
    $editPage->setHref($context->getUrl(array('page' => 'content/edit')));

    foreach ($ctypes as $key => $val) {
      $subpage = new rex_be_page('ctype' . $key, rex_i18n::translate($val));
      $subpage->setHref(array('page' => 'content/edit', 'article_id' => $article_id, 'clang' => $clang, 'ctype' => $key));
      $subpage->setIsActive($ctype == $key);
     $editPage->addSubPage($subpage);
    }

    $nav = rex_be_navigation::factory();
    $nav->addPage($editPage);
    $nav->setActiveElements();
    $blocks = $nav->getNavigation();
    $navigation = current($blocks);
    $content_navi_left = $navigation['navigation'];

    $nav = rex_be_navigation::factory();
    foreach (rex_be_controller::getPageObject('content')->getSubPages() as $subpage) {
      if ($subpage->getKey() != 'edit') {
        $subpage->setHref($context->getUrl(array('page' => $subpage->getFullKey())));
        $nav->addPage($subpage);
      }
    }
    $nav->setActiveElements();
    $blocks = $nav->getNavigation();
    $navigation = current($blocks);
    $content_navi_right = $navigation['navigation'];

    $content_navi_text_right = rex_i18n::msg('article') . ' <a href="' . rex_getUrl($article_id, $clang) . '" onclick="window.open(this.href); return false;" data-pjax="false">' . rex_i18n::msg('show') . '</a>';

    $fragment = new rex_fragment();
    $fragment->setVar('navigation_left', $content_navi_left, false);
    $fragment->setVar('navigation_right', $content_navi_right, false);
    $fragment->setVar('text_right', $content_navi_text_right, false);
    echo $fragment->parse('core/navigations/content.tpl');


    // ------------------------------------------ END: CONTENT HEAD MENUE

    // ------------------------------------------ WARNING
    if ($global_warning != '') {
      echo rex_view::warning($global_warning);
    }
    if ($global_info != '') {
      echo rex_view::success($global_info);
    }

    // --------------------------------------------- API MESSAGES
    echo rex_api_function::getMessage();

    if ($warning != '') {
      echo rex_view::warning($warning);
    }
    if ($info != '') {
      echo rex_view::success($info);
    }


    // ------------------------------------------ START: MODULE EDITIEREN/ADDEN ETC.
    include rex_be_controller::getCurrentPageObject()->getSubPath();

    // ------------------------------------------ END: AUSGABE

  }
}
