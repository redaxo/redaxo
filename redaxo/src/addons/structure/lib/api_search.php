<?php

/**
 * @package redaxo\structure
 */
class rex_api_structure_search_fulltext_typeahead extends rex_api_function
{
	public function execute()
	{
		/**
		 * @var rex_article[]
		 */
		/*
		$results = [];
		
    // ------------ Parameter
    $clang = 1;
    $category_id = 0;
		$search_article_name = rex_request('search_article_name', 'string');

    // ------------ Suche via ArtikelId
    if (preg_match('/^[0-9]+$/', $search_article_name, $matches)) {
        $OOArt = rex_article::getArticleById($matches[0], $clang);
        if ($OOArt instanceof rex_article) {
        	  $results[] = $OOArt;
        }
    }
    
    if (empty($results)) {
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
            if ($category_id != 0)
                $qry .= ' AND path LIKE "%|' . $category_id . '|%"';
        }

        $search = rex_sql::factory();
        $search->setQuery($qry);
        $foundRows = $search->getRows();

        // Suche ergab nur einen Treffer => Direkt auf den Treffer weiterleiten
        if ($foundRows == 1) {
            $OOArt = rex_article::getArticleById($search->getValue('id'));
            if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($OOArt->getCategoryId())) {
        	  	  $results[] = $OOArt;
            }
        }
        // Mehrere Suchtreffer, Liste anzeigen
        elseif ($foundRows > 0)
        {
            for ($i = 0; $i < $foundRows; $i++) {
                $OOArt = rex_article::getArticleById($search->getValue('id'));

                if (rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($OOArt->getCategoryId())) {
                    if (rex::getUser()->hasPerm('advancedMode[]'))
                        $label .= ' [' . $search->getValue('id') . ']';
                    
                    $results[] = $OOArt;

                    $s = '';
                    $first = true;
                    foreach ($OOArt->getParentTree() as $treeItem) {
                        $treeLabel = $treeItem->getName();

                        if (rex::getUser()->hasPerm('advancedMode[]'))
                            $treeLabel .= ' [' . $treeItem->getId() . ']';

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
        }
    }
    */
    
		$results = [];

		for ($i = 0; $i < 10; $i++) {
			$entry = new stdClass();
			$entry->value = 'hallo'. $i;
			$entry->tokens = ['ha'. $i, 'lo'. $i, 'xy'. $i];
			$results []= $entry;
		}
		
		$result = new rex_api_result(true);
		$result->entries = $results;
		
		return $result;
  }
}

/**
 * @package redaxo\structure
 */
class rex_api_structure_search_fulltext extends rex_api_function
{
	public function execute()
	{
		$results = [];
		
		$entry = new stdClass();
		$entry->value = 'hallo';
		$entry->tokens = ['ha', 'lo', 'xy'];
		$results []= $entry;
		
		$result = new rex_api_result(true);
		$result->entries = $results;
		
		return $result;
	}
}