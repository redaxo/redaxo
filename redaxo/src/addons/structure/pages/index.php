<?php

/**
 * @package redaxo5
 */

// basic request vars
$category_id = rex_request('category_id', 'int');
$article_id = rex_request('article_id', 'int');
$clang = rex_request('clang', 'int');
$ctype = rex_request('ctype', 'int');

// additional request vars
$artstart = rex_request('artstart', 'int');
$catstart = rex_request('catstart', 'int');
$edit_id = rex_request('edit_id', 'int');
$function = rex_request('function', 'string');

$info = '';
$warning = '';

$category_id = rex_category::get($category_id) ? $category_id : 0;
$article_id = rex_article::get($article_id) ? $article_id : 0;
$clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();

// --------------------------------------------- Mountpoints

$mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
if (count($mountpoints) == 1 && $category_id == 0) {
    // Nur ein Mointpoint -> Sprung in die Kategory
    $category_id = current($mountpoints);
}

// --------------------------------------------- Rechte prüfen
$KATPERM = rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id);

$stop = false;
if (rex_clang::count() > 1) {
    if (!rex::getUser()->getComplexPerm('clang')->hasPerm($clang)) {
        $stop = true;
        foreach (rex_clang::getAllIds() as $key) {
            if (rex::getUser()->getComplexPerm('clang')->hasPerm($key)) {
                $clang = $key;
                $stop = false;
                break;
            }
        }

        if ($stop) {
            echo rex_view::error('You have no permission to this area');
            exit;
        }
    }
} else {
    $clang = rex_clang::getStartId();
}

$context = new rex_context([
    'page' => 'structure',
    'category_id' => $category_id,
    'article_id' => $article_id,
    'clang' => $clang,
]);

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER_PRE', '', [
    'context' => $context,
]));

// --------------------------------------------- TITLE
echo rex_view::title(rex_i18n::msg('title_structure'));

// --------------------------------------------- Languages
echo rex_view::clangSwitchAsButtons($context);

// --------------------------------------------- Path
require __DIR__ . '/../functions/function_rex_category.php';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();
$artStatusTypes = rex_article_service::statusTypes();

// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE
$cat_name = rex_i18n::msg('root_level');
$category = rex_category::get($category_id, $clang);
if ($category) {
    $cat_name = $category->getName();
}

$add_category = '';
if ($KATPERM) {
    $add_category = '<a href="' . $context->getUrl(['function' => 'add_cat', 'catstart' => $catstart]) . '"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'add') . '><i class="rex-icon rex-icon-add-category"></i></a>';
}

$data_colspan = 5;

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER', '', [
    'category_id' => $category_id,
    'clang' => $clang,
]));

// --------------------- SEARCH BAR
//require_once $this->getPath('functions/function_rex_searchbar.php');
//echo rex_structure_searchbar($context);

// --------------------- COUNT CATEGORY ROWS

$KAT = rex_sql::factory();
// $KAT->setDebug();
if (count($mountpoints) > 0 && $category_id == 0) {
    $parent_id = implode(',', $mountpoints);
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang_id=' . $clang);
} else {
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $category_id . ' AND startarticle=1 AND clang_id=' . $clang);
}

// --------------------- ADD PAGINATION

$catPager = new rex_pager(30, 'catstart');
$catPager->setRowCount($KAT->getValue('rowCount'));
$catFragment = new rex_fragment();
$catFragment->setVar('urlprovider', $context);
$catFragment->setVar('pager', $catPager);
echo $catFragment->parse('core/navigations/pagination.php');

// --------------------- GET THE DATA

if (count($mountpoints) > 0 && $category_id == 0) {
    $parent_id = implode(',', $mountpoints);

    $KAT->setQuery('SELECT parent_id FROM ' . rex::getTable('article') . ' WHERE id IN (' . $parent_id . ') GROUP BY parent_id');
    $orderBy = $KAT->getRows() > 1 ? 'catname' : 'catpriority';

    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang_id=' . $clang . ' ORDER BY ' . $orderBy . ' LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
} else {
    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $category_id . ' AND startarticle=1 AND clang_id=' . $clang . ' ORDER BY catpriority LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
}

