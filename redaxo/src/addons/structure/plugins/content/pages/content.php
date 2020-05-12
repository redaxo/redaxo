<?php

/**
 * Verwaltung der Inhalte. EditierModul / Metadaten ...
 *
 * @package redaxo5
 */

$article_id = rex_request('article_id', 'int');
$clang = rex_request('clang', 'int');
$slice_id = rex_request('slice_id', 'int', '');
$function = rex_request('function', 'string');

$article_id = rex_article::get($article_id) ? $article_id : 0;
$clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();

$article_revision = 0;
$slice_revision = 0;
$template_attributes = [];

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
        LEFT JOIN ' . rex::getTablePrefix() . 'template as template
            ON template.id=article.template_id
        WHERE
            article.id=?
            AND clang_id=?', [$article_id, $clang]);

if (1 !== $article->getRows()) {
    echo rex_view::title(rex_i18n::msg('content'), '');
    echo rex_view::error(rex_i18n::msg('article_doesnt_exist'));
    return;
}

// ----- ctype holen
$template_attributes = $article->getArrayValue('template_attributes');

// Für Artikel ohne Template
if (!is_array($template_attributes)) {
    $template_attributes = [];
}

$ctypes = $template_attributes['ctype'] ?? []; // ctypes - aus dem template

$ctype = rex_request('ctype', 'int', 1);
if (!array_key_exists($ctype, $ctypes)) {
    $ctype = 1;
}

// ----- Artikel wurde gefunden - Kategorie holen
$OOArt = rex_article::get($article_id, $clang);
$category_id = $OOArt->getCategoryId();

// ----- Request Parameter
$subpage = rex_be_controller::getCurrentPagePart(2);
$function = rex_request('function', 'string');
$warning = rex_escape(rex_request('warning', 'string'));
$info = rex_escape(rex_request('info', 'string'));

$context = new rex_context([
    'page' => rex_be_controller::getCurrentPage(),
    'article_id' => $article_id,
    'category_id' => $category_id,
    'clang' => $clang,
    'ctype' => $ctype,
]);

// ----- Titel anzeigen
echo rex_view::title(rex_i18n::msg('content'), '');

// ----- Languages
echo rex_view::clangSwitchAsButtons($context);

// ----- category pfad und rechte
require rex_path::addon('structure', 'functions/function_rex_category.php');

// ----- EXTENSION POINT
echo rex_extension::registerPoint(new rex_extension_point('STRUCTURE_CONTENT_HEADER', '', [
    'article_id' => $article_id,
    'clang' => $clang,
    'function' => $function,
    'slice_id' => $slice_id,
    'page' => rex_be_controller::getCurrentPage(),
    'ctype' => $ctype,
    'category_id' => $category_id,
    'article_revision' => &$article_revision,
    'slice_revision' => &$slice_revision,
]));

