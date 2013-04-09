<?php

/**
 *
 * @package redaxo5
 */

// basic request vars
$category_id = rex_request('category_id', 'int');
$article_id  = rex_request('article_id',  'int');
$clang       = rex_request('clang',       'int');
$ctype       = rex_request('ctype',       'int');

// additional request vars
$artstart    = rex_request('artstart',    'int');
$catstart    = rex_request('catstart',    'int');
$edit_id     = rex_request('edit_id',     'int');
$function    = rex_request('function',    'string');

$info = '';
$warning = '';

$category_id = rex_category::get($category_id) ? $category_id : 0;
$article_id = rex_article::get($article_id) ? $article_id : 0;
$clang = rex_clang::exists($clang) ? $clang : rex::getProperty('start_clang_id');



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
            echo '
        <!-- *** OUTPUT OF CLANG-VALIDATE - START *** -->
                    ' . rex_view::warning('You have no permission to this area') . '
        <!-- *** OUTPUT OF CLANG-VALIDATE - END *** -->
            ';
            exit;
        }
    }
} else {
    $clang = rex::getProperty('start_clang_id');
}


$context = new rex_context([
    'page' => 'structure',
    'category_id' => $category_id,
    'article_id' => $article_id,
    'clang' => $clang
]);



// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER_PRE', '', [
    'context' => $context
]));



// --------------------------------------------- Languages
echo rex_view::clangSwitch($context);

// --------------------------------------------- TITLE
echo rex_view::title(rex_i18n::msg('title_structure'));

// --------------------------------------------- Path
require __DIR__ . '/../functions/function_rex_category.php';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();
$artStatusTypes = rex_article_service::statusTypes();





// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE
$cat_name = 'Homepage';
$category = rex_category::get($category_id, $clang);
if ($category) {
    $cat_name = $category->getName();
}

$add_category = '';
if ($KATPERM) {
    $add_category = '<a href="' . $context->getUrl(['function' => 'add_cat']) . '"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'add') . '><span class="rex-icon rex-icon-add-category"></span></a>';
}

$add_header = '';
$data_colspan = 4;
if (rex::getUser()->hasPerm('advancedMode[]')) {
    $add_header = '<th class="rex-id">' . rex_i18n::msg('header_id') . '</th>';
    $data_colspan = 5;
}

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER', '', [
    'category_id' => $category_id,
    'clang' => $clang
]));

// --------------------- SEARCH BAR
require_once $this->getPath('functions/function_rex_searchbar.php');
echo rex_structure_searchbar($context);

// --------------------- COUNT CATEGORY ROWS

$KAT = rex_sql::factory();
// $KAT->setDebug();
if (count($mountpoints) > 0 && $category_id == 0) {
    $parent_id = implode(',', $mountpoints);
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang=' . $clang . ' ORDER BY catname');
} else {
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $category_id . ' AND startarticle=1 AND clang=' . $clang . ' ORDER BY catpriority');
}

// --------------------- ADD PAGINATION

$catPager = new rex_pager(30, 'catstart');
$catPager->setRowCount($KAT->getValue('rowCount'));
$catFragment = new rex_fragment();
$catFragment->setVar('urlprovider', $context);
$catFragment->setVar('pager', $catPager);
echo $catFragment->parse('pagination.php');

// --------------------- GET THE DATA

if (count($mountpoints) > 0 && $category_id == 0) {
    $parent_id = implode(',', $mountpoints);
    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang=' . $clang . ' ORDER BY catname LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
} else {
    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $category_id . ' AND startarticle=1 AND clang=' . $clang . ' ORDER BY catpriority LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
}



$echo = '';
// ---------- INLINE THE EDIT/ADD FORM
if ($function == 'add_cat' || $function == 'edit_cat') {

    $echo .= '
    <div class="rex-form" id="rex-form-structure-category">
    <form action="' . $context->getUrl(['catstart' => $catstart]) . '" method="post">
        <fieldset>

            <input type="hidden" name="edit_id" value="' . $edit_id . '" />';
}


