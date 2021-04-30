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

        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');

        // Check permissions
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        $result = new rex_api_result(true, rex_article_service::deleteArticle($articleId));
        return $result;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
