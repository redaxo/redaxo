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
    'rows_per_page' => $addon->getProperty('rows_per_page', 50),
]);

if (0 == $structureContext->getClangId()) {
    if (rex_clang::exists(0)) {
        echo rex_view::error('Oooops. Your clang ids start with <code>0</code>. Looks like a broken REDAXO 4.x to 5.x upgrade. Please update all your database tables, php code (if there are any hard coded clang ids) aswell as additional configurations in add-ons, e.g. YRewrite. You may start with updating those tables: <code>rex_article</code>, <code>rex_article_slice</code>, <code>rex_clang</code>, by increasing every clang id <code>+ 1</code>.');
        exit;
    }
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
$articleId = $structureContext->getArticleId();
$categoryId = $structureContext->getCategoryId();
$clang = $structureContext->getClangId();
require __DIR__ . '/../functions/function_rex_category.php';

// -------------- STATUS_TYPE Map
$catStatusTypes = rex_category_service::statusTypes();
$artStatusTypes = rex_article_service::statusTypes();

// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

// --------------------------------------------- KATEGORIE LISTE
$catName = rex_i18n::msg('root_level');
$category = rex_category::get($structureContext->getCategoryId(), $structureContext->getClangId());
if ($category) {
    $catName = $category->getName();
}

$addCategory = '';
if (rex::getUser()->hasPerm('addCategory[]') && $structureContext->hasCategoryPermission()) {
    $addCategory = '<a class="rex-link-expanded" href="' . $structureContext->getContext()->getUrl(['function' => 'add_cat', 'catstart' => $structureContext->getCatStart()]) . '"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'add') . '><i class="rex-icon rex-icon-add-category"></i></a>';
}

$dataColspan = 5;

// --------------------- Extension Point
echo rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_HEADER', '', [
    'category_id' => $structureContext->getCategoryId(),
    'clang' => $structureContext->getClangId(),
]));

// --------------------- COUNT CATEGORY ROWS

$KAT = rex_sql::factory();
// $KAT->setDebug();
if (count($structureContext->getMountpoints()) > 0 && 0 == $structureContext->getCategoryId()) {
    $parentIds = $KAT->in($structureContext->getMountpoints());
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parentIds . ') AND startarticle=1 AND clang_id=?', [$structureContext->getClangId()]);
} else {
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=? AND startarticle=1 AND clang_id=?', [$structureContext->getCategoryId(), $structureContext->getClangId()]);
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
    $parentIds = $KAT->in($structureContext->getMountpoints());

    $KAT->setQuery('SELECT parent_id FROM ' . rex::getTable('article') . ' WHERE id IN (' . $parentIds . ') GROUP BY parent_id');
    $orderBy = $KAT->getRows() > 1 ? 'catname' : 'catpriority';

    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parentIds . ') AND startarticle=1 AND clang_id = ? ORDER BY ' . $orderBy . ' LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage(), [$structureContext->getClangId()]);
} else {
    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE parent_id = ? AND startarticle=1 AND clang_id = ? ORDER BY catpriority LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage(), [$structureContext->getCategoryId(), $structureContext->getClangId()]);
}

$echo = '';
// ---------- INLINE THE EDIT/ADD FORM
if ('add_cat' == $structureContext->getFunction() || 'edit_cat' == $structureContext->getFunction()) {
    $echo .= '
    <form action="' . $structureContext->getContext()->getUrl(['catstart' => $structureContext->getCatStart()]) . '" method="post">
        <fieldset>

            <input type="hidden" name="edit_id" value="' . $structureContext->getEditId() . '" />';
}

$canEdit = rex::getUser()->hasPerm('editCategory[]');
$canDelete = rex::getUser()->hasPerm('deleteCategory[]');
$colspan = (int) $canEdit + (int) $canDelete + 1;

