<?php

/**
 * @package redaxo5
 */

$addon = rex_addon::get('structure');
$structureContext = new rex_structure_context([
    'category_id' => rex_request('category_id', 'int'),
    'article_id' => rex_request('article_id', 'int'),
    'clang_id' => rex_request('clang', 'int'),
    'ctype_id' => rex_request('ctype', 'int'),
    'artstart' => rex_request('artstart', 'int'),
    'catstart' => rex_request('catstart', 'int'),
    'edit_id' => rex_request('edit_id', 'int'),
    'function' => rex_request('function', 'string'),
    'rows_per_page' => 30,
]);

if (0 == $structureContext->getClangId()) {
    echo rex_view::error('You have no permission to access this area');
    exit;
}

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER_PRE', '', [
    'context' => $structureContext->getContext(),
]));

// --------------------------------------------- TITLE
echo rex_view::title(rex_i18n::msg('title_structure'));

// --------------------------------------------- Languages
echo rex_view::clangSwitchAsButtons($structureContext->getContext());

// --------------------------------------------- Path
$article_id = $structureContext->getArticleId();
$category_id = $structureContext->getCategoryId();
$clang = $structureContext->getClangId();
require __DIR__ . '/../functions/function_rex_category.php';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();
$artStatusTypes = rex_article_service::statusTypes();

// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE
$cat_name = rex_i18n::msg('root_level');
$category = rex_category::get($structureContext->getCategoryId(), $structureContext->getClangId());
if ($category) {
    $cat_name = $category->getName();
}

$add_category = '';
if ($structureContext->hasCategoryPermission()) {
    $add_category = '<a href="' . $structureContext->getContext()->getUrl(['function' => 'add_cat', 'catstart' => $structureContext->getCatStart()]) . '"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'add') . '><i class="rex-icon rex-icon-add-category"></i></a>';
}

$data_colspan = 5;

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER', '', [
    'category_id' => $structureContext->getCategoryId(),
    'clang' => $structureContext->getClangId(),
]));

// --------------------- COUNT CATEGORY ROWS

$KAT = rex_sql::factory();
// $KAT->setDebug();
if (count($structureContext->getMountpoints()) > 0 && 0 == $structureContext->getMountpoints()) {
    $parent_id = implode(',', $structureContext->getMountpoints());
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang_id=' . $structureContext->getClangId());
} else {
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $structureContext->getCategoryId() . ' AND startarticle=1 AND clang_id=' . $structureContext->getClangId());
}

// --------------------- ADD PAGINATION

$catPager = new rex_pager($structureContext->getRowsPerPage(), 'catstart');
$catPager->setRowCount($KAT->getValue('rowCount'));
$catFragment = new rex_fragment();
$catFragment->setVar('urlprovider', $structureContext->getContext());
$catFragment->setVar('pager', $catPager);
echo $catFragment->parse('core/navigations/pagination.php');

// --------------------- GET THE DATA

if (count($structureContext->getMountpoints()) > 0 && 0 == $structureContext->getCategoryId()) {
    $parent_id = implode(',', $structureContext->getMountpoints());

    $KAT->setQuery('SELECT parent_id FROM ' . rex::getTable('article') . ' WHERE id IN (' . $parent_id . ') GROUP BY parent_id');
    $orderBy = $KAT->getRows() > 1 ? 'catname' : 'catpriority';

    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang_id=' . $structureContext->getClangId() . ' ORDER BY ' . $orderBy . ' LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
} else {
    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $structureContext->getCategoryId() . ' AND startarticle=1 AND clang_id=' . $structureContext->getClangId() . ' ORDER BY catpriority LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
}

$echo = '';
// ---------- INLINE THE EDIT/ADD FORM
if ('add_cat' == $function || 'edit_cat' == $function) {
    $echo .= '
    <form action="' . $structureContext->getContext()->getUrl(['catstart' => $structureContext->getCatStart()]) . '" method="post">
        <fieldset>

            <input type="hidden" name="edit_id" value="' . $structureContext->getEditId() . '" />';
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

// --------------------- KATEGORIE ADD FORM

if ('add_cat' == $structureContext->getFunction() && $structureContext->hasCategoryPermission()) {
    $meta_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
        'id' => $structureContext->getCategoryId(),
        'clang' => $structureContext->getClangId(),
    ]));
    $add_buttons = rex_api_category_add::getHiddenFields().'
        <input type="hidden" name="parent-category-id" value="' . $structureContext->getCategoryId() . '" />
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
        'id' => $structureContext->getCategoryId(),
        'clang' => $structureContext->getClangId(),
        'data_colspan' => ($data_colspan + 1),
    ]));
}

