<?php

/**
 * @package redaxo\structure
 */
class rex_api_article_add extends rex_api_function
{
    public function execute()
    {
        $category_id = rex_request('category_id', 'int');

        // check permissions
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        $data = [];
        $data['name']        = rex_post('article-name', 'string');
        $data['priority']    = rex_post('article-position', 'int');
        $data['template_id'] = rex_post('template_id', 'int');
        $data['category_id'] = $category_id;

        $result = new rex_api_result(true, rex_article_service::addArticle($data));
        return $result;
    }
}

/**
 * @package redaxo\structure
 */
class rex_api_article_edit extends rex_api_function
{
    public function execute()
    {
        $category_id = rex_request('category_id', 'int');
        $article_id  = rex_request('article_id',  'int');
        $clang       = rex_request('clang',       'int');

        // check permissions
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        // --------------------- ARTIKEL EDIT
        $data = [];
        $data['priority']    = rex_post('article-position', 'int');
        $data['name']        = rex_post('article-name', 'string');
        $data['template_id'] = rex_post('template_id', 'int');

        $result = new rex_api_result(true, rex_article_service::editArticle($article_id, $clang, $data));
        return $result;
    }
}

/**
 * @package redaxo\structure
 */
class rex_api_article_delete extends rex_api_function
{
    public function execute()
    {
        $category_id = rex_request('category_id', 'int');
        $article_id  = rex_request('article_id',  'int');

        // check permissions
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        $result = new rex_api_result(true, rex_article_service::deleteArticle($article_id));
        return $result;
    }
}

/**
 * @package redaxo\structure
 */
class rex_api_article_status extends rex_api_function
{
    public function execute()
    {
        $catId       = rex_request('category-id', 'int');
        $article_id  = rex_request('article_id',  'int');
        $clang       = rex_request('clang',       'int');

        /**
         * @var rex_user
         */
        $user = rex::getUser();

        // check permissions
        if ($user->getComplexPerm('structure')->hasCategoryPerm($catId) && $user->hasPerm('publishArticle[]')) {
            $newStatus = rex_article_service::articleStatus($article_id, $clang);
            $oldStatus = rex_article_service::prevStatus($newStatus);
            $statusTypes = rex_article_service::statusTypes();

            $result = new rex_api_result(true, rex_i18n::msg('article_status_updated'));
            return $result;
        } else {
            throw new rex_api_exception('user has no permission for this article!');
        }
    }
}

/**
 * @package redaxo\structure
 */
class rex_api_article2category extends rex_api_function
{
    public function execute()
    {
        $article_id  = rex_request('article_id',  'int');

        $ooArticle = rex_article::getArticleById($article_id);
        $category_id = $ooArticle->getCategoryId();

        /**
         * @var rex_user
         */
        $user = rex::getUser();

        // check permissions
        if ($user->hasPerm('article2category[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            if (rex_article_service::article2category($article_id)) {
                $result = new rex_api_result(true, rex_i18n::msg('content_tocategory_ok'));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_tocategory_failed'));
            }

            return $result;
        } else {
            throw new rex_api_exception('user has no permission for this article!');
        }
    }
}

/**
 * @package redaxo\structure
 */
class rex_api_category2article extends rex_api_function
{
    public function execute()
    {
        $article_id  = rex_request('article_id',  'int');

        $ooArticle = rex_article::getArticleById($article_id);
        $category_id = $ooArticle->getCategoryId();

        /**
         * @var rex_user
         */
        $user = rex::getUser();

        // article2category und category2article verwenden das gleiche Recht: article2category
        if ($user->hasPerm('article2category[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            if (rex_article_service::category2article($article_id)) {
                $result = new rex_api_result(true, rex_i18n::msg('content_toarticle_ok'));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_toarticle_failed'));
            }

            return $result;
        } else {
            throw new rex_api_exception('user has no permission for this article!');
        }
    }
}

/**
 * @package redaxo\structure
 */
class rex_api_article2startarticle extends rex_api_function
{
    public function execute()
    {
        $article_id  = rex_request('article_id',  'int');

        $ooArticle = rex_article::getArticleById($article_id);
        $category_id = $ooArticle->getCategoryId();

        /**
         * @var rex_user
         */
        $user = rex::getUser();

        // article2category und category2article verwenden das gleiche Recht: article2category
        if ($user->hasPerm('article2startarticle[]') && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            if (rex_article_service::article2startarticle($article_id)) {
                $result = new rex_api_result(true, rex_i18n::msg('content_tostartarticle_ok'));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_tostartarticle_failed'));
            }

            return $result;
        } else {
            throw new rex_api_exception('user has no permission for this article!');
        }
    }
}

/**
 * @package redaxo\structure
 */
// TODO move into separate api class
class rex_api_structure_remote_typeahead extends rex_api_function
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
// TODO move into separate api class 
class rex_api_structure_prefetch_typeahead extends rex_api_function
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
