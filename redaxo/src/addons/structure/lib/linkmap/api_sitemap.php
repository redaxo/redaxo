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
        $categoryId = rex_request('toggle_category_id', 'int', -1);
        $categoryId = rex_category::get($categoryId) ? $categoryId : -1;

        /**
         * @var rex_user
         */
        $user = rex::getUser();

        if (!$user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        $context = rex_context::fromGet();
        $categoryTree = new rex_sitemap_category_tree($context);
        $tree = $categoryTree->getTree($categoryId);
        return new rex_api_result(true);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
