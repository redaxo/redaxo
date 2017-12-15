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
        $article_id = rex_request('article_id', 'int');
        $category_id = rex_article::get($article_id)->getCategoryId();
        // The destination category in which the given category will be moved
        $category_id_new = rex_request('category_id_new', 'int');

        $user = rex::getUser();

        // Check permissions
        if ($user->hasPerm('moveCategory[]') &&
            $user->getComplexPerm('structure')->hasCategoryPerm($category_id) &&
            $user->getComplexPerm('structure')->hasCategoryPerm($category_id_new)
        ) {
            if ($category_id != $category_id_new && rex_category_service::moveCategory($category_id, $category_id_new)) {
                $result = new rex_api_result(true, rex_i18n::msg('category_moved'));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_error_movecategory'));
            }

            return $result;
        }

        throw new rex_api_exception('user has no permission for this category!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