// --------------------- KATEGORIE LIST
if ($KAT->getRows() > 0) {
    for ($i = 0; $i < $KAT->getRows(); ++$i) {
        $i_category_id = $KAT->getValue('id');

        $kat_link = $structureContext->getContext()->getUrl(['category_id' => $i_category_id]);
        $kat_icon_td = '<td class="rex-table-icon"><a href="' . $kat_link . '" title="' . rex_escape($KAT->getValue('catname')) . '"><i class="rex-icon rex-icon-category"></i></a></td>';

        $kat_status = $catStatusTypes[$KAT->getValue('status')][0];
        $status_class = $catStatusTypes[$KAT->getValue('status')][1];
        $status_icon = $catStatusTypes[$KAT->getValue('status')][2];

        if ($structureContext->hasCategoryPermission()) {
            if ($structureContext->hasCategoryPermission() && rex::getUser()->hasPerm('publishCategory[]')) {
                $kat_status = '<a class="' . $status_class . '" href="' . $structureContext->getContext()->getUrl(['category-id' => $i_category_id, 'catstart' => $structureContext->getCatStart()] + rex_api_category_status::getUrlParams()) . '"><i class="rex-icon ' . $status_icon . '"></i> ' . $kat_status . '</a>';
            } else {
                $kat_status = '<span class="' . $status_class . ' text-muted"><i class="rex-icon ' . $status_icon . '"></i> ' . $kat_status . '</span>';
            }

            if ($structureContext->getEditId() == $i_category_id && 'edit_cat' == $structureContext->getFunction()) {
                // --------------------- KATEGORIE EDIT FORM

                // ----- EXTENSION POINT
                $meta_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
                    'id' => $structureContext->getEditId(),
                    'clang' => $structureContext->getClangId(),
                ]));

                $add_buttons = rex_api_category_edit::getHiddenFields().'
                <input type="hidden" name="category-id" value="' . $structureContext->getEditId() . '" />
                <button class="btn btn-save" type="submit" name="category-edit-button"' . rex::getAccesskey(rex_i18n::msg('save_category'), 'save') . '>' . rex_i18n::msg('save_category') . '</button>';

                $class = 'mark';
                if ('' != $meta_buttons) {
                    $class .= ' rex-has-metainfo';
                }

                $echo .= '
                    <tr class="' . $class . '">
                        ' . $kat_icon_td . '
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $i_category_id . '</td>
                        <td data-title="' . rex_i18n::msg('header_category') . '"><input class="form-control" type="text" name="category-name" value="' . rex_escape($KAT->getValue('catname')) . '" class="rex-js-autofocus" autofocus /></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="category-position" value="' . rex_escape($KAT->getValue('catpriority')) . '" /></td>
                        <td class="rex-table-action">' . $meta_buttons . '</td>
                        <td class="rex-table-action" colspan="2">' . $add_buttons . '</td>
                    </tr>';

                // ----- EXTENSION POINT
                $echo .= rex_extension::registerPoint(new rex_extension_point('CAT_FORM_EDIT', '', [
                    'id' => $structureContext->getEditId(),
                    'clang' => $structureContext->getClangId(),
                    'category' => $KAT,
                    'catname' => $KAT->getValue('catname'),
                    'catpriority' => $KAT->getValue('catpriority'),
                    'data_colspan' => ($data_colspan + 1),
                ]));
            } else {
                // --------------------- KATEGORIE WITH WRITE

                $category_delete = '<a href="' . $structureContext->getContext()->getUrl(['category-id' => $i_category_id, 'catstart' => $structureContext->getCatStart()] + rex_api_category_delete::getUrlParams()) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</a>';

                $echo .= '
                    <tr>
                        ' . $kat_icon_td . '
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $i_category_id . '</td>
                        <td data-title="' . rex_i18n::msg('header_category') . '"><a href="' . $kat_link . '">' . rex_escape($KAT->getValue('catname')) . '</a></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . rex_escape($KAT->getValue('catpriority')) . '</td>
                        <td class="rex-table-action"><a href="' . $structureContext->getContext()->getUrl(['edit_id' => $i_category_id, 'function' => 'edit_cat', 'catstart' => $structureContext->getCatStart()]) . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</a></td>
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
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . rex_escape($KAT->getValue('catpriority')) . '</td>
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

if ('add_cat' == $function || 'edit_cat' == $function) {
    $echo .= '
    </fieldset>
</form>';
}

$heading = rex_i18n::msg('structure_categories_caption', $cat_name);
if (0 == $structureContext->getCategoryId()) {
    $heading = rex_i18n::msg('structure_root_level_categories_caption');
}
$fragment = new rex_fragment();
$fragment->setVar('heading', $heading, false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');

// --------------------------------------------- ARTIKEL LISTE

$echo = '';

// --------------------- READ TEMPLATES

$template_select = new rex_template_select($category_id, $clang);

if ($structureContext->getCategoryId() > 0 || (0 == $structureContext->getCategoryId() && !rex::getUser()->getComplexPerm('structure')->hasMountpoints())) {
    $withTemplates = $addon->getPlugin('content')->isAvailable();
    $tmpl_head = '';
    if ($withTemplates) {
        $template_select->setName('template_id');
        $template_select->setSize(1);
        $template_select->setStyle('class="form-control selectpicker"');

        $TEMPLATE_NAME = $template_select->getTemplates();
        $TEMPLATE_NAME[0] = rex_i18n::msg('template_default_name');

        $tmpl_head = '<th>' . rex_i18n::msg('header_template') . '</th>';
    }

    // --------------------- ARTIKEL LIST
    $art_add_link = '';
    if ($structureContext->hasCategoryPermission()) {
        $art_add_link = '<a href="' . $structureContext->getContext()->getUrl(['function' => 'add_art', 'artstart' => $structureContext->getArtStart()]) . '"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2') . '><i class="rex-icon rex-icon-add-article"></i></a>';
    }

    // ---------- COUNT DATA
    $sql = rex_sql::factory();
    // $sql->setDebug();
    $sql->setQuery('SELECT COUNT(*) as artCount
                FROM
                    ' . rex::getTablePrefix() . 'article
                WHERE
                    ((parent_id=' . $structureContext->getCategoryId() . ' AND startarticle=0) OR (id=' . $structureContext->getCategoryId() . ' AND startarticle=1))
                    AND clang_id=' . $structureContext->getClangId() . '
                ORDER BY
                    priority, name');

    // --------------------- ADD PAGINATION

    $artPager = new rex_pager($structureContext->getRowsPerPage(), 'artstart');
    $artPager->setRowCount($sql->getValue('artCount'));
    $artFragment = new rex_fragment();
    $artFragment->setVar('urlprovider', $structureContext->getContext());
    $artFragment->setVar('pager', $artPager);
    echo $artFragment->parse('core/navigations/pagination.php');

    // ---------- READ DATA
    $sql->setQuery('SELECT *
                FROM
                    ' . rex::getTablePrefix() . 'article
                WHERE
                    ((parent_id=' . $structureContext->getCategoryId() . ' AND startarticle=0) OR (id=' . $structureContext->getCategoryId() . ' AND startarticle=1))
                    AND clang_id=' . $structureContext->getClangId() . '
                ORDER BY
                    priority, name
                LIMIT ' . $artPager->getCursor() . ',' . $artPager->getRowsPerPage());

    // ---------- INLINE THE EDIT/ADD FORM
    if ('add_art' == $function || 'edit_art' == $function) {
        $echo .= '
        <form action="' . $structureContext->getContext()->getUrl(['artstart' => $structureContext->getArtStart()]) . '" method="post">
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
    if ($sql->getRows() > 0 || 'add_art' == $function) {
        $echo .= '<tbody>
                    ';
    }

    // --------------------- ARTIKEL ADD FORM
    if ('add_art' == $structureContext->getFunction() && $structureContext->hasCategoryPermission()) {
        $tmpl_td = '';
        if ($withTemplates) {
            $template_select->setSelectedFromStartArticle();

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
        } elseif (1 == $sql->getValue('startarticle')) {
            $class = ' rex-icon-startarticle';
        } else {
            $class = ' rex-icon-article';
        }

        $class_startarticle = '';
        if (1 == $sql->getValue('startarticle')) {
            $class_startarticle = ' rex-startarticle';
        }

        // --------------------- ARTIKEL EDIT FORM

        if ('edit_art' == $structureContext->getFunction() && $sql->getValue('id') == $structureContext->getArticleId() && $structureContext->hasCategoryPermission()) {
            $tmpl_td = '';
            if ($withTemplates) {
                $template_select->setSelected($sql->getValue('template_id'));
                $tmpl_td = '<td data-title="' . rex_i18n::msg('header_template') . '">' . $template_select->get() . '</td>';
            }
            $echo .= '<tr class="mark' . $class_startarticle . '">
                            <td class="rex-table-icon"><a href="' . $structureContext->getContext()->getUrl(['page' => 'content/edit', 'article_id' => $sql->getValue('id')]) . '" title="' . rex_escape($sql->getValue('name')) . '"><i class="rex-icon' . $class . '"></i></a></td>
                            <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $sql->getValue('id') . '</td>
                            <td data-title="' . rex_i18n::msg('header_article_name') . '"><input class="form-control" type="text" name="article-name" value="' . rex_escape($sql->getValue('name')) . '" autofocus /></td>
                            ' . $tmpl_td . '
                            <td data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="article-position" value="' . rex_escape($sql->getValue('priority')) . '" /></td>
                            <td class="rex-table-action" colspan="3">'.rex_api_article_edit::getHiddenFields().'<button class="btn btn-save" type="submit" name="artedit_function"' . rex::getAccesskey(rex_i18n::msg('article_save'), 'save') . '>' . rex_i18n::msg('article_save') . '</button></td>
                        </tr>';
        } elseif ($structureContext->hasCategoryPermission()) {
            // --------------------- ARTIKEL NORMAL VIEW | EDIT AND ENTER

            $article_status = $artStatusTypes[$sql->getValue('status')][0];
            $article_class = $artStatusTypes[$sql->getValue('status')][1];
            $article_icon = $artStatusTypes[$sql->getValue('status')][2];

            $add_extra = '';
            if (1 == $sql->getValue('startarticle')) {
                $add_extra = '<td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</span></td>
                              <td class="rex-table-action"><span class="' . $article_class . ' text-muted"><i class="rex-icon ' . $article_icon . '"></i> ' . $article_status . '</span></td>';
            } else {
                if ($structureContext->hasCategoryPermission() && rex::getUser()->hasPerm('publishArticle[]')) {
                    $article_status = '<a class="' . $article_class . '" href="' . $structureContext->getContext()->getUrl(['article_id' => $sql->getValue('id'), 'artstart' => $structureContext->getArtStart()] + rex_api_article_status::getUrlParams()) . '"><i class="rex-icon ' . $article_icon . '"></i> ' . $article_status . '</a>';
                } else {
                    $article_status = '<span class="' . $article_class . ' text-muted"><i class="rex-icon ' . $article_icon . '"></i> ' . $article_status . '</span>';
                }

                $article_delete = '<a href="' . $structureContext->getContext()->getUrl(['article_id' => $sql->getValue('id'), 'artstart' => $structureContext->getArtStart()] + rex_api_article_delete::getUrlParams()) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</a>';

                $add_extra = '<td class="rex-table-action">' . $article_delete . '</td>
                              <td class="rex-table-action">' . $article_status . '</td>';
            }

            $editModeUrl = $structureContext->getContext()->getUrl(['page' => 'content/edit', 'article_id' => $sql->getValue('id'), 'mode' => 'edit']);

            $tmpl_td = '';
            if ($withTemplates) {
                $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';
                $tmpl_td = '<td data-title="' . rex_i18n::msg('header_template') . '">' . $tmpl . '</td>';
            }

            $echo .= '<tr' . (('' != $class_startarticle) ? ' class="' . trim($class_startarticle) . '"' : '') . '>
                            <td class="rex-table-icon"><a href="' . $editModeUrl . '" title="' . rex_escape($sql->getValue('name')) . '"><i class="rex-icon' . $class . '"></i></a></td>
                            <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $sql->getValue('id') . '</td>
                            <td data-title="' . rex_i18n::msg('header_article_name') . '"><a href="' . $editModeUrl . '">' . rex_escape($sql->getValue('name')) . '</a></td>
                            ' . $tmpl_td . '
                            <td data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . rex_escape($sql->getValue('priority')) . '</td>
                            <td class="rex-table-action"><a href="' . $structureContext->getContext()->getUrl(['article_id' => $sql->getValue('id'), 'function' => 'edit_art', 'artstart' => $structureContext->getArtStart()]) . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</a></td>
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
                            <td data-title="' . rex_i18n::msg('header_article_name') . '">' . rex_escape($sql->getValue('name')) . '</td>
                            ' . $tmpl_td . '
                            <td data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . rex_escape($sql->getValue('priority')) . '</td>
                            <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</span></td>
                            <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</span></td>
                            <td class="rex-table-action"><span class="' . $art_status_class . ' text-muted"><i class="rex-icon ' . $art_status_icon . '"></i> ' . $art_status . '</span></td>
                        </tr>';
        }

        $sql->next();
    }

    // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
    if ($sql->getRows() > 0 || 'add_art' == $function) {
        $echo .= '
                </tbody>';
    }

    $echo .= '
            </table>';

    if ('add_art' == $function || 'edit_art' == $function) {
        $echo .= '
        </fieldset>
    </form>';
    }
}

$heading = rex_i18n::msg('structure_articles_caption', $cat_name);
if (0 == $structureContext->getCategoryId()) {
    $heading = rex_i18n::msg('structure_root_level_articles_caption');
}
$fragment = new rex_fragment();
$fragment->setVar('heading', $heading, false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');
