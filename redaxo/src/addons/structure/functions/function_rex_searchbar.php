<?php

/**
 * Backend Search Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\structure
 */

/**
 * @param rex_context $context
 * @return string
 * @package redaxo\structure
 */
function rex_structure_searchbar(rex_context $context)
{
    $message = '';
    $search_result = '';

    // ------------ Parameter
    $clang       = $context->getParam('clang', 1);
    $category_id = $context->getParam('category_id', 0);
    $article_id  = $context->getParam('article_id', 0);
    $search_article_name = rex_request('search_article_name', 'string');

    // ------------ Suche via ArtikelId
    if (preg_match('/^[0-9]+$/', $search_article_name, $matches)) {
        $OOArt = rex_article::getArticleById($matches[0], $clang);
        if ($OOArt instanceof rex_article) {
            rex_response::sendRedirect(htmlspecialchars_decode($context->getUrl(['page' => 'content', 'article_id' => $OOArt->getId()])));
        }
    }

    // Auswahl eines normalen Artikels => category holen
    if ($article_id != 0) {
        $OOArt = rex_article::getArticleById($article_id, $clang);
        // Falls Artikel gerade geloescht wird, gibts keinen rex_article
        if ($OOArt) {
            $category_id = $OOArt->getCategoryId();
        }
    }

    // ------------ Suche via ArtikelName
    if (rex_get('search_start', 'bool')) {
        // replace LIKE wildcards
        $search_article_name_like = str_replace(['_', '%'], ['\_', '\%'], $search_article_name);

        $qry = '
        SELECT id
        FROM ' . rex::getTablePrefix() . 'article
        WHERE
            clang = ' . $clang . ' AND
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
            $OOArt = rex_article::getArticleById($search->getValue('id'), $clang);
            if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($OOArt->getCategoryId())) {
                rex_response::sendRedirect(htmlspecialchars_decode($context->getUrl(['page' => 'content', 'article_id' => $search->getValue('id')])));
            }
        }
        // Mehrere Suchtreffer, Liste anzeigen
        elseif ($foundRows > 0) {
            $needle = htmlspecialchars($search_article_name);
            $search_result .= '<ul class="be_search-search-result">';
            for ($i = 0; $i < $foundRows; $i++) {
                $OOArt = rex_article::getArticleById($search->getValue('id'), $clang);
                $label = $OOArt->getName();

                if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($OOArt->getCategoryId())) {
                    if (rex::getUser()->hasPerm('advancedMode[]')) {
                        $label .= ' [' . $search->getValue('id') . ']';
                    }

                    $highlightHit = function ($string, $needle) {
                        return preg_replace(
                            '/(.*)(' . preg_quote($needle, '/') . ')(.*)/i',
                            '\\1<span class="be_search-search-hit">\\2</span>\\3',
                            $string
                        );
                    };

                    $s = '';
                    $first = true;
                    foreach ($OOArt->getParentTree() as $treeItem) {
                        $treeLabel = $treeItem->getName();

                        if (rex::getUser()->hasPerm('advancedMode[]')) {
                            $treeLabel .= ' [' . $treeItem->getId() . ']';
                        }

                        $prefix = ': ';
                        if ($first) {
                            $prefix = '';
                            $first = false;
                        }

                        $treeLabel = htmlspecialchars($treeLabel);
                        $treeLabel = $highlightHit($treeLabel, $needle);

                        $s .= '<li>' . $prefix . '<a href="' . $context->getUrl(['page' => 'structure', 'category_id' => $treeItem->getId()]) . '">' . $treeLabel . ' </a></li>';
                    }

                    $prefix = ': ';
                    if ($first) {
                        $prefix = '';
                        $first = false;
                    }

                    $label = htmlspecialchars($label);
                    $label = $highlightHit($label, $needle);

                    $s .= '<li>' . $prefix . '<a href="' . $context->getUrl(['page' => 'content', 'article_id' => $treeItem->getId()]) . '">' . $label . ' </a></li>';

                    $search_result .= '<li><ul class="be_search-search-hit">' . $s . '</ul></li>';
                }
                $search->next();
            }
            $search_result .= '</ul>';
        } else {
            $message = rex_view::warning(rex_i18n::msg('be_search_no_results'));
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
    $category_select->setId('rex-id-search-category-id');
    $category_select->setSize('1');
    $category_select->setAttribute('onchange', 'this.form.submit();');
    $category_select->setSelected($category_id);

    $select = $category_select->get();


    $droplist = '';
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="UTF-8">' . $select);

    $options = $doc->getElementsByTagName('option');

    $js_button = 'Home';
    foreach ($options as $option) {
        $value = '';
        $class = '';
        if ($option->hasAttributes()) {
            foreach ($option->attributes as $attribute) {
                if ($attribute->name == 'value') {
                    $value = $attribute->value;

                    if ($attribute->value == $category_id) {
                        $js_button = $option->nodeValue;
                        $class = ' rex-drop-active';
                    }
                }
            }
        }
        $droplist .= '<li class="rex-drop-item' . $class . '"><a href="index.php?page=structure&amp;category_id=' . $value . '"><span class="rex-icon rex-icon-check"></span><div class="rex-drop-item-text rex-js-button-text">' . $option->nodeValue . '</div></a></li>';

    }

    $droplist = '
                <div class="rex-js-drop rex-dropdown">
                    <span class="rex-button rex-drop-button rex-js-drop-button">
                        <i>Schnellnavigation</i>
                        <span class="rex-js-button">' . $js_button . '</span>
                        <span class="rex-drop"></span>
                    </span>
                    <div class="rex-drop-container">
                        <ul class="rex-drop-list rex-nowrap">' . $droplist . '</ul>
                    </div>
                </div>';



    $form =
    '<div class="rex-form">
        <form action="' . rex_url::backendController() . '" method="get">
            <fieldset>';

    $form .= $context->getHiddenInputFields();
    $form .= '<input type="text" name="search_article_name" id="rex-id-search-article-name" value="' . htmlspecialchars($search_article_name) . '" placeholder="' . htmlspecialchars(rex_i18n::msg('be_search_article_name') . '/' . rex_i18n::msg('be_search_article_id')) . '" />
              <button class="rex-button" type="submit" name="search_start" value="1">' . rex_i18n::msg('be_search_start') . '</button>
            </fieldset>
        </form>
    </div>';


    $fragment = new rex_fragment();
    $fragment->setVar('left', $form, false);
    $fragment->setVar('right', $droplist, false);
    $navi = $fragment->parse('core/navigations/content.php');

    return $navi . $search_result;
}