// --------------------- PRINT CATS/SUBCATS
$echo .= '
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon">' . $addCategory . '</th>
                        <th class="rex-table-id">' . rex_i18n::msg('header_id') . '</th>
                        <th class="rex-table-category">' . rex_i18n::msg('header_category') . '</th>
                        <th class="rex-table-priority">' . rex_i18n::msg('header_priority') . '</th>
                        <th class="rex-table-action" colspan="'.$colspan.'">' . rex_i18n::msg('header_status') . '</th>
                    </tr>
                </thead>
                <tbody>';

// --------------------- KATEGORIE ADD FORM

if ('add_cat' == $structureContext->getFunction() && rex::getUser()->hasPerm('addCategory[]') && $structureContext->hasCategoryPermission()) {
    $metaButtons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
        'id' => $structureContext->getCategoryId(),
        'clang' => $structureContext->getClangId(),
    ]));
    $addButtons = rex_api_category_add::getHiddenFields().'
        <input type="hidden" name="parent-category-id" value="' . $structureContext->getCategoryId() . '" />
        <button class="btn btn-save" type="submit" name="category-add-button"' . rex::getAccesskey(rex_i18n::msg('add_category'), 'save') . '>' . rex_i18n::msg('add_category') . '</button>';

    $class = 'mark';

    $echo .= '
                <tr class="' . $class . '">
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-category"></i></td>
                    <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">-</td>
                    <td class="rex-table-category" data-title="' . rex_i18n::msg('header_category') . '"><input class="form-control" type="text" name="category-name" class="rex-js-autofocus" autofocus /></td>
                    <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="category-position" value="' . ($catPager->getRowCount() + 1) . '" /></td>
                    <td class="rex-table-action" colspan="'.$colspan.'">' . $metaButtons . $addButtons . '</td>
                </tr>';

    // ----- EXTENSION POINT
    $echo .= rex_extension::registerPoint(new rex_extension_point('CAT_FORM_ADD', '', [
        'id' => $structureContext->getCategoryId(),
        'clang' => $structureContext->getClangId(),
        'data_colspan' => ($dataColspan + 1),
    ]));
}