// --------------------- PRINT CATS/SUBCATS
$echo .= '
            <table id="rex-table-categories" class="rex-table rex-table-middle rex-table-striped">
                <caption>' . rex_i18n::msg('structure_categories_caption', $cat_name) . '</caption>
                <thead>
                    <tr>
                        <th class="rex-slim">' . $add_category . '</th>
                        ' . $add_header . '
                        <th class="rex-name">' . rex_i18n::msg('header_category') . '</th>
                        <th class="rex-priority">' . rex_i18n::msg('header_priority') . '</th>
                        <th class="rex-function" colspan="3">' . rex_i18n::msg('header_status') . '</th>
                    </tr>
                </thead>
                <tbody>';
if ($category_id != 0 && ($category = rex_category::get($category_id))) {
    $echo .= '<tr>
                    <td class="rex-slim"><span class="rex-icon rex-icon-open-category"></span></td>';
    if (rex::getUser()->hasPerm('advancedMode[]')) {
        $echo .= '<td class="rex-id">-</td>';
    }


    $echo .= '<td class="rex-name"><a href="' . $context->getUrl(['category_id' => $category->getParentId()]) . '">..</a></td>';
    $echo .= '<td class="rex-priority">&nbsp;</td>';
    $echo .= '<td colspan="3">&nbsp;</td>';
    $echo .= '</tr>';

}

// --------------------- KATEGORIE ADD FORM

if ($function == 'add_cat' && $KATPERM) {
    $add_td = '';
    if (rex::getUser()->hasPerm('advancedMode[]')) {
        $add_td = '<td class="rex-id">-</td>';
    }

    $meta_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
        'id' => $category_id,
        'clang' => $clang
    ]));
    $add_buttons = '
        <input type="hidden" name="rex-api-call" value="category_add" />
        <input type="hidden" name="parent-category-id" value="' . $category_id . '" />
        <button class="rex-button" type="submit" name="category-add-button"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'save') . '>' . rex_i18n::msg('add_category') . '</button>';

    $class = 'rex-active';

    $echo .= '
                <tr class="' . $class . '">
                    <td class="rex-slim"><span class="rex-icon rex-icon-category"></span></td>
                    ' . $add_td . '
                    <td class="rex-name"><input type="text" id="rex-form-field-name" name="category-name" /></td>
                    <td class="rex-priority"><input class="rex-number" type="text" id="rex-form-field-priority" name="category-position" value="' . ($KAT->getRows() + 1) . '" /></td>
                    <td>' . $meta_buttons . '</td>
                    <td colspan="2">' . $add_buttons . '</td>
                </tr>';

    // ----- EXTENSION POINT
    $echo .= rex_extension::registerPoint(new rex_extension_point('CAT_FORM_ADD', '', [
        'id' => $category_id,
        'clang' => $clang,
        'data_colspan' => ($data_colspan + 1),
    ]));
}





// --------------------- KATEGORIE LIST

