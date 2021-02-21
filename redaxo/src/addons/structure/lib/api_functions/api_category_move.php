<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_category_move extends rex_api_function
{
    public function execute()
    {
        // The category to move
        $articleId = rex_request('article_id', 'int');
        $categoryId = rex_article::get($articleId)->getCategoryId();
        // The destination category in which the given category will be moved
        $categoryIdNew = rex_request('category_id_new', 'int');

        $user = rex::getUser();

        // Check permissions
        if ($user->hasPerm('moveCategory[]') &&
            $user->getComplexPerm('structure')->hasCategoryPerm($categoryId) &&
            $user->getComplexPerm('structure')->hasCategoryPerm($categoryIdNew)
        ) {
            if ($categoryId != $categoryIdNew && rex_category_service::moveCategory($categoryId, $categoryIdNew)) {
                return new rex_api_result(true, rex_i18n::msg('category_moved'));
            }

            return new rex_api_result(false, rex_i18n::msg('content_error_movecategory'));
        }

        throw new rex_api_exception('user has no permission for this category!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