$echo = '';
// ---------- INLINE THE EDIT/ADD FORM
if ($function == 'add_cat' || $function == 'edit_cat') {
    $echo .= '
    <form action="' . $context->getUrl(['catstart' => $catstart]) . '" method="post">
        <fieldset>

            <input type="hidden" name="edit_id" value="' . $edit_id . '" />';
}

// --------------------- PRINT CATS/SUBCATS
$echo .= '
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon">' . $add_category . '</th>
                        <th class="rex-table-id">' . rex_i18n::msg('header_id') . '</th>
                        <th>' . rex_i18n::msg('header_category') . '</th>
                        <th class="rex-table-priority">' . rex_i18n::msg('header_priority') . '</th>
                        <th class="rex-table-action" colspan="3">' . rex_i18n::msg('header_status') . '</th>
                    </tr>
                </thead>
                <tbody>';
if ($category_id != 0 && ($category = rex_category::get($category_id))) {
    $echo .= '  <tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-open-category"></i></td>
                    <td class="rex-table-id">-</td>
                    <td data-title="' . rex_i18n::msg('header_category') . '"><a href="' . $context->getUrl(['category_id' => $category->getParentId()]) . '">..</a></td>
                    <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">&nbsp;</td>
                    <td class="rex-table-action" colspan="3">&nbsp;</td>
                </tr>';
}

// --------------------- KATEGORIE ADD FORM

if ($function == 'add_cat' && $KATPERM) {
    $meta_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
        'id' => $category_id,
        'clang' => $clang,
    ]));
    $add_buttons = rex_api_category_add::getHiddenFields().'
        <input type="hidden" name="parent-category-id" value="' . $category_id . '" />
        <button class="btn btn-save" type="submit" name="category-add-button"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'save') . '>' . rex_i18n::msg('add_category') . '</button>';

    $class = 'mark';

    $echo .= '
                <tr class="' . $class . '">
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-category"></i></td>
                    <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">-</td>
                    <td data-title="' . rex_i18n::msg('header_category') . '"><input class="form-control" type="text" name="category-name" class="rex-js-autofocus" autofocus /></td>
                    <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="category-position" value="' . ($catPager->getRowCount() + 1) . '" /></td>
                    <td class="rex-table-action">' . $meta_buttons . '</td>
                    <td class="rex-table-action" colspan="2">' . $add_buttons . '</td>
                </tr>';

    // ----- EXTENSION POINT
    $echo .= rex_extension::registerPoint(new rex_extension_point('CAT_FORM_ADD', '', [
        'id' => $category_id,
        'clang' => $clang,
        'data_colspan' => ($data_colspan + 1),
    ]));
}