for ($i = 0; $i < $KAT->getRows(); $i++) {
    $i_category_id = $KAT->getValue('id');

    $kat_link = $context->getUrl(['category_id' => $i_category_id]);
    $kat_icon_td = '<td class="rex-slim"><a href="' . $kat_link . '" title="' . htmlspecialchars($KAT->getValue('catname')) . '"><span class="rex-icon rex-icon-category"></span></a></td>';

    $kat_status = $catStatusTypes[$KAT->getValue('status')][0];
    $status_class = $catStatusTypes[$KAT->getValue('status')][1];

    if ($KATPERM) {
        if ($KATPERM && rex::getUser()->hasPerm('publishCategory[]')) {
            $kat_status = '<a class="rex-link rex-status ' . $status_class . '" href="' . $context->getUrl(['category-id' => $i_category_id, 'rex-api-call' => 'category_status', 'catstart' => $catstart]) . '">' . $kat_status . '</a>';
        } else {
            $kat_status = '<span class="' . $status_class . ' rex-disabled">' . $kat_status . '</span>';
        }

        if (isset ($edit_id) && $edit_id == $i_category_id && $function == 'edit_cat') {
            // --------------------- KATEGORIE EDIT FORM
            $add_td = '';
            if (rex::getUser()->hasPerm('advancedMode[]')) {
                $add_td = '<td class="rex-id">' . $i_category_id . '</td>';
            }

            // ----- EXTENSION POINT
            $meta_buttons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
                'id' => $edit_id,
                'clang' => $clang,
            ]));

            $add_buttons = '
            <input type="hidden" name="rex-api-call" value="category_edit" />
            <input type="hidden" name="category-id" value="' . $edit_id . '" />
            <button class="rex-button" type="submit" name="category-edit-button"' . rex::getAccesskey(rex_i18n::msg('save_category'), 'save') . '>' . rex_i18n::msg('save_category') . '</button>';

            $class = 'rex-active';
            if ($meta_buttons != '') {
                $class .= ' rex-has-metainfo';
            }

            $echo .= '
                <tr id="rex-structure-category-' . $i_category_id . '" class="' . $class . '">
                    ' . $kat_icon_td . '
                    ' . $add_td . '
                    <td class="rex-name"><input type="text" id="rex-form-field-name" name="category-name" value="' . htmlspecialchars($KAT->getValue('catname')) . '" /></td>
                    <td class="rex-priority"><input class="rex-number" type="text" id="rex-form-field-priority" name="category-position" value="' . htmlspecialchars($KAT->getValue('catpriority')) . '" /></td>
                    <td>' . $meta_buttons . '</td>
                    <td colspan="2">' . $add_buttons . '</td>
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

            $add_td = '';
            if (rex::getUser()->hasPerm('advancedMode[]')) {
                $add_td = '<td class="rex-id">' . $i_category_id . '</td>';
            }

            $category_delete = '<a class="rex-link rex-delete" href="' . $context->getUrl(['category-id' => $i_category_id, 'rex-api-call' => 'category_delete', 'catstart' => $catstart]) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?">' . rex_i18n::msg('delete') . '</a>';

            $echo .= '
                <tr id="rex-structure-category-' . $i_category_id . '">
                    ' . $kat_icon_td . '
                    ' . $add_td . '
                    <td class="rex-name"><a href="' . $kat_link . '">' . htmlspecialchars($KAT->getValue('catname')) . '</a></td>
                    <td class="rex-priority">' . htmlspecialchars($KAT->getValue('catpriority')) . '</td>
                    <td class="rex-edit"><a class="rex-link rex-edit" href="' . $context->getUrl(['edit_id' => $i_category_id, 'function' => 'edit_cat', 'catstart' => $catstart]) . '">' . rex_i18n::msg('change') . '</a></td>
                    <td class="rex-delete">' . $category_delete . '</td>
                    <td class="rex-status">' . $kat_status . '</td>
                </tr>';
        }

    } elseif (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($i_category_id)) {
            // --------------------- KATEGORIE WITH READ
            $add_td = '';
            if (rex::getUser()->hasPerm('advancedMode[]')) {
                $add_td = '<td class="rex-id">' . $i_category_id . '</td>';
            }

            $echo .= '
                <tr id="rex-structure-category-' . $i_category_id . '">
                    ' . $kat_icon_td . '
                    ' . $add_td . '
                    <td class="rex-name"><a href="' . $kat_link . '">' . $KAT->getValue('catname') . '</a></td>
                    <td class="rex-priority">' . htmlspecialchars($KAT->getValue('catpriority')) . '</td>
                    <td class="rex-edit"><span class="rex-edit rex-disabled">' . rex_i18n::msg('change') . '</span></td>
                    <td class="rex-delete"><span class="rex-delete rex-disabled">' . rex_i18n::msg('delete') . '</span></td>
                    <td class="rex-status"><span class="' . $status_class . ' rex-disabled">' . $kat_status . '</span></td>
                </tr>';
    }

    $KAT->next();
}

$echo .= '
            </tbody>
        </table>';

if ($function == 'add_cat' || $function == 'edit_cat') {
    $echo .= '
        <script type="text/javascript">
            <!--
            jQuery(function($){
                $("#rex-form-field-name").focus();
            });
            //-->
        </script>
    </fieldset>
</form>
</div>';
}


echo rex_view::contentBlock($echo);



// --------------------------------------------- ARTIKEL LISTE

$echo = '';

// --------------------- READ TEMPLATES

