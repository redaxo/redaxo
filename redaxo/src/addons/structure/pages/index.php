<?php
/**
 * @package redaxo5/structure
 */

$addon = rex_addon::get('structure');

$structure_context = new rex_structure_context([
    'category_id' => rex_request('category_id', 'int'),
    'article_id' => rex_request('article_id', 'int'),
    'clang_id' => rex_request('clang', 'int'),
    'ctype_id' => rex_request('ctype', 'int'),
    'artstart' => rex_request('artstart', 'int'),
    'catstart' => rex_request('catstart', 'int'),
    'edit_id' => rex_request('edit_id', 'int'),
    'function' => rex_request('function', 'string'),
]);

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER_PRE', '', [
    'context' => $structure_context->getContext(),
]));

// --------------------------------------------- TITLE
echo rex_view::title(rex_i18n::msg('title_structure'));

// --------------------------------------------- Languages
echo rex_view::clangSwitchAsButtons($structure_context->getContext());

// --------------------------------------------- Path
$article_id = $structure_context->getArticleId();
$category_id = $structure_context->getCategoryId();
$clang = $structure_context->getClangId();
require __DIR__ . '/../functions/function_rex_category.php';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();

// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE
$cat_name = rex_i18n::msg('root_level');
$category = rex_category::get($structure_context->getCategoryId(), $structure_context->getClangId());
if ($category) {
    $cat_name = $category->getName();
}

$data_colspan = 5;

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER', '', [
    'category_id' => $structure_context->getCategoryId(),
    'clang' => $structure_context->getClangId(),
]));

// --------------------- COUNT CATEGORY ROWS

$KAT = rex_sql::factory();
// $KAT->setDebug();
if (count($structure_context->getMountpoints()) > 0 && 0 == $structure_context->getCategoryId()) {
    $parent_id = implode(',', $structure_context->getMountpoints());
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang_id=' . $structure_context->getClangId());
} else {
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $structure_context->getCategoryId() . ' AND startarticle=1 AND clang_id=' . $structure_context->getClangId());
}

// --------------------- ADD PAGINATION

$catPager = new rex_pager(30, 'catstart');
$catPager->setRowCount($KAT->getValue('rowCount'));
$catFragment = new rex_fragment();
$catFragment->setVar('urlprovider', $structure_context->getContext());
$catFragment->setVar('pager', $catPager);
echo $catFragment->parse('core/navigations/pagination.php');

// --------------------- GET THE DATA

if (count($structure_context->getMountpoints()) > 0 && 0 == $structure_context->getCategoryId()) {
    $parent_id = implode(',', $structure_context->getMountpoints());

    $KAT->setQuery('SELECT parent_id FROM ' . rex::getTable('article') . ' WHERE id IN (' . $parent_id . ') GROUP BY parent_id');
    $orderBy = $KAT->getRows() > 1 ? 'catname' : 'catpriority';

    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang_id=' . $structure_context->getClangId() . ' ORDER BY ' . $orderBy . ' LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
} else {
    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $structure_context->getCategoryId() . ' AND startarticle=1 AND clang_id=' . $structure_context->getClangId() . ' ORDER BY catpriority LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
}

$echo = '';

// --------------------- KATEGORIE ADD FORM

if ('add_cat' == $structure_context->getFunction() && $structure_context->hasCategoryPermission()) {
    // ----- EXTENSION POINT
    $meta_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
        'id' => $structure_context->getCategoryId(),
        'clang' => $structure_context->getClangId(),
    ]));

    $fragment = new rex_fragment();
    $fragment->setVar('catPager', $catPager, false);
    $fragment->setVar('meta_buttons', $meta_buttons, false);
    $fragment->setVar('structure_context', $structure_context, false);
    $echo .= $fragment->parse('structure/table_row_categories_form.php');

    // ----- EXTENSION POINT
    $echo .= rex_extension::registerPoint(new rex_extension_point('CAT_FORM_ADD', '', [
        'id' => $structure_context->getCategoryId(),
        'clang' => $structure_context->getClangId(),
        'data_colspan' => ($data_colspan + 1),
    ]));
}