// --------------------- KATEGORIE LIST
if ($KAT->getRows() > 0) {
    for ($i = 0; $i < $KAT->getRows(); ++$i) {
        $i_category_id = $KAT->getValue('id');

        $kat_link = $context->getUrl(['category_id' => $i_category_id]);
        $kat_icon_td = '<td class="rex-table-icon"><a href="' . $kat_link . '" title="' . htmlspecialchars($KAT->getValue('catname')) . '"><i class="rex-icon rex-icon-category"></i></a></td>';

        $kat_status = $catStatusTypes[$KAT->getValue('status')][0];
        $status_class = $catStatusTypes[$KAT->getValue('status')][1];
        $status_icon = $catStatusTypes[$KAT->getValue('status')][2];

        if ($KATPERM) {
            if ($KATPERM && rex::getUser()->hasPerm('publishCategory[]')) {
                $kat_status = '<a class="' . $status_class . '" href="' . $context->getUrl(['category-id' => $i_category_id, 'catstart' => $catstart] + rex_api_category_status::getUrlParams()) . '"><i class="rex-icon ' . $status_icon . '"></i> ' . $kat_status . '</a>';
            } else {
                $kat_status = '<span class="' . $status_class . ' text-muted"><i class="rex-icon ' . $status_icon . '"></i> ' . $kat_status . '</span>';
            }

            if (isset($edit_id) && $edit_id == $i_category_id && $function == 'edit_cat') {
                // --------------------- KATEGORIE EDIT FORM

                // ----- EXTENSION POINT
                $meta_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
                    'id' => $edit_id,
                    'clang' => $clang,
                ]));

                $add_buttons = rex_api_category_edit::getHiddenFields().'
                <input type="hidden" name="category-id" value="' . $edit_id . '" />
                <button class="btn btn-save" type="submit" name="category-edit-button"' . rex::getAccesskey(rex_i18n::msg('save_category'), 'save') . '>' . rex_i18n::msg('save_category') . '</button>';

                $class = 'mark';
                if ($meta_buttons != '') {
                    $class .= ' rex-has-metainfo';
                }

                $echo .= '
                    <tr class="' . $class . '">
                        ' . $kat_icon_td . '
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $i_category_id . '</td>
                        <td data-title="' . rex_i18n::msg('header_category') . '"><input class="form-control" type="text" name="category-name" value="' . htmlspecialchars($KAT->getValue('catname')) . '" class="rex-js-autofocus" autofocus /></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="category-position" value="' . htmlspecialchars($KAT->getValue('catpriority')) . '" /></td>
                        <td class="rex-table-action">' . $meta_buttons . '</td>
                        <td class="rex-table-action" colspan="2">' . $add_buttons . '</td>
                    </tr>';

                // ----- EXTENSION POINT
                $echo .= rex_extension::registerPoint(new rex_extension_point('CAT_FORM_EDIT', '', [
                    'id' => $edit_id,
                    'clang' => $clang,
                    'category' => $KAT,
                    'catname' => $KAT->getValue('catname'),
                    'catpriority' => $KAT->getValue('catpriority'),
                    'data_colspan' => ($data_colspan + 1),
                ]));
            } else {
                // --------------------- KATEGORIE WITH WRITE

                $category_delete = '<a href="' . $context->getUrl(['category-id' => $i_category_id, 'catstart' => $catstart] + rex_api_category_delete::getUrlParams()) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</a>';

                $echo .= '
                    <tr>
                        ' . $kat_icon_td . '
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $i_category_id . '</td>
                        <td data-title="' . rex_i18n::msg('header_category') . '"><a href="' . $kat_link . '">' . htmlspecialchars($KAT->getValue('catname')) . '</a></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . htmlspecialchars($KAT->getValue('catpriority')) . '</td>
                        <td class="rex-table-action"><a href="' . $context->getUrl(['edit_id' => $i_category_id, 'function' => 'edit_cat', 'catstart' => $catstart]) . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</a></td>
                        <td class="rex-table-action">' . $category_delete . '</td>
                        <td class="rex-table-action">' . $kat_status . '</td>
                    </tr>';
            }
        } elseif (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($i_category_id)) {
            // --------------------- KATEGORIE WITH READ

            $echo .= '
                    <tr>
                        ' . $kat_icon_td . '
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $i_category_id . '</td>
                        <td data-title="' . rex_i18n::msg('header_category') . '"><a href="' . $kat_link . '">' . $KAT->getValue('catname') . '</a></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . htmlspecialchars($KAT->getValue('catpriority')) . '</td>
                        <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</span></td>
                        <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</span></td>
                        <td class="rex-table-action"><span class="' . $status_class . ' text-muted"><i class="rex-icon ' . $status_icon . '"></i> ' . $kat_status . '</span></td>
                    </tr>';
        }

        $KAT->next();
    }
} else {
    $echo .= '
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';
}

$echo .= '
            </tbody>
        </table>';

if ($function == 'add_cat' || $function == 'edit_cat') {
    $echo .= '
    </fieldset>
</form>';
}

