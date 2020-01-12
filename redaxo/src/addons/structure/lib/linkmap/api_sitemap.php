<?php

/**
 * @package redaxo\structure
 *
 * @internal
 *
 * XXX NOT USED ATM
 */
class rex_api_sitemap_tree extends rex_api_function
{
    public function execute()
    {
        // check if a new category was folded
        $category_id = rex_request('toggle_category_id', 'int', -1);
        $category_id = rex_category::get($category_id) ? $category_id : -1;

        /**
         * @var rex_user
         */
        $user = rex::getUser();

        if (!$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        $context = rex_context::fromGet();
        $categoryTree = new rex_sitemap_category_tree($context);
        $tree = $categoryTree->getTree($category_id);

        $result = new rex_api_result(true);
        return $result;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
