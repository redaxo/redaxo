<?php

use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_article_delete extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('deleteArticle[]')) {
            throw new rex_api_exception('User has no permission to delete articles!');
        }

        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');

        // Check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }
        return new rex_api_result(true, ArticleHandler::deleteArticle($articleId));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