$heading = rex_i18n::msg('structure_categories_caption', $cat_name);
if ($category_id == 0) {
    $heading = rex_i18n::msg('structure_root_level_categories_caption');
}
$fragment = new rex_fragment();
$fragment->setVar('heading', $heading, false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');

// --------------------------------------------- ARTIKEL LISTE

$echo = '';

// --------------------- READ TEMPLATES

if ($category_id > 0 || ($category_id == 0 && !rex::getUser()->getComplexPerm('structure')->hasMountpoints())) {
    $withTemplates = $this->getPlugin('content')->isAvailable();
    $tmpl_head = '';
    if ($withTemplates) {
        $template_select = new rex_select();
        $template_select->setName('template_id');
        $template_select->setSize(1);
        $template_select->setStyle('class="form-control"');

        $templates = rex_template::getTemplatesForCategory($category_id);
        if (count($templates) > 0) {
            foreach ($templates as $t_id => $t_name) {
                $template_select->addOption(rex_i18n::translate($t_name, false), $t_id);
                $TEMPLATE_NAME[$t_id] = rex_i18n::translate($t_name);
            }
        } else {
            $template_select->addOption(rex_i18n::msg('option_no_template'), '0');
        }
        $TEMPLATE_NAME[0] = rex_i18n::msg('template_default_name');
        $tmpl_head = '<th>' . rex_i18n::msg('header_template') . '</th>';
    }

    // --------------------- ARTIKEL LIST
    $art_add_link = '';
    if ($KATPERM) {
        $art_add_link = '<a href="' . $context->getUrl(['function' => 'add_art', 'artstart' => $artstart]) . '"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2') . '><i class="rex-icon rex-icon-add-article"></i></a>';
    }

    // ---------- COUNT DATA
    $sql = rex_sql::factory();
    // $sql->setDebug();
    $sql->setQuery('SELECT COUNT(*) as artCount
                FROM
                    ' . rex::getTablePrefix() . 'article
                WHERE
                    ((parent_id=' . $category_id . ' AND startarticle=0) OR (id=' . $category_id . ' AND startarticle=1))
                    AND clang_id=' . $clang . '
                ORDER BY
                    priority, name');

    // --------------------- ADD PAGINATION

    $artPager = new rex_pager(30, 'artstart');
    $artPager->setRowCount($sql->getValue('artCount'));
    $artFragment = new rex_fragment();
    $artFragment->setVar('urlprovider', $context);
    $artFragment->setVar('pager', $artPager);
    echo $artFragment->parse('core/navigations/pagination.php');

    // ---------- READ DATA
    $sql->setQuery('SELECT *
                FROM
                    ' . rex::getTablePrefix() . 'article
                WHERE
                    ((parent_id=' . $category_id . ' AND startarticle=0) OR (id=' . $category_id . ' AND startarticle=1))
                    AND clang_id=' . $clang . '
                ORDER BY
                    priority, name
                LIMIT ' . $artPager->getCursor() . ',' . $artPager->getRowsPerPage());

    // ---------- INLINE THE EDIT/ADD FORM
    if ($function == 'add_art' || $function == 'edit_art') {
        $echo .= '
        <form action="' . $context->getUrl(['artstart' => $artstart]) . '" method="post">
            <fieldset>';
    }

    // ----------- PRINT OUT THE ARTICLES

    $echo .= '
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon">' . $art_add_link . '</th>
                        <th class="rex-table-id">' . rex_i18n::msg('header_id') . '</th>
                        <th>' . rex_i18n::msg('header_article_name') . '</th>
                        ' . $tmpl_head . '
                        <th>' . rex_i18n::msg('header_date') . '</th>
                        <th class="rex-table-priority">' . rex_i18n::msg('header_priority') . '</th>
                        <th class="rex-table-action" colspan="3">' . rex_i18n::msg('header_status') . '</th>
                    </tr>
                </thead>
                ';

    // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
    if ($sql->getRows() > 0 || $function == 'add_art') {
        $echo .= '<tbody>
                    ';
    }

    // --------------------- ARTIKEL ADD FORM
    if ($function == 'add_art' && $KATPERM) {
        $tmpl_td = '';
        if ($withTemplates) {
            $selectedTemplate = 0;
            if ($category_id) {
                // template_id vom Startartikel erben
                $sql2 = rex_sql::factory();
                $sql2->setQuery('SELECT template_id FROM ' . rex::getTablePrefix() . 'article WHERE id=' . $category_id . ' AND clang_id=' . $clang . ' AND startarticle=1');
                if ($sql2->getRows() == 1) {
                    $selectedTemplate = $sql2->getValue('template_id');
                }
            }
            if (!$selectedTemplate || !isset($TEMPLATE_NAME[$selectedTemplate])) {
                $selectedTemplate = rex_template::getDefaultId();
            }
            if ($selectedTemplate && isset($TEMPLATE_NAME[$selectedTemplate])) {
                $template_select->setSelected($selectedTemplate);
            }

            $tmpl_td = '<td data-title="' . rex_i18n::msg('header_template') . '">' . $template_select->get() . '</td>';
        }

        $echo .= '<tr class="mark">
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-article"></i></td>
                    <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">-</td>
                    <td data-title="' . rex_i18n::msg('header_article_name') . '"><input class="form-control" type="text" name="article-name" autofocus /></td>
                    ' . $tmpl_td . '
                    <td data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime(time(), 'date') . '</td>
                    <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="article-position" value="' . ($artPager->getRowCount() + 1) . '" /></td>
                    <td class="rex-table-action" colspan="3">'.rex_api_article_add::getHiddenFields().'<button class="btn btn-save" type="submit" name="artadd_function"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'save') . '>' . rex_i18n::msg('article_add') . '</button></td>
                </tr>
                            ';
    }

    // --------------------- ARTIKEL LIST

    for ($i = 0; $i < $sql->getRows(); ++$i) {
        if ($sql->getValue('id') == rex_article::getSiteStartArticleId()) {
            $class = ' rex-icon-sitestartarticle';
        } elseif ($sql->getValue('startarticle') == 1) {
            $class = ' rex-icon-startarticle';
        } else {
            $class = ' rex-icon-article';
        }

        $class_startarticle = '';
        if ($sql->getValue('startarticle') == 1) {
            $class_startarticle = ' rex-startarticle';
        }

        // --------------------- ARTIKEL EDIT FORM

        if ($function == 'edit_art' && $sql->getValue('id') == $article_id && $KATPERM) {
            $tmpl_td = '';
            if ($withTemplates) {
                $template_select->setSelected($sql->getValue('template_id'));
                $tmpl_td = '<td data-title="' . rex_i18n::msg('header_template') . '">' . $template_select->get() . '</td>';
            }
            $echo .= '<tr class="mark' . $class_startarticle . '">
                            <td class="rex-table-icon"><a href="' . $context->getUrl(['page' => 'content/edit', 'article_id' => $sql->getValue('id')]) . '" title="' . htmlspecialchars($sql->getValue('name')) . '"><i class="rex-icon' . $class . '"></i></a></td>
                            <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $sql->getValue('id') . '</td>
                            <td data-title="' . rex_i18n::msg('header_article_name') . '"><input class="form-control" type="text" name="article-name" value="' . htmlspecialchars($sql->getValue('name')) . '" autofocus /></td>
                            ' . $tmpl_td . '
                            <td data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="article-position" value="' . htmlspecialchars($sql->getValue('priority')) . '" /></td>
                            <td class="rex-table-action" colspan="3">'.rex_api_article_edit::getHiddenFields().'<button class="btn btn-save" type="submit" name="artedit_function"' . rex::getAccesskey(rex_i18n::msg('article_save'), 'save') . '>' . rex_i18n::msg('article_save') . '</button></td>
                        </tr>';
        } elseif ($KATPERM) {
            // --------------------- ARTIKEL NORMAL VIEW | EDIT AND ENTER

            $article_status = $artStatusTypes[$sql->getValue('status')][0];
            $article_class = $artStatusTypes[$sql->getValue('status')][1];
            $article_icon = $artStatusTypes[$sql->getValue('status')][2];

            $add_extra = '';
            if ($sql->getValue('startarticle') == 1) {
                $add_extra = '<td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</span></td>
                              <td class="rex-table-action"><span class="' . $article_class . ' text-muted"><i class="rex-icon ' . $article_icon . '"></i> ' . $article_status . '</span></td>';
            } else {
                if ($KATPERM && rex::getUser()->hasPerm('publishArticle[]')) {
                    $article_status = '<a class="' . $article_class . '" href="' . $context->getUrl(['article_id' => $sql->getValue('id'), 'artstart' => $artstart] + rex_api_article_status::getUrlParams()) . '"><i class="rex-icon ' . $article_icon . '"></i> ' . $article_status . '</a>';
                } else {
                    $article_status = '<span class="' . $article_class . ' text-muted"><i class="rex-icon ' . $article_icon . '"></i> ' . $article_status . '</span>';
                }

                $article_delete = '<a href="' . $context->getUrl(['article_id' => $sql->getValue('id'), 'artstart' => $artstart] + rex_api_article_delete::getUrlParams()) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</a>';

                $add_extra = '<td class="rex-table-action">' . $article_delete . '</td>
                              <td class="rex-table-action">' . $article_status . '</td>';
            }

            $editModeUrl = $context->getUrl(['page' => 'content/edit', 'article_id' => $sql->getValue('id'), 'mode' => 'edit']);

            $tmpl_td = '';
            if ($withTemplates) {
                $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';
                $tmpl_td = '<td data-title="' . rex_i18n::msg('header_template') . '">' . $tmpl . '</td>';
            }

            $echo .= '<tr' . (($class_startarticle != '') ? ' class="' . trim($class_startarticle) . '"' : '') . '>
                            <td class="rex-table-icon"><a href="' . $editModeUrl . '" title="' . htmlspecialchars($sql->getValue('name')) . '"><i class="rex-icon' . $class . '"></i></a></td>
                            <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $sql->getValue('id') . '</td>
                            <td data-title="' . rex_i18n::msg('header_article_name') . '"><a href="' . $editModeUrl . '">' . htmlspecialchars($sql->getValue('name')) . '</a></td>
                            ' . $tmpl_td . '
                            <td data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . htmlspecialchars($sql->getValue('priority')) . '</td>
                            <td class="rex-table-action"><a href="' . $context->getUrl(['article_id' => $sql->getValue('id'), 'function' => 'edit_art', 'artstart' => $artstart]) . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</a></td>
                            ' . $add_extra . '
                        </tr>
                        ';
        } else {
            // --------------------- ARTIKEL NORMAL VIEW | NO EDIT NO ENTER

            $art_status = $artStatusTypes[$sql->getValue('status')][0];
            $art_status_class = $artStatusTypes[$sql->getValue('status')][1];
            $art_status_icon = $artStatusTypes[$sql->getValue('status')][2];

            $tmpl_td = '';
            if ($withTemplates) {
                $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';
                $tmpl_td = '<td data-title="' . rex_i18n::msg('header_template') . '">' . $tmpl . '</td>';
            }

            $echo .= '<tr>
                            <td class="rex-table-icon"><i class="rex-icon' . $class . '"></i></td>
                            <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $sql->getValue('id') . '</td>
                            <td data-title="' . rex_i18n::msg('header_article_name') . '">' . htmlspecialchars($sql->getValue('name')) . '</td>
                            ' . $tmpl_td . '
                            <td data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . htmlspecialchars($sql->getValue('priority')) . '</td>
                            <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</span></td>
                            <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</span></td>
                            <td class="rex-table-action"><span class="' . $art_status_class . ' text-muted"><i class="rex-icon ' . $art_status_icon . '"></i> ' . $art_status . '</span></td>
                        </tr>';
        }

        $sql->next();
    }

    // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
    if ($sql->getRows() > 0 || $function == 'add_art') {
        $echo .= '
                </tbody>';
    }

    $echo .= '
            </table>';

    if ($function == 'add_art' || $function == 'edit_art') {
        $echo .= '
        </fieldset>
    </form>';
    }
}

$heading = rex_i18n::msg('structure_articles_caption', $cat_name);
if ($category_id == 0) {
    $heading = rex_i18n::msg('structure_root_level_articles_caption');
}
$fragment = new rex_fragment();
$fragment->setVar('heading', $heading, false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');