if ($category_id > 0 || ($category_id == 0 && !rex::getUser()->getComplexPerm('structure')->hasMountpoints())) {

    $withTemplates = $this->getPlugin('content')->isAvailable();
    $tmpl_head = '';
    if ($withTemplates) {
        $template_select = new rex_select;
        $template_select->setName('template_id');
        $template_select->setId('rex-form-template');
        $template_select->setSize(1);

        $templates = rex_template::getTemplatesForCategory($category_id);
        if (count($templates) > 0) {
            foreach ($templates as $t_id => $t_name) {
                $template_select->addOption(rex_i18n::translate($t_name, false), $t_id);
                $TEMPLATE_NAME[$t_id] = rex_i18n::translate($t_name);
            }
        } else {
            $template_select->addOption(rex_i18n::msg('option_no_template'), '0');
            $TEMPLATE_NAME[0] = rex_i18n::msg('template_default_name');
        }
        $tmpl_head = '<th class="rex-template">' . rex_i18n::msg('header_template') . '</th>';
    }

    // --------------------- ARTIKEL LIST
    $art_add_link = '';
    if ($KATPERM) {
        $art_add_link = '<a href="' . $context->getUrl(['function' => 'add_art']) . '"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2') . '><span class="rex-icon rex-icon-add-article"></span></a>';
    }

    $add_head = '';
    if (rex::getUser()->hasPerm('advancedMode[]')) {
        $add_head = '<th class="rex-id">' . rex_i18n::msg('header_id') . '</th>';
    }

    // ---------- COUNT DATA
    $sql = rex_sql::factory();
    // $sql->setDebug();
    $sql->setQuery('SELECT COUNT(*) as artCount
                FROM
                    ' . rex::getTablePrefix() . 'article
                WHERE
                    ((parent_id=' . $category_id . ' AND startarticle=0) OR (id=' . $category_id . ' AND startarticle=1))
                    AND clang=' . $clang . '
                ORDER BY
                    priority, name');

    // --------------------- ADD PAGINATION

    $artPager = new rex_pager(30, 'artstart');
    $artPager->setRowCount($sql->getValue('artCount'));
    $artFragment = new rex_fragment();
    $artFragment->setVar('urlprovider', $context);
    $artFragment->setVar('pager', $artPager);
    echo $artFragment->parse('pagination.php');

    // ---------- READ DATA
    $sql->setQuery('SELECT *
                FROM
                    ' . rex::getTablePrefix() . 'article
                WHERE
                    ((parent_id=' . $category_id . ' AND startarticle=0) OR (id=' . $category_id . ' AND startarticle=1))
                    AND clang=' . $clang . '
                ORDER BY
                    priority, name
                LIMIT ' . $artPager->getCursor() . ',' . $artPager->getRowsPerPage());


    // ---------- INLINE THE EDIT/ADD FORM
    if ($function == 'add_art' || $function == 'edit_art') {

        $echo .= '
        <div class="rex-form" id="rex-form-structure-article">
        <form action="' . $context->getUrl(['artstart' => $artstart]) . '" method="post">
            <fieldset>';
    }

    // ----------- PRINT OUT THE ARTICLES

    // for valid html
    $colspan = '';
    if ($sql->getRows() > 0) {
        $colspan = ' colspan="3"';
    }

    $echo .= '
            <table id="rex-table-articles" class="rex-table rex-table-middle rex-table-striped">
                <caption>' . rex_i18n::msg('structure_articles_caption', $cat_name) . '</caption>
                <thead>
                    <tr>
                        <th class="rex-slim">' . $art_add_link . '</th>
                        ' . $add_head . '
                        <th class="rex-name">' . rex_i18n::msg('header_article_name') . '</th>
                        <th class="rex-priority">' . rex_i18n::msg('header_priority') . '</th>
                        ' . $tmpl_head . '
                        <th class="rex-date">' . rex_i18n::msg('header_date') . '</th>
                        <th class="rex-function"' . $colspan . '>' . rex_i18n::msg('header_status') . '</th>
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
            $defaultTemplateId = rex::getProperty('default_template_id');
            if ($defaultTemplateId > 0 && isset($TEMPLATE_NAME[$defaultTemplateId])) {
                $template_select->setSelected($defaultTemplateId);

            } else {
                // template_id vom Startartikel erben
                $sql2 = rex_sql::factory();
                $sql2->setQuery('SELECT template_id FROM ' . rex::getTablePrefix() . 'article WHERE id=' . $category_id . ' AND clang=' . $clang . ' AND startarticle=1');
                if ($sql2->getRows() == 1) {
                    $template_select->setSelected($sql2->getValue('template_id'));
                }
            }
            $tmpl_td = '<td class="rex-template">' . $template_select->get() . '</td>';
        }


        $add_td = '';
        if (rex::getUser()->hasPerm('advancedMode[]')) {
            $add_td .= '<td class="rex-id">-</td>';
        }

        $echo .= '<tr class="rex-active">
                                <td class="rex-slim"><span class="rex-icon rex-icon-article"></span></td>
                                ' . $add_td . '
                                <td class="rex-name"><input type="text" id="rex-form-field-name" name="article-name" /></td>
                                <td class="rex-priority"><input class="rex-number" type="text" id="rex-form-field-priority" name="article-position" value="' . ($sql->getRows() + 1) . '" /></td>
                                ' . $tmpl_td . '
                                <td class="rex-date">' . rex_formatter::strftime(time(), 'date') . '</td>
                                <td' . $colspan . '><input type="hidden" name="rex-api-call" value="article_add" /><button class="rex-button" type="submit" name="artadd_function"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'save') . '>' . rex_i18n::msg('article_add') . '</button></td>
                            </tr>
                            ';
    }

    // --------------------- ARTIKEL LIST

    for ($i = 0; $i < $sql->getRows(); $i++) {

        if ($sql->getValue('id') == rex::getProperty('start_article_id')) {
            $class = ' rex-icon-sitestartarticle';
        } elseif ($sql->getValue('startarticle') == 1) {
            $class = ' rex-icon-startarticle';
        } else {
            $class = ' rex-icon-article';
        }

        $class_highlight = '';
        if ($sql->getValue('startarticle') == 1) {
            $class_highlight = ' rex-highlight';
        }

        // --------------------- ARTIKEL EDIT FORM

        if ($function == 'edit_art' && $sql->getValue('id') == $article_id && $KATPERM) {

            $add_td = '';
            if (rex::getUser()->hasPerm('advancedMode[]')) {
                $add_td .= '<td class="rex-id">' . $sql->getValue('id') . '</td>';
            }

            $tmpl_td = '';
            if ($withTemplates) {
                $template_select->setSelected($sql->getValue('template_id'));
                $tmpl_td = '<td class="rex-template">' . $template_select->get() . '</td>';
            }

            $echo .= '<tr id="rex-structure-article-' . $article_id . '" class="rex-active">
                                    <td class="rex-slim"><a href="' . $context->getUrl(['page' => 'content', 'article_id' => $sql->getValue('id')]) . '" title="' . htmlspecialchars($sql->getValue('name')) . '"><span class="rex-icon' . $class . '"></span></a></td>
                                    ' . $add_td . '
                                    <td class="rex-name"><input type="text" id="rex-form-field-name" name="article-name" value="' . htmlspecialchars($sql->getValue('name')) . '" /></td>
                                    <td class="rex-priority"><input class="rex-number" type="text" id="rex-form-field-priority" name="article-position" value="' . htmlspecialchars($sql->getValue('priority')) . '" /></td>
                                    ' . $tmpl_td . '
                                    <td class="rex-date">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                                    <td' . $colspan . '><input type="hidden" name="rex-api-call" value="article_edit" /><button class="rex-button" type="submit" name="artedit_function"' . rex::getAccesskey(rex_i18n::msg('article_save'), 'save') . '>' . rex_i18n::msg('article_save') . '</button></td>
                                </tr>
                                ';

        } elseif ($KATPERM) {
            // --------------------- ARTIKEL NORMAL VIEW | EDIT AND ENTER

            $add_td = '';
            if (rex::getUser()->hasPerm('advancedMode[]')) {
                $add_td = '<td class="rex-id' . $class_highlight . '">' . $sql->getValue('id') . '</td>';
            }

            $article_status = $artStatusTypes[$sql->getValue('status')][0];
            $article_class = $artStatusTypes[$sql->getValue('status')][1];

            $add_extra = '';
            if ($sql->getValue('startarticle') == 1) {
                $add_extra = '<td class="rex-delete"><span class="rex-delete rex-disabled">' . rex_i18n::msg('delete') . '</span></td>
                                            <td class="rex-status"><span class="' . $article_class . ' rex-disabled">' . $article_status . '</span></td>';
            } else {
                if ($KATPERM && rex::getUser()->hasPerm('publishArticle[]')) {
                    $article_status = '<a class="rex-link ' . $article_class . '" href="' . $context->getUrl(['article_id' => $sql->getValue('id'), 'rex-api-call' => 'article_status', 'artstart' => $artstart]) . '">' . $article_status . '</a>';
                } else {
                    $article_status = '<span class="' . $article_class . ' rex-disabled">' . $article_status . '</span>';
                }

                $article_delete = '<a class="rex-link rex-delete" href="' . $context->getUrl(['article_id' => $sql->getValue('id'), 'rex-api-call' => 'article_delete', 'artstart' => $artstart]) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?">' . rex_i18n::msg('delete') . '</a>';

                $add_extra = '<td class="rex-delete">' . $article_delete . '</td>
                                            <td class="rex-status">' . $article_status . '</td>';
            }

            $editModeUrl = $context->getUrl(['page' => 'content', 'article_id' => $sql->getValue('id'), 'mode' => 'edit']);

            $tmpl_td = '';
            if ($withTemplates) {
                $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';
                $tmpl_td = '<td class="rex-template">' . $tmpl . '</td>';
            }

            $echo .= '<tr id="rex-structure-article-' . $sql->getValue('id') . '">
                                    <td class="rex-slim"><a href="' . $editModeUrl . '" title="' . htmlspecialchars($sql->getValue('name')) . '"><span class="rex-icon' . $class . '"></span></a></td>
                                    ' . $add_td . '
                                    <td class="rex-name' . $class_highlight . '"><a href="' . $editModeUrl . '">' . htmlspecialchars($sql->getValue('name')) . '</a></td>
                                    <td class="rex-priority">' . htmlspecialchars($sql->getValue('priority')) . '</td>
                                    ' . $tmpl_td . '
                                    <td class="rex-date">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                                    <td><a class="rex-link rex-edit" href="' . $context->getUrl(['article_id' => $sql->getValue('id'), 'function' => 'edit_art', 'artstart' => $artstart]) . '">' . rex_i18n::msg('change') . '</a></td>
                                    ' . $add_extra . '
                                </tr>
                                ';

        } else {
            // --------------------- ARTIKEL NORMAL VIEW | NO EDIT NO ENTER

            $add_td = '';
            if (rex::getUser()->hasPerm('advancedMode[]')) {
                $add_td = '<td class="rex-id">' . $sql->getValue('id') . '</td>';
            }

            $art_status = $artStatusTypes[$sql->getValue('status')][0];
            $art_status_class = $artStatusTypes[$sql->getValue('status')][1];

            $tmpl_td = '';
            if ($withTemplates) {
                $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';
                $tmpl_td = '<td class="rex-template">' . $tmpl . '</td>';
            }

            $echo .= '<tr id="rex-structure-article-' . $sql->getValue('id') . '">
                                    <td class="rex-slim"><span class="rex-icon' . $class . '"></span></td>
                                    ' . $add_td . '
                                    <td class="rex-name">' . htmlspecialchars($sql->getValue('name')) . '</td>
                                    <td class="rex-priority">' . htmlspecialchars($sql->getValue('priority')) . '</td>
                                    ' . $tmpl_td . '
                                    <td class="rex-date">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                                    <td class="rex-edit"><span class="rex-edit rex-disabled">' . rex_i18n::msg('change') . '</span></td>
                                    <td class="rex-delete"><span class="rex-delete rex-disabled">' . rex_i18n::msg('delete') . '</span></td>
                                    <td class="rex-status"><span class="' . $art_status_class . ' rex-disabled">' . $art_status . '</span></td>
                                </tr>
                                ';
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
            <script type="text/javascript">
                <!--
                jQuery(function($){
                    $("#rex-form-field-name").focus();
                });
                //-->
            </script>
        </fieldset>
    </form>
    </div>';
    }
}


echo rex_view::contentBlock($echo);
