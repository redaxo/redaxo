<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_article_delete extends rex_api_function
{
    public function execute()
    {
        if (!rex::getUser()->hasPerm('deleteArticle[]')) {
            throw new rex_api_exception('User has no permission to delete articles!');
        }

        $category_id = rex_request('category_id', 'int');
        $article_id = rex_request('article_id', 'int');

        // Check permissions
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        $result = new rex_api_result(true, rex_article_service::deleteArticle($article_id));
        return $result;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