// --------------------- KATEGORIE LIST
if ($KAT->getRows() > 0) {
    for ($i = 0; $i < $KAT->getRows(); ++$i) {
        $i_category_id = $KAT->getValue('id');

        $kat_link = $structure_context->getContext()->getUrl(['category_id' => $i_category_id]);

        $kat_status = $catStatusTypes[$KAT->getValue('status')][0];
        $status_class = $catStatusTypes[$KAT->getValue('status')][1];
        $status_icon = $catStatusTypes[$KAT->getValue('status')][2];

        if ($structure_context->hasCategoryPermission() && $structure_context->getEditId() == $i_category_id && 'edit_cat' == $structure_context->getFunction()) {
            // --------------------- KATEGORIE EDIT FORM

            // ----- EXTENSION POINT
            $meta_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
                'id' => $structure_context->getEditId(),
                'clang' => $structure_context->getClangId(),
            ]));

            $fragment = new rex_fragment();
            $fragment->setVar('i_category_id', $i_category_id);
            $fragment->setVar('kat_link', $kat_link, false);
            $fragment->setVar('KAT', $KAT, false);
            $fragment->setVar('meta_buttons', $meta_buttons, false);
            $fragment->setVar('structure_context', $structure_context, false);
            $echo .= $fragment->parse('structure/table_row_categories_form.php');

            // ----- EXTENSION POINT
            $echo .= rex_extension::registerPoint(new rex_extension_point('CAT_FORM_EDIT', '', [
                'id' => $structure_context->getEditId(),
                'clang' => $structure_context->getClangId(),
                'category' => $KAT,
                'catname' => $KAT->getValue('catname'),
                'catpriority' => $KAT->getValue('catpriority'),
                'data_colspan' => ($data_colspan + 1),
            ]));
        } else {
            $fragment = new rex_fragment();
            $fragment->setVar('i_category_id', $i_category_id);
            $fragment->setVar('kat_link', $kat_link, false);
            $fragment->setVar('KAT', $KAT, false);
            $fragment->setVar('status_class', $status_class, false);
            $fragment->setVar('status_icon', $status_icon, false);
            $fragment->setVar('kat_status', $kat_status, false);
            $fragment->setVar('structure_context', $structure_context, false);
            $echo .= $fragment->parse('structure/table_row_categories.php');
        }

        $KAT->next();
    }
}

$fragment = new rex_fragment();
$fragment->setVar('content', $echo, false);
$fragment->setVar('structure_context', $structure_context, false);
$echo = $fragment->parse('structure/table_categories.php');

