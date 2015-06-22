<?php

/**
 * Backend Search Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\structure
 */

/**
 * @param rex_context $context
 *
 * @return string
 *
 * @package redaxo\structure
 */
function rex_structure_searchbar(rex_context $context)
{
    $message = '';
    $search_result = '';

    // ------------ Parameter
    $clang = $context->getParam('clang', 1);
    $category_id = $context->getParam('category_id', 0);
    $article_id = $context->getParam('article_id', 0);
    $search_article_name = rex_request('search_article_name', 'string');

    // ------------ Suche via ArtikelId
    if (preg_match('/^[0-9]+$/', $search_article_name, $matches)) {
        if ($OOArt = rex_article::get($matches[0], $clang)) {
            rex_response::sendRedirect($context->getUrl(['page' => 'content/edit', 'article_id' => $OOArt->getId()], false));
        }
    }

    // Auswahl eines normalen Artikels => category holen
    if ($article_id != 0) {
        $OOArt = rex_article::get($article_id, $clang);
        // Falls Artikel gerade geloescht wird, gibts keinen rex_article
        if ($OOArt) {
            $category_id = $OOArt->getCategoryId();
        }
    }

    // ------------ Suche via ArtikelName
    if (rex_request('search_start', 'bool')) {
        // replace LIKE wildcards
        $search_article_name_like = str_replace(['_', '%'], ['\_', '\%'], $search_article_name);

        $qry = '
        SELECT id
        FROM ' . rex::getTablePrefix() . 'article
        WHERE
            clang_id = ' . $clang . ' AND
            (
                name LIKE "%' . $search_article_name_like . '%" OR
                catname LIKE "%' . $search_article_name_like . '%"
            )';

        if (rex_addon::get('structure')->getConfig('searchmode', 'local') != 'global') {
            // Suche auf aktuellen Kontext eingrenzen
            if ($category_id != 0) {
                $qry .= ' AND path LIKE "%|' . $category_id . '|%"';
            }
        }

        $search = rex_sql::factory();
//    $search->setDebug();
        $search->setQuery($qry);
        $foundRows = $search->getRows();

        // Suche ergab nur einen Treffer => Direkt auf den Treffer weiterleiten
        if ($foundRows == 1) {
            $OOArt = rex_article::get($search->getValue('id'), $clang);
            if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($OOArt->getCategoryId())) {
                rex_response::sendRedirect($context->getUrl(['page' => 'content/edit', 'article_id' => $search->getValue('id')], false));
            }
        }
        // Mehrere Suchtreffer, Liste anzeigen
        elseif ($foundRows > 0) {
            $needle = htmlspecialchars($search_article_name);
            $search_result .= '<div class="list-group">';
            for ($i = 0; $i < $foundRows; ++$i) {
                $breadcrumb = [];

                $OOArt = rex_article::get($search->getValue('id'), $clang);
                $label = $OOArt->getName();

                if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($OOArt->getCategoryId())) {
                    $label .= ' [' . $search->getValue('id') . ']';

                    $highlightHit = function ($string, $needle) {
                        return preg_replace(
                            '/(.*)(' . preg_quote($needle, '/') . ')(.*)/i',
                            '\\1<mark>\\2</mark>\\3',
                            $string
                        );
                    };

                    foreach ($OOArt->getParentTree() as $treeItem) {
                        $treeLabel = $treeItem->getName();

                        if (rex::getUser()->hasPerm('advancedMode[]')) {
                            $treeLabel .= ' [' . $treeItem->getId() . ']';
                        }

                        $treeLabel = htmlspecialchars($treeLabel);
                        $treeLabel = $highlightHit($treeLabel, $needle);

                        $e = [];
                        $e['title'] = $treeLabel;
                        $e['href'] = $context->getUrl(['page' => 'structure', 'category_id' => $treeItem->getId()]);
                        $breadcrumb[] = $e;
                    }

                    $label = htmlspecialchars($label);
                    $label = $highlightHit($label, $needle);

                    $e = [];
                    $e['title'] = $label;
                    $e['href'] = $context->getUrl(['page' => 'content/edit', 'article_id' => $treeItem->getId()]);
                    $breadcrumb[] = $e;

                    $fragment = new rex_fragment();
                    $fragment->setVar('items', $breadcrumb, false);
                    $search_result .= '<div class="list-group-item">' . $fragment->parse('core/navigations/breadcrumb.php') . '</div>';
                }
                $search->next();
            }
            $search_result .= '</div>';

            $fragment = new rex_fragment();
            $fragment->setVar('title', rex_i18n::msg('be_search_result'), false);
            $fragment->setVar('content', $search_result, false);
            $search_result = $fragment->parse('core/page/section.php');
        } else {
            $message = rex_view::info(rex_i18n::msg('be_search_no_results'));
        }
    }

    $select_name = 'category_id';
    $add_homepage = true;
    if (rex_be_controller::getCurrentPagePart(1) == 'content') {
        $select_name = 'article_id';
        $add_homepage = false;
    }

    $category_select = new rex_category_select(false, false, true, $add_homepage);
    $category_select->setName($select_name);
    $category_select->setSize('1');
    $category_select->setAttribute('onchange', 'this.form.submit();');
    $category_select->setSelected($category_id);

    $select = $category_select->get();

    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="UTF-8">' . $select);

    $options = $doc->getElementsByTagName('option');

    $droplistContext = new rex_context([
        'page' => 'structure',
        'category_id' => 0,
    ]);

    $button_label = '';
    $items = [];
    foreach ($options as $option) {
        $value = '';
        $item = [];
        if ($option->hasAttributes()) {
            foreach ($option->attributes as $attribute) {
                if ($attribute->name == 'value') {
                    $value = $attribute->value;

                    $droplistContext->setParam('category_id', $value);

                    if ($attribute->value == $category_id) {
                        $button_label = str_replace("\xC2\xA0", '', $option->nodeValue);
                        $item['active'] = true;
                    }
                }
            }
        }

        $item['title'] = $option->nodeValue;
        $item['href'] = $droplistContext->getUrl();
        $items[] = $item;
    }

    $fragment = new rex_fragment();
    $fragment->setVar('button_prefix', rex_i18n::msg('be_search_quick_navi'));
    $fragment->setVar('button_label', $button_label);
    $fragment->setVar('items', $items, false);

    $droplist = '<div class="navbar-btn navbar-right">' . $fragment->parse('core/dropdowns/dropdown.php');

    $formElements = [];
    $n = [];
    $n['field'] = '<input class="form-control" type="text" name="search_article_name" value="' . htmlspecialchars($search_article_name) . '" placeholder="' . htmlspecialchars(rex_i18n::msg('be_search_article_name') . '/' . rex_i18n::msg('be_search_article_id')) . '" />';
    $n['right'] = '<button class="btn btn-search" type="submit" name="search_start" value="1">' . rex_i18n::msg('be_search_start') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $toolbar = $fragment->parse('core/form/input_group.php');

    $toolbar = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
    ' . $context->getHiddenInputFields() . '
    <div class="navbar-form navbar-left">
        <div class="form-group">
            ' . $toolbar . '
        </div>
    </div>
    </form>';
    $toolbar = rex_view::toolbar($toolbar . $droplist, rex_i18n::msg('be_search_search'));

    return $toolbar . $search_result;
}
