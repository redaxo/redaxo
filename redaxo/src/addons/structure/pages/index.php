<?php
/**
 * @package redaxo5/structure
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

// --------------------------------------------- API MESSAGES
echo rex_api_function::getMessage();

/**
 * KATEGORIE LISTE
 */
$cat_name = 'Homepage';
$category = rex_category::get($category_id, $clang);
if ($category) {
    $cat_name = $category->getName();
}

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
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang_id=' . $clang . ' ORDER BY catname');
} else {
    $KAT->setQuery('SELECT COUNT(*) as rowCount FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $category_id . ' AND startarticle=1 AND clang_id=' . $clang . ' ORDER BY catpriority');
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
    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id IN (' . $parent_id . ') AND startarticle=1 AND clang_id=' . $clang . ' ORDER BY catname LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
} else {
    $KAT->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=' . $category_id . ' AND startarticle=1 AND clang_id=' . $clang . ' ORDER BY catpriority LIMIT ' . $catPager->getCursor() . ',' . $catPager->getRowsPerPage());
}

$echo = '';

// --------------------- PRINT CATS/SUBCATS

// Header
$structure_category_add = new rex_structure_category_add([
    'edit_id' => $category_id,
    'sql' => $KAT,
    'pager' => $catPager,
    'clang' => $clang,
    'context' => $context,
    'url_params' => ['artstart' => $artstart, 'catstart' => $catstart],
]);
$category_add_button = $structure_category_add->get();

$echo .= '
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="rex-table-icon">'.$category_add_button.'</th>
                <th class="rex-table-id">' . rex_i18n::msg('header_id') . '</th>
                <th>' . rex_i18n::msg('header_category') . '</th>
                <th class="rex-table-priority">' . rex_i18n::msg('header_priority') . '</th>
                <th class="rex-table-action">'.rex_i18n::msg('header_status').'</th>
            </tr>
        </thead>
        <tbody>
';
// Link to parent category
if ($category_id != 0 && ($category = rex_category::get($category_id))) {
    $echo .= '  
        <tr>
            <td class="rex-table-icon"><i class="rex-icon rex-icon-open-category"></i></td>
            <td class="rex-table-id">-</td>
            <td data-title="' . rex_i18n::msg('header_category') . '"><a href="' . $context->getUrl(['category_id' => $category->getParentId()]) . '">..</a></td>
            <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority') . '">&nbsp;</td>
            <td class="rex-table-action">&nbsp;</td>
        </tr>'
    ;
}

// --------------------- KATEGORIE LIST
if ($KAT->getRows() > 0) {
    for ($i = 0; $i < $KAT->getRows(); ++$i) {
        $i_category_id = $KAT->getValue('id');

        $kat_link = $context->getUrl(['category_id' => $i_category_id]);

        // Show a category
        if ($KATPERM || rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($i_category_id)) {
            // These params are passed to the structure actions
            $action_params = [
                'edit_id' => $i_category_id,
                'sql' => $KAT,
                'pager' => $catPager,
                'clang' => $clang,
                'context' => $context,
                'url_params' => ['artstart' => $artstart, 'catstart' => $catstart],
            ];

            $category_actions = [];
            if ($KATPERM) {
                $category_actions = [
                    'category_edit' => new rex_structure_category_edit($action_params),
                    'category_delete' => new rex_structure_category_delete($action_params),
                    'category_status' => new rex_structure_category_status($action_params),
                    'category2article' => new rex_structure_category2Article($action_params),
                    'category_move' => new rex_structure_category_move($action_params),
                ];

                // EXTENSION POINT to manipulate the action array
                $category_actions = rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_CATEGORY_ACTIONS', $category_actions, $action_params));
            }

            $echo .= '
                <tr class="rex-structure rex-structure-category">
                    <td class="rex-table-icon"><a href="'.$kat_link.'" title="'.htmlspecialchars($KAT->getValue('catname')).'"><i class="rex-icon rex-icon-category"></i></a></td>
                    <td class="rex-table-id" data-title="'.rex_i18n::msg('header_id') . '">'.$i_category_id.'</td>
                    <td data-title="'.rex_i18n::msg('header_category').'"><a href="'.$kat_link.'">'.htmlspecialchars($KAT->getValue('catname')).'</a></td>
                    <td class="rex-table-priority" data-title="'.rex_i18n::msg('header_priority').'">'.htmlspecialchars($KAT->getValue('catpriority')).'</td>
            ';

            // Add category actions
            // Each action must be an decendant of rex_fragment and implement the method get() to return an action trigger
            // which is collected in this loop
            $echo .= '
                <td class="rex-table-action">
                    <div class="btn-group">
            ';
            foreach ($category_actions as $category_action) {
                if ($category_action instanceof rex_fragment && method_exists($category_action, 'get')) {
                    $echo .= $category_action->get().PHP_EOL;
                }
            }
            $echo .= '
                    </div>
                </td>
            ';

            $echo .= '</tr>';
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
        </tr>
    ';
}

$echo .= '
        </tbody>
    </table>
';

$fragment = new rex_fragment();
$fragment->setVar('heading', rex_i18n::msg('structure_categories_caption', $cat_name), false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');

/**
 * ARTIKEL LISTE
 */
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

    // ----------- PRINT OUT THE ARTICLES
    $structure_article_add = new rex_structure_article_add([
        'edit_id' => $category_id,
        'sql' => $sql,
        'pager' => $artPager,
        'clang' => $clang,
        'context' => $context,
        'url_params' => ['artstart' => $artstart, 'catstart' => $catstart],
    ]);
    $article_add_button = $structure_article_add->get();

    $echo .= '
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="rex-table-icon">'.$article_add_button.'</th>
                    <th class="rex-table-id">' . rex_i18n::msg('header_id') . '</th>
                    <th>' . rex_i18n::msg('header_article_name') . '</th>
                    ' . $tmpl_head . '
                    <th>' . rex_i18n::msg('header_date') . '</th>
                    <th class="rex-table-priority">' . rex_i18n::msg('header_priority') . '</th>
                    <th class="rex-table-action">' . rex_i18n::msg('header_status') . '</th>
                </tr>
            </thead>
    ';

    // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
    if ($sql->getRows() > 0/* || $function == 'add_art'*/) {
        $echo .= '<tbody>';
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

        // --------------------- ARTIKEL NORMAL VIEW | EDIT AND ENTER
        $article_icon = '<i class="rex-icon'.$class.'"></i>';
        $article_title = htmlspecialchars($sql->getValue('name'));

        if ($KATPERM) {
            $edit_url = $context->getUrl([
                'page' => 'content/edit',
                'article_id' => $sql->getValue('id'),
                'mode' => 'edit'
            ]);

            $article_icon = '<a href="'.$edit_url.'" title="'.$article_title.'">'.$article_icon.'</a>';
            $article_title = '<a href="'.$edit_url.'">'.$article_title.'</a>';
        }

        $tmpl_td = '';
        if ($withTemplates) {
            $tmpl = isset($TEMPLATE_NAME[$sql->getValue('template_id')]) ? $TEMPLATE_NAME[$sql->getValue('template_id')] : '';
            $tmpl_td = '<td data-title="' . rex_i18n::msg('header_template') . '">' . $tmpl . '</td>';
        }

        // These params are passed to the structure functions
        $action_params = [
            'edit_id' => $sql->getValue('id'),
            'sql' => $sql,
            'pager' => $artPager,
            'clang' => $clang,
            'context' => $context,
            'url_params' => ['artstart' => $artstart, 'catstart' => $catstart],
        ];

        // Get article actions
        $article_actions = [
            'article_edit' => new rex_structure_article_edit($action_params),
            'article_delete' => new rex_structure_article_delete($action_params),
            'article_status' => new rex_structure_article_status($action_params),
            'article2category' => new rex_structure_article2category($action_params),
            'article2startarticle' => new rex_structure_article2Startarticle($action_params),
            'article_move' => new rex_structure_article_move($action_params),
            'article_copy' => new rex_structure_article_copy($action_params),
        ];

        // EXTENSION POINT to manipulate the action array
        $article_actions = rex_extension::registerPoint(new rex_extension_point('PAGE_STRUCTURE_ARTICLE_ACTIONS', $article_actions, $action_params));

        $echo .= '
            <tr class="rex-structure-article'.(($class_startarticle != '') ? ' '.trim($class_startarticle) : '').'">
                <td class="rex-table-icon">'.$article_icon.'</td>
                <td class="rex-table-id" data-title="'.rex_i18n::msg('header_id').'">'.$sql->getValue('id').'</td>
                <td data-title="'.rex_i18n::msg('header_article_name').'">'.$article_title.'</td>
                '.$tmpl_td.'
                <td data-title="' . rex_i18n::msg('header_date') . '">' . rex_formatter::strftime($sql->getDateTimeValue('createdate'), 'date') . '</td>
                <td class="rex-table-priority" data-title="' . rex_i18n::msg('header_priority').'">'.htmlspecialchars($sql->getValue('priority')).'</td>
        ';

        // Add category actions
        // Each action must be an decendant of rex_fragment and implement the method get() to return an action trigger
        // which is collected in this loop
        $echo .= '
            <td class="rex-table-action">
                <div class="btn-group">
        ';
        foreach ($article_actions as $article_action) {
            if ($article_action instanceof rex_fragment && method_exists($article_action, 'get')) {
                $echo .= $article_action->get().PHP_EOL;
            }
        }
        $echo .= '
                </div>
            </td>
        ';

        $echo .= '</tr>';

        $sql->next();
    }

    // tbody nur anzeigen, wenn später auch inhalt drinnen stehen wird
    if ($sql->getRows() > 0) {
        $echo .= '
            </tbody>
        ';
    }

    $echo .= '
        </table>
    ';
}

$fragment = new rex_fragment();
$fragment->setVar('heading', rex_i18n::msg('structure_articles_caption', $cat_name), false);
$fragment->setVar('content', $echo, false);
echo $fragment->parse('core/page/section.php');