$heading = rex_i18n::msg('structure_categories_caption', $cat_name);
if (0 == $structure_context->getCategoryId()) {
    $heading = rex_i18n::msg('structure_root_level_categories_caption');
}
$fragment = new rex_fragment();
$fragment->setVar('heading', $heading, false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');

// --------------------------------------------- ARTIKEL LISTE

$echo = '';


if ($structure_context->getCategoryId() > 0 || (0 == $structure_context->getCategoryId() && !rex::getUser()->getComplexPerm('structure')->hasMountpoints())) {

    // --------------------- READ TEMPLATES
    $tmpl_head = '';
    if ($structure_context->hasTemplates()) {
        $template_select = new rex_template_select($structure_context->getCategoryId(), $structure_context->getClangId());
        $template_select->setName('template_id');
        $template_select->setSize(1);
        $template_select->setStyle('class="form-control selectpicker"');

        $TEMPLATE_NAME = $template_select->getTemplates();
        $TEMPLATE_NAME[0] = rex_i18n::msg('template_default_name');

        $tmpl_head = rex_i18n::msg('header_template');
    }

    // --------------------- ARTIKEL LIST

    // ---------- COUNT DATA
    $sql = rex_sql::factory();
    // $sql->setDebug();
    $sql->setQuery('SELECT COUNT(*) as artCount
                FROM
                    ' . rex::getTablePrefix() . 'article
                WHERE
                    ((parent_id=' . $structure_context->getCategoryId() . ' AND startarticle=0) OR (id=' . $structure_context->getCategoryId() . ' AND startarticle=1))
                    AND clang_id=' . $structure_context->getClangId() . '
                ORDER BY
                    priority, name');

    // --------------------- ADD PAGINATION

    $artPager = new rex_pager(30, 'artstart');
    $artPager->setRowCount($sql->getValue('artCount'));
    $artFragment = new rex_fragment();
    $artFragment->setVar('urlprovider', $structure_context->getContext());
    $artFragment->setVar('pager', $artPager);
    echo $artFragment->parse('core/navigations/pagination.php');

    // ---------- READ DATA
    $sql->setQuery('SELECT *
                FROM
                    ' . rex::getTablePrefix() . 'article
                WHERE
                    ((parent_id=' . $structure_context->getCategoryId() . ' AND startarticle=0) OR (id=' . $structure_context->getCategoryId() . ' AND startarticle=1))
                    AND clang_id=' . $structure_context->getClangId() . '
                ORDER BY
                    priority, name
                LIMIT ' . $artPager->getCursor() . ',' . $artPager->getRowsPerPage());

    // ----------- PRINT OUT THE ARTICLES

    // --------------------- ARTIKEL ADD FORM
    if ('add_art' == $structure_context->getFunction() && $structure_context->hasCategoryPermission()) {
        $tmpl_td = '';
        if ($structure_context->hasTemplates()) {
            $template_select->setSelected();
            $tmpl_td = $template_select->get();
        }

        $fragment = new rex_fragment();
        $fragment->setVar('artPager', $artPager, false);
        $fragment->setVar('sql', null, false);
        $fragment->setVar('tmpl_td', $tmpl_td, false);
        $fragment->setVar('structure_context', $structure_context, false);
        $echo .= $fragment->parse('structure/table_row_articles_form.php');
    }

    // --------------------- ARTIKEL LIST
    for ($i = 0; $i < $sql->getRows(); ++$i) {
        $fields = [
            rex_structure_field::factory('icon')
                ->setField(rex_structure_action_icon::factory('icon')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['class' => 'rex-table-icon']),
            rex_structure_field::factory('id')
                ->setField(rex_structure_action_id::factory('id')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['class' => 'rex-table-id', 'data-title' => rex_i18n::msg('header_id')]),
            rex_structure_field::factory('name')
                ->setField(rex_structure_action_name::factory('name')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['data-title' => rex_i18n::msg('header_article_name')]),
            rex_structure_field::factory('template')
                ->setField(rex_structure_action_template::factory('template')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['data-title' => rex_i18n::msg('header_template')])
                ->setCondition($structure_context->hasTemplates()),
            rex_structure_field::factory('date')
                ->setField(rex_structure_action_createdate::factory('date')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['data-title' => rex_i18n::msg('header_date')]),
            rex_structure_field::factory('priority')
                ->setField(rex_structure_action_priority::factory('priority')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['class' => 'rex-table-priority', 'data-title' => rex_i18n::msg('header_priority')]),
        ];

        // ARTIKEL EDIT FORM
        if ('edit_art' == $structure_context->getFunction() && $sql->getValue('id') == $structure_context->getArticleId() && $structure_context->hasCategoryPermission()) {
            $fields[] = rex_structure_field::factory('submit')
                ->setField(rex_structure_action_submit::factory('submit')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['class' => 'rex-table-action', 'colspan' => 3]);

            $row = rex_structure_field::factory('article_row_'.$i)
                ->setFields($fields)
                ->setFragment('structure\tr.php')
                ->setAttributes(['class' => 'mark'.(1 == $sql->getValue('startarticle') ? ' rex-startarticle' : '')]);
        }
        // ARTIKEL NORMAL VIEW | EDIT AND ENTER | NO EDIT NO ENTER
        else {
            $fields[] = rex_structure_field::factory('change')
                ->setField(rex_structure_action_change::factory('change')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['class' => 'rex-table-action']);
            $fields[] = rex_structure_field::factory('delete')
                ->setField(rex_structure_action_delete::factory('delete')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['class' => 'rex-table-action']);
            $fields[] = rex_structure_field::factory('status')
                ->setField(rex_structure_action_status::factory('status')->setContext($structure_context, $sql))
                ->setFragment('structure\td.php')
                ->setAttributes(['class' => 'rex-table-action']);

            $row = rex_structure_field::factory('article_row_'.$i)
                ->setFields($fields)
                ->setFragment('structure\tr.php')
                ->setAttributes(['class' => 1 == $sql->getValue('startarticle') ? ' rex-startarticle' : '']);

        }
        $echo .= $row->get();

        $sql->next();
    }

    $fragment = new rex_fragment();
    $fragment->setVar('tmpl_head', $tmpl_head, false);
    $fragment->setVar('structure_context', $structure_context, false);
    $fragment->setVar('content', $echo, false);
    $echo = $fragment->parse('structure/table_articles.php');
}

$heading = rex_i18n::msg('structure_articles_caption', $cat_name);
if (0 == $structure_context->getCategoryId()) {
    $heading = rex_i18n::msg('structure_root_level_articles_caption');
}
$fragment = new rex_fragment();
$fragment->setVar('heading', $heading, false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');