// --------------------- KATEGORIE LIST
if ($KAT->getRows() > 0) {
    for ($i = 0; $i < $KAT->getRows(); ++$i) {
        $iCategoryId = $KAT->getValue('id');

        $katLink = $structureContext->getContext()->getUrl(['category_id' => $iCategoryId]);

        /** @var rex_category $katObject */
        $katObject = rex_category::get($KAT->getValue('id'));
        $katHasChildElements = (count($katObject->getChildren()) > 0 || count($katObject->getArticles()) > 1); // contains child categories or articles other than the start article
        $katIconClass = $katHasChildElements ? 'rex-icon-category' : 'rex-icon-category-without-elements';
        $katIconTitle = $katHasChildElements ? rex_i18n::msg('category_has_child_elements') : rex_i18n::msg('category_without_child_elements');
        $katIconTd = '<td class="rex-table-icon"><a class="rex-link-expanded" href="' . $katLink . '" title="' . rex_escape($KAT->getValue('catname')) . '"><i class="rex-icon ' . $katIconClass . '" title="' . $katIconTitle . '"></i></a></td>';

        $katStatus = $catStatusTypes[$KAT->getValue('status')][0];
        $statusClass = $catStatusTypes[$KAT->getValue('status')][1];
        $statusIcon = $catStatusTypes[$KAT->getValue('status')][2];

        $tdLayoutClass = '';
        if ($structureContext->hasCategoryPermission()) {
            if (rex::getUser()->hasPerm('publishCategory[]')) {
                $tdLayoutClass = 'rex-table-action-no-dropdown';
                if (count($catStatusTypes) > 2) {
                    $tdLayoutClass = 'rex-table-action-dropdown';
                    $katStatus = '<div class="dropdown"><a href="#" class="dropdown-toggle '. $statusClass .'" type="button" data-toggle="dropdown"><i class="rex-icon ' . $statusIcon . '"></i>&nbsp;'.$katStatus.'&nbsp;<span class="caret"></span></a><ul class="dropdown-menu dropdown-menu-right">';
                    foreach ($catStatusTypes as $catStatusKey => $catStatusType) {
                        $katStatus .= '<li><a class="' . $catStatusType[1] . '" href="' . $structureContext->getContext()->getUrl(['category-id' => $iCategoryId, 'catstart' => $structureContext->getCatStart(), 'cat_status' => $catStatusKey] + rex_api_category_status::getUrlParams()) . '">' . $catStatusType[0] . '</a></li>';
                    }
                    $katStatus .= '</ul></div>';
                } else {
                    $katStatus = '<a class="' . $statusClass . '" href="' . $structureContext->getContext()->getUrl(['category-id' => $iCategoryId, 'catstart' => $structureContext->getCatStart()] + rex_api_category_status::getUrlParams()) . '"><i class="rex-icon ' . $statusIcon . '"></i>&nbsp;' . $katStatus . '</a>';
                }
            } else {
                $katStatus = '<span class="' . $statusClass . ' text-muted"><i class="rex-icon ' . $statusIcon . '"></i> ' . $katStatus . '</span>';
            }

            if ($canEdit && $structureContext->getEditId() == $iCategoryId && 'edit_cat' == $structureContext->getFunction()) {
                // --------------------- KATEGORIE EDIT FORM

                // ----- EXTENSION POINT
                $metaButtons = rex_extension::registerPoint(new rex_extension_point('CAT_FORM_BUTTONS', '', [
                    'id' => $structureContext->getEditId(),
                    'clang' => $structureContext->getClangId(),
                ]));

                $addButtons = rex_api_category_edit::getHiddenFields().'
                <input type="hidden" name="category-id" value="' . $structureContext->getEditId() . '" />
                <button class="btn btn-save" type="submit" name="category-edit-button"' . rex::getAccesskey(rex_i18n::msg('save_category'), 'save') . '>' . rex_i18n::msg('save_category') . '</button>';

                $class = 'mark';
                if ('' != $metaButtons) {
                    $class .= ' rex-has-metainfo';
                }

                $echo .= '
                    <tr class="' . $class . '">
                        ' . $katIconTd . '
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $iCategoryId . '</td>
                        <td class="rex-table-category" data-title="' . rex_i18n::msg('header_category') . '"><input class="form-control" type="text" name="category-name" value="' . rex_escape($KAT->getValue('catname')) . '" class="rex-js-autofocus" autofocus /></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="category-position" value="' . rex_escape($KAT->getValue('catpriority')) . '" /></td>
                        <td class="rex-table-action" colspan="'.$colspan.'">' . $metaButtons . $addButtons . '</td>
                    </tr>';

                // ----- EXTENSION POINT
                $echo .= rex_extension::registerPoint(new rex_extension_point('CAT_FORM_EDIT', '', [
                    'id' => $structureContext->getEditId(),
                    'clang' => $structureContext->getClangId(),
                    'category' => $KAT,
                    'catname' => $KAT->getValue('catname'),
                    'catpriority' => $KAT->getValue('catpriority'),
                    'data_colspan' => ($dataColspan + 1),
                ]));
            } else {
                // --------------------- KATEGORIE WITH WRITE

                $echo .= '
                    <tr>
                        ' . $katIconTd . '
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $iCategoryId . '</td>
                        <td class="rex-table-category" data-title="' . rex_i18n::msg('header_category') . '"><a class="rex-link-expanded" href="' . $katLink . '">' . rex_escape($KAT->getValue('catname')) . '</a></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . rex_escape($KAT->getValue('catpriority')) . '</td>';
                if ($canEdit) {
                    $echo .= '
                        <td class="rex-table-action"><a class="rex-link-expanded" href="' . $structureContext->getContext()->getUrl(['edit_id' => $iCategoryId, 'function' => 'edit_cat', 'catstart' => $structureContext->getCatStart()]) . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</a></td>';
                }
                if ($canDelete) {
                    $echo .= '
                        <td class="rex-table-action"><a class="rex-link-expanded" href="' . $structureContext->getContext()->getUrl(['category-id' => $iCategoryId, 'catstart' => $structureContext->getCatStart()] + rex_api_category_delete::getUrlParams()) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</a></td>';
                }
                $echo .= '
                        <td class="rex-table-action '.$tdLayoutClass.'">' . $katStatus . '</td>
                    </tr>';
            }
        } elseif (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($iCategoryId)) {
            // --------------------- KATEGORIE WITH READ

            $echo .= '
                    <tr>
                        ' . $katIconTd . '
                        <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $iCategoryId . '</td>
                        <td class="rex-table-category" data-title="' . rex_i18n::msg('header_category') . '"><a class="rex-link-expanded" href="' . $katLink . '">' . $KAT->getValue('catname') . '</a></td>
                        <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . rex_escape($KAT->getValue('catpriority')) . '</td>';
            if ($canEdit) {
                $echo .= '
                        <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</span></td>';
            }
            if ($canDelete) {
                $echo .= '
                        <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</span></td>';
            }
            $echo .= '
                        <td class="rex-table-action"><span class="' . $statusClass . ' text-muted"><i class="rex-icon ' . $statusIcon . '"></i> ' . $katStatus . '</span></td>
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
                    <td colspan="'.$colspan.'"></td>
                </tr>';
}

$echo .= '
            </tbody>
        </table>';

if ('add_cat' == $structureContext->getFunction() || 'edit_cat' == $structureContext->getFunction()) {
    $echo .= '
    </fieldset>
</form>';
}

$heading = rex_i18n::msg('structure_categories_caption', $catName);
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

$templateSelect = null;
if ($addon->getPlugin('content')->isAvailable()) {
    $templateSelect = new rex_template_select($categoryId, $clang);
}

if ($structureContext->getCategoryId() > 0 || (0 == $structureContext->getCategoryId() && !rex::getUser()->getComplexPerm('structure')->hasMountpoints())) {
    $tmplHead = '';
    if ($templateSelect) {
        $templateSelect->setName('template_id');
        $templateSelect->setSize(1);
        $templateSelect->setStyle('class="form-control selectpicker"');

        $tEMPLATENAME = $templateSelect->getTemplates();
        $tEMPLATENAME[0] = rex_i18n::msg('template_default_name');

        $tmplHead = '<th class="rex-table-template">' . rex_i18n::msg('header_template') . '</th>';
    }

    // --------------------- ARTIKEL LIST
    $artAddLink = '';
    if (rex::getUser()->hasPerm('addArticle[]') && $structureContext->hasCategoryPermission()) {
        $artAddLink = '<a class="rex-link-expanded" href="' . $structureContext->getContext()->getUrl(['function' => 'add_art', 'artstart' => $structureContext->getArtStart()]) . '"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2') . '><i class="rex-icon rex-icon-add-article"></i></a>';
    }

    // ---------- COUNT DATA
    $sql = rex_sql::factory();
    // $sql->setDebug();
    $sql->setQuery('
        SELECT COUNT(*) as artCount
        FROM ' . rex::getTablePrefix() . 'article
        WHERE
            ((parent_id = :category_id AND startarticle=0) OR (id = :category_id AND startarticle=1))
            AND clang_id = :clang_id
        ORDER BY priority, name
    ', [
        'category_id' => $structureContext->getCategoryId(),
        'clang_id' => $structureContext->getClangId(),
    ]);

    // --------------------- ADD PAGINATION

    $artPager = new rex_pager($structureContext->getRowsPerPage(), 'artstart');
    $artPager->setRowCount($sql->getValue('artCount'));
    $artFragment = new rex_fragment();
    $artFragment->setVar('urlprovider', $structureContext->getContext());
    $artFragment->setVar('pager', $artPager);
    echo $artFragment->parse('core/navigations/pagination.php');

    // ---------- READ DATA
    $sql->setQuery('
        SELECT *
        FROM ' . rex::getTablePrefix() . 'article
        WHERE
            ((parent_id = :category_id AND startarticle=0) OR (id = :category_id AND startarticle=1))
            AND clang_id = :clang_id
        ORDER BY
            priority, name
        LIMIT ' . $artPager->getCursor() . ',' . $artPager->getRowsPerPage(),
        [
            'category_id' => $structureContext->getCategoryId(),
            'clang_id' => $structureContext->getClangId(),
        ]
    );

    // ---------- INLINE THE EDIT/ADD FORM
    if ('add_art' == $structureContext->getFunction() || 'edit_art' == $structureContext->getFunction()) {
        $echo .= '
        <form action="' . $structureContext->getContext()->getUrl(['artstart' => $structureContext->getArtStart()]) . '" method="post">
            <fieldset>';
    }

    // ----------- PRINT OUT THE ARTICLES

    $echo .= '
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon">' . $artAddLink . '</th>
                        <th class="rex-table-id">' . rex_i18n::msg('header_id') . '</th>
                        <th class="rex-table-article-name">' . rex_i18n::msg('header_article_name') . '</th>
                        ' . $tmplHead . '
                        <th class="rex-table-date">' . rex_i18n::msg('header_date') . '</th>
                        <th class="rex-table-priority">' . rex_i18n::msg('header_priority') . '</th>
                        <th class="rex-table-action" colspan="3">' . rex_i18n::msg('header_status') . '</th>
                    </tr>
                </thead>
                <tbody>
                ';

    $canEdit = rex::getUser()->hasPerm('editArticle[]');
    $canDelete = rex::getUser()->hasPerm('deleteArticle[]');
    $colspan = (int) $canEdit + (int) $canDelete + 1;

    // --------------------- ARTIKEL ADD FORM
    if ('add_art' == $structureContext->getFunction() && rex::getUser()->hasPerm('addArticle[]') && $structureContext->hasCategoryPermission()) {
        $tmplTd = '';
        if ($templateSelect) {
            $templateSelect->setSelectedFromStartArticle();

            $tmplTd = '<td class="rex-table-template" data-title="' . rex_i18n::msg('header_template') . '">' . $templateSelect->get() . '</td>';
        }

        $echo .= '<tr class="mark">
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-article"></i></td>
                    <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">-</td>
                    <td class="rex-table-article-name" data-title="' . rex_i18n::msg('header_article_name') . '"><input class="form-control" type="text" name="article-name" autofocus /></td>
                    ' . $tmplTd . '
                    <td class="rex-table-date" data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime(time(), 'date') . '</td>
                    <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="article-position" value="' . ($artPager->getRowCount() + 1) . '" /></td>
                    <td class="rex-table-action" colspan="'.$colspan.'">'.rex_api_article_add::getHiddenFields().'<button class="btn btn-save" type="submit" name="artadd_function"' . rex::getAccesskey(rex_i18n::msg('article_add'), 'save') . '>' . rex_i18n::msg('article_add') . '</button></td>
                </tr>
                            ';
    } elseif (0 === $sql->getRows()) {
        $echo .= '<tr>
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            '.('' !== $tmplHead ? '<td></td>' : '').'
            <td></td>
            <td></td>
            <td colspan="'.$colspan.'"></td>
        </tr>';
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
        $dataArtid = 'data-article-id="'.$sql->getValue('id').'"';

        $classStartarticle = '';
        if (1 == $sql->getValue('startarticle')) {
            $classStartarticle = ' rex-startarticle';
        }

        // --------------------- ARTIKEL EDIT FORM

        if ($canEdit && 'edit_art' == $structureContext->getFunction() && $sql->getValue('id') == $structureContext->getArticleId() && $structureContext->hasCategoryPermission()) {
            $tmplTd = '';
            if ($templateSelect) {
                $templateSelect->setSelected($sql->getValue('template_id'));
                $tmplTd = '<td class="rex-table-template" data-title="' . rex_i18n::msg('header_template') . '">' . $templateSelect->get() . '</td>';
            }
            $echo .= '<tr class="mark' . $classStartarticle . '">
                            <td class="rex-table-icon"><a class="rex-link-expanded" href="' . $structureContext->getContext()->getUrl(['page' => 'content/edit', 'article_id' => $sql->getValue('id')]) . '" title="' . rex_escape($sql->getValue('name')) . '"><i class="rex-icon' . $class . '"></i></a></td>
                            <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $sql->getValue('id') . '</td>
                            <td class="rex-table-article-name" data-title="' . rex_i18n::msg('header_article_name') . '"><input class="form-control" type="text" name="article-name" value="' . rex_escape($sql->getValue('name')) . '" autofocus /></td>
                            ' . $tmplTd . '
                            <td class="rex-table-date" data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '"><input class="form-control" type="text" name="article-position" value="' . rex_escape($sql->getValue('priority')) . '" /></td>
                            <td class="rex-table-action" colspan="'.$colspan.'">'.rex_api_article_edit::getHiddenFields().'<button class="btn btn-save" type="submit" name="artedit_function"' . rex::getAccesskey(rex_i18n::msg('article_save'), 'save') . '>' . rex_i18n::msg('article_save') . '</button></td>
                        </tr>';
        } elseif ($structureContext->hasCategoryPermission()) {
            // --------------------- ARTIKEL NORMAL VIEW | EDIT AND ENTER

            $articleStatus = $artStatusTypes[$sql->getValue('status')][0];
            $articleClass = $artStatusTypes[$sql->getValue('status')][1];
            $articleIcon = $artStatusTypes[$sql->getValue('status')][2];

            $addExtra = '';
            if ($canEdit) {
                $addExtra = '<td class="rex-table-action"><a class="rex-link-expanded" href="' . $structureContext->getContext()->getUrl(['article_id' => $sql->getValue('id'), 'function' => 'edit_art', 'artstart' => $structureContext->getArtStart()]) . '"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</a></td>';
            }

            if (1 == $sql->getValue('startarticle')) {
                if ($canDelete) {
                    $addExtra .= '<td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</span></td>';
                }

                $addExtra .= '<td class="rex-table-action"><span class="' . $articleClass . ' text-muted"><i class="rex-icon ' . $articleIcon . '"></i> ' . $articleStatus . '</span></td>';
            } else {
                if ($canDelete) {
                    $addExtra .= '<td class="rex-table-action"><a class="rex-link-expanded" href="' . $structureContext->getContext()->getUrl(['article_id' => $sql->getValue('id'), 'artstart' => $structureContext->getArtStart()] + rex_api_article_delete::getUrlParams()) . '" data-confirm="' . rex_i18n::msg('delete') . ' ?"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</a></td>';
                }

                $tdLayoutClass = '';
                if ($structureContext->hasCategoryPermission() && rex::getUser()->hasPerm('publishArticle[]')) {
                    $tdLayoutClass = 'rex-table-action-no-dropdown';

                    if (count($artStatusTypes) > 2) {
                        $tdLayoutClass = 'rex-table-action-dropdown';
                        $articleStatus = '<div class="dropdown"><a href="#" class="dropdown-toggle '. $articleClass .'" type="button" data-toggle="dropdown"><i class="rex-icon ' . $articleIcon . '"></i>&nbsp;'.$articleStatus.'&nbsp;<span class="caret"></span></a><ul class="dropdown-menu dropdown-menu-right">';
                        foreach ($artStatusTypes as $artStatusKey => $artStatusType) {
                            $articleStatus .= '<li><a  class="' . $artStatusType[1] . '" href="' . $structureContext->getContext()->getUrl(['article_id' => $sql->getValue('id'), 'artstart' => $structureContext->getArtStart(), 'art_status' => $artStatusKey] + rex_api_article_status::getUrlParams()) . '">' . $artStatusType[0] . '</a></li>';
                        }
                        $articleStatus .= '</ul></div>';
                    } else {
                        $articleStatus = '<a class="' . $articleClass . '" href="' . $structureContext->getContext()->getUrl(['article_id' => $sql->getValue('id'), 'artstart' => $structureContext->getArtStart()] + rex_api_article_status::getUrlParams()) . '"><i class="rex-icon ' . $articleIcon . '"></i>&nbsp;' . $articleStatus . '</a>';
                    }
                } else {
                    $articleStatus = '<span class="' . $articleClass . ' text-muted"><i class="rex-icon ' . $articleIcon . '"></i> ' . $articleStatus . '</span>';
                }

                $addExtra .= '<td class="rex-table-action '.$tdLayoutClass.'">' . $articleStatus . '</td>';
            }

            $editModeUrl = $structureContext->getContext()->getUrl(['page' => 'content/edit', 'article_id' => $sql->getValue('id'), 'mode' => 'edit']);

            $tmplTd = '';
            if ($templateSelect) {
                $tmpl = isset($tEMPLATENAME[$sql->getValue('template_id')]) ? $tEMPLATENAME[$sql->getValue('template_id')] : '';
                $tmplTd = '<td class="rex-table-template" data-title="' . rex_i18n::msg('header_template') . '">' . $tmpl . '</td>';
            }

            $echo .= '<tr '.$dataArtid.(('' != $classStartarticle) ? ' class="' . trim($classStartarticle) . '"' : '') . '>
                            <td class="rex-table-icon"><a class="rex-link-expanded" href="' . $editModeUrl . '" title="' . rex_escape($sql->getValue('name')) . '"><i class="rex-icon' . $class . '"></i></a></td>
                            <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $sql->getValue('id') . '</td>
                            <td class="rex-table-article-name" data-title="' . rex_i18n::msg('header_article_name') . '"><a class="rex-link-expanded" href="' . $editModeUrl . '">' . rex_escape($sql->getValue('name')) . '</a></td>
                            ' . $tmplTd . '
                            <td class="rex-table-date" data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . rex_escape($sql->getValue('priority')) . '</td>
                            ' . $addExtra . '
                        </tr>
                        ';
        } else {
            // --------------------- ARTIKEL NORMAL VIEW | NO EDIT NO ENTER

            $artStatus = $artStatusTypes[$sql->getValue('status')][0];
            $artStatusClass = $artStatusTypes[$sql->getValue('status')][1];
            $artStatusIcon = $artStatusTypes[$sql->getValue('status')][2];

            $tmplTd = '';
            if ($templateSelect) {
                $tmpl = isset($tEMPLATENAME[$sql->getValue('template_id')]) ? $tEMPLATENAME[$sql->getValue('template_id')] : '';
                $tmplTd = '<td class="rex-table-template" data-title="' . rex_i18n::msg('header_template') . '">' . $tmpl . '</td>';
            }

            $echo .= '<tr '.$dataArtid.'>
                            <td class="rex-table-icon"><i class="rex-icon' . $class . '"></i></td>
                            <td class="rex-table-id" data-title="' . rex_i18n::msg('header_id') . '">' . $sql->getValue('id') . '</td>
                            <td class="rex-table-article-name" data-title="' . rex_i18n::msg('header_article_name') . '">' . rex_escape($sql->getValue('name')) . '</td>
                            ' . $tmplTd . '
                            <td class="rex-table-date" data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">' . rex_escape($sql->getValue('priority')) . '</td>';
            if ($canEdit) {
                $echo .= '
                            <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('change') . '</span></td>';
            }
            if ($canDelete) {
                $echo .= '
                            <td class="rex-table-action"><span class="text-muted"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete') . '</span></td>';
            }
            $echo .= '
                            <td class="rex-table-action"><span class="' . $artStatusClass . ' text-muted"><i class="rex-icon ' . $artStatusIcon . '"></i> ' . $artStatus . '</span></td>
                        </tr>';
        }

        $sql->next();
    }

    $echo .= '</tbody></table>';

    if ('add_art' == $structureContext->getFunction() || 'edit_art' == $structureContext->getFunction()) {
        $echo .= '
        </fieldset>
    </form>';
    }
}

$heading = rex_i18n::msg('structure_articles_caption', $catName);
if (0 == $structureContext->getCategoryId()) {
    $heading = rex_i18n::msg('structure_root_level_articles_caption');
}
$fragment = new rex_fragment();
$fragment->setVar('heading', $heading, false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');