// ----------------- HAT USER DIE RECHTE AN DIESEM ARTICLE ODER NICHT
if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
    // ----- hat keine rechte an diesem artikel
    echo rex_view::warning(rex_i18n::msg('no_rights_to_edit'));
} else {
    // ----- hat rechte an diesem artikel

    // ------------------------------------------ Slice add/edit/delete
    if (rex_request('save', 'boolean') && in_array($function, ['add', 'edit', 'delete'])) {
        // ----- check module

        $CM = rex_sql::factory();
        $module_id = null;
        if ('edit' == $function || 'delete' == $function) {
            // edit/ delete
            $CM->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article_slice LEFT JOIN ' . rex::getTablePrefix() . 'module ON ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id WHERE ' . rex::getTablePrefix() . 'article_slice.id=? AND clang_id=?', [$slice_id, $clang]);
            if (1 == $CM->getRows()) {
                $module_id = $CM->getValue('' . rex::getTablePrefix() . 'article_slice.module_id');
            }
        } else {
            // add
            $module_id = rex_post('module_id', 'int');
            $CM->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'module WHERE id=?', [$module_id]);
        }

        if (1 != $CM->getRows()) {
            // ------------- MODUL IST NICHT VORHANDEN
            $global_warning = rex_i18n::msg('module_not_found');
            $slice_id = '';
            $function = '';
        } else {
            // ------------- MODUL IST VORHANDEN

            // ----- RECHTE AM MODUL ?
            if ('delete' != $function && !rex_template::hasModule($template_attributes, $ctype, $module_id)) {
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
                    if ('' != $action_message) {
                        $warning = $action_message;
                    } elseif ('delete' == $function) {
                        $warning = rex_i18n::msg('slice_deleted_error');
                    } else {
                        $warning = rex_i18n::msg('slice_saved_error');
                    }
                } else {
                    if ($action_message) {
                        $action_message .= '<br />';
                    }

                    // clone sql object to preserve values in sql object given to rex_article_action
                    // otherwise the POSTSAVE action did not have access to values
                    $newsql = clone $newsql;

                    // ----- SAVE/UPDATE SLICE
                    if ('add' == $function || 'edit' == $function) {
                        $sliceTable = rex::getTablePrefix() . 'article_slice';
                        $newsql->setTable($sliceTable);

                        if ('edit' == $function) {
                            $newsql->setWhere(['id' => $slice_id]);
                        } elseif ('add' == $function) {
                            // determine priority value to get the new slice into the right order
                            $prevSlice = rex_sql::factory();
                            // $prevSlice->setDebug();
                            if (-1 == $slice_id) {
                                $prevSlice->setQuery('SELECT IFNULL(MAX(priority),0)+1 as priority FROM ' . $sliceTable . ' WHERE article_id=? AND clang_id=? AND ctype_id=? AND revision=?', [$article_id, $clang, $ctype, $slice_revision]);
                            } else {
                                $prevSlice->setQuery('SELECT * FROM ' . $sliceTable . ' WHERE id=?', [$slice_id]);
                            }

                            $priority = $prevSlice->getValue('priority');

                            $newsql->setValue('article_id', $article_id);
                            $newsql->setValue('module_id', $module_id);
                            $newsql->setValue('clang_id', $clang);
                            $newsql->setValue('ctype_id', $ctype);
                            $newsql->setValue('revision', $slice_revision);
                            $newsql->setValue('priority', $priority);
                        }

                        if ('edit' == $function) {
                            $newsql->addGlobalUpdateFields();
                            try {
                                rex_extension::registerPoint(new rex_extension_point('SLICE_UPDATE', '', [
                                    'slice_id' => $slice_id,
                                    'article_id' => $article_id,
                                    'clang_id' => $clang,
                                    'slice_revision' => $slice_revision,
                                ]));

                                $newsql->update();
                                $info = $action_message . rex_i18n::msg('block_updated');
                                $epParams = [
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
                                ];

                                // ----- EXTENSION POINT
                                $info = rex_extension::registerPoint(new rex_extension_point('SLICE_UPDATED', $info, $epParams));
                                /* deprecated */ $info = rex_extension::registerPoint(new rex_extension_point('STRUCTURE_CONTENT_SLICE_UPDATED', $info, $epParams));
                                $info = rex_extension::registerPoint(new rex_extension_point_art_content_updated($OOArt, 'slice_updated', $info));
                            } catch (rex_sql_exception $e) {
                                $warning = $action_message . $e->getMessage();
                            }
                        } elseif ('add' == $function) {
                            $newsql->addGlobalUpdateFields();
                            $newsql->addGlobalCreateFields();

                            try {
                                rex_extension::registerPoint(new rex_extension_point('SLICE_ADD', '', [
                                    'article_id' => $article_id,
                                    'clang_id' => $clang,
                                    'slice_revision' => $slice_revision,
                                ]));

                                $newsql->insert();
                                $slice_id = (int) $newsql->getLastId();

                                rex_sql_util::organizePriorities(
                                    rex::getTable('article_slice'),
                                    'priority',
                                    'article_id=' . (int) $article_id . ' AND clang_id=' . (int) $clang . ' AND ctype_id=' . (int) $ctype . ' AND revision=' . (int) $slice_revision,
                                    'priority, updatedate DESC'
                                );

                                $info = $action_message . rex_i18n::msg('block_added');
                                $function = '';
                                $epParams = [
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
                                ];

                                // ----- EXTENSION POINT
                                $info = rex_extension::registerPoint(new rex_extension_point('SLICE_ADDED', $info, $epParams));
                                /* deprecated */ $info = rex_extension::registerPoint(new rex_extension_point('STRUCTURE_CONTENT_SLICE_ADDED', $info, $epParams));
                                $info = rex_extension::registerPoint(new rex_extension_point_art_content_updated($OOArt, 'slice_added', $info));
                            } catch (rex_sql_exception $e) {
                                $warning = $action_message . $e->getMessage();
                            }
                        }
                    } else {
                        // make delete

                        if (rex_content_service::deleteSlice($slice_id)) {
                            $global_info = rex_i18n::msg('block_deleted');
                            $epParams = [
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
                            ];

                            // ----- EXTENSION POINT
                            $global_info = rex_extension::registerPoint(new rex_extension_point('SLICE_DELETED', $global_info, $epParams));
                            /* deprecated */ $global_info = rex_extension::registerPoint(new rex_extension_point('STRUCTURE_CONTENT_SLICE_DELETED', $global_info, $epParams));
                            $global_info = rex_extension::registerPoint(new rex_extension_point_art_content_updated($OOArt, 'slice_deleted', $global_info));
                        } else {
                            $global_warning = rex_i18n::msg('block_not_deleted');
                        }
                    }
                    // ----- / SAVE SLICE

                    // ----- artikel neu generieren
                    $EA = rex_sql::factory();
                    $EA->setTable(rex::getTablePrefix() . 'article');
                    $EA->setWhere(['id' => $article_id, 'clang_id' => $clang]);
                    $EA->addGlobalUpdateFields();
                    $EA->update();
                    rex_article_cache::delete($article_id, $clang);

                    rex_extension::registerPoint(new rex_extension_point('STRUCTURE_CONTENT_ARTICLE_UPDATED', '', [
                        'id' => $article_id,
                        'clang' => $clang,
                    ]));

                    // ----- POST SAVE ACTION [ADD/EDIT/DELETE]
                    $action->exec(rex_article_action::POSTSAVE);
                    if ($messages = $action->getMessages()) {
                        $info .= '<br />' . implode('<br />', $messages);
                    }
                    // ----- / POST SAVE ACTION

                    // Update Button wurde gedrückt?
                    if (rex_post('btn_save', 'string')) {
                        $function = '';
                    }
                }
            }
        }
    }
    // ------------------------------------------ END: Slice add/edit/delete

    // ------------------------------------------ START: CONTENT HEAD MENUE

    $editPage = rex_be_controller::getPageObject('content/edit');

    foreach ($ctypes as $key => $val) {
        $hasSlice = true;
        if ($ctype != $key) {
            $hasSlice = null !== rex_article_slice::getFirstSliceForCtype($key, $article_id, $clang);
        }
        $editPage->addSubpage((new rex_be_page('ctype' . $key, rex_i18n::translate($val)))
            ->setHref(['page' => 'content/edit', 'article_id' => $article_id, 'clang' => $clang, 'ctype' => $key])
            ->setIsActive($ctype == $key)
            ->setItemAttr('class', $hasSlice ? '' : 'rex-empty')
        );
    }

    $leftNav = rex_be_navigation::factory();
    $rightNav = rex_be_navigation::factory();

    $user = rex::getUser();

    foreach (rex_be_controller::getPageObject('content')->getSubpages() as $subpage) {
        if (!$subpage->hasHref()) {
            $subpage->setHref($context->getUrl(['page' => $subpage->getFullKey()], false));
        }
        // If the user has none of the content function permissions the page 'functions' will not be displayed
        if (
            'functions' != $subpage->getKey() ||
            $user->hasPerm('article2category[]') ||
            $user->hasPerm('article2startarticle[]') ||
            $user->hasPerm('copyArticle[]') ||
            $user->hasPerm('moveArticle[]') ||
            $user->hasPerm('moveCategory[]') ||
            ($user->hasPerm('copyContent[]') && $user->getComplexPerm('clang')->count() > 1)
        ) {
            if ($subpage->getItemAttr('left')) {
                $leftNav->addPage($subpage);
            } else {
                $rightNav->addPage($subpage);
            }
        }
        $subpage->removeItemAttr('left');
    }

    $blocks = $leftNav->getNavigation();
    $navigation = current($blocks);
    $content_navi_left = $navigation['navigation'];

    $blocks = $rightNav->getNavigation();
    $navigation = current($blocks);
    $content_navi_right = $navigation['navigation'];

    $content_navi_right[] = ['title' => '<a href="' . rex_getUrl($article_id, $clang) . '" onclick="window.open(this.href); return false;"><i class="rex-icon rex-icon-view"></i> ' . rex_i18n::msg('article') . ' ' . rex_i18n::msg('show') . '</a>'];

    $fragment = new rex_fragment();
    $fragment->setVar('id', 'rex-js-structure-content-nav', false);
    $fragment->setVar('left', $content_navi_left, false);
    $fragment->setVar('right', $content_navi_right, false);

    $contentMain = $fragment->parse('core/navigations/content.php');

    // ------------------------------------------ END: CONTENT HEAD MENUE

    // ------------------------------------------ WARNING
    if ('' != $global_warning) {
        $contentMain .= rex_view::warning($global_warning);
    }
    if ('' != $global_info) {
        $contentMain .= rex_view::success($global_info);
    }

    // --------------------------------------------- API MESSAGES
    $contentMain .= rex_api_function::getMessage();

    if ('' != $warning) {
        $contentMain .= rex_view::warning($warning);
    }
    if ('' != $info) {
        $contentMain .= rex_view::success($info);
    }

    // ----- EXTENSION POINT
    $contentMain .= rex_extension::registerPoint(new rex_extension_point('STRUCTURE_CONTENT_BEFORE_SLICES', '', [
        'article_id' => $article_id,
        'clang' => $clang,
        'function' => $function,
        'slice_id' => $slice_id,
        'page' => rex_be_controller::getCurrentPage(),
        'ctype' => $ctype,
        'category_id' => $category_id,
        'article_revision' => &$article_revision,
        'slice_revision' => &$slice_revision,
    ]));

    // ------------------------------------------ START: MODULE EDITIEREN/ADDEN ETC.
    $contentMain .= rex_be_controller::includeCurrentPageSubPath(compact('info', 'warning', 'template_attributes', 'article', 'article_id', 'category_id', 'clang', 'slice_id', 'slice_revision', 'function', 'ctype', 'context'));
    // ------------------------------------------ END: AUSGABE

    // ----- EXTENSION POINT
    $contentMain .= rex_extension::registerPoint(new rex_extension_point('STRUCTURE_CONTENT_AFTER_SLICES', '', [
        'article_id' => $article_id,
        'clang' => $clang,
        'function' => $function,
        'slice_id' => $slice_id,
        'page' => rex_be_controller::getCurrentPage(),
        'ctype' => $ctype,
        'category_id' => $category_id,
        'article_revision' => &$article_revision,
        'slice_revision' => &$slice_revision,
    ]));

    $contentMain = '<section id="rex-js-page-main-content" data-pjax-container="#rex-js-page-main-content">'.$contentMain.'</section>';

    // ----- EXTENSION POINT
    $contentSidebar = rex_extension::registerPoint(new rex_extension_point('STRUCTURE_CONTENT_SIDEBAR', '', [
        'article_id' => $article_id,
        'clang' => $clang,
        'function' => $function,
        'slice_id' => $slice_id,
        'page' => rex_be_controller::getCurrentPage(),
        'ctype' => $ctype,
        'category_id' => $category_id,
        'article_revision' => &$article_revision,
        'slice_revision' => &$slice_revision,
    ]));

    $fragment = new rex_fragment();
    $fragment->setVar('content', $contentMain, false);
    $fragment->setVar('sidebar', $contentSidebar, false);

    echo $fragment->parse('core/page/main_content.php');
}
