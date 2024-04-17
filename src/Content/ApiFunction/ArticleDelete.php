<?php

namespace Redaxo\Core\Content\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\ApiFunctionResult;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class ArticleDelete extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('deleteArticle[]')) {
            throw new ApiFunctionException('User has no permission to delete articles!');
        }

        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');

        // Check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new ApiFunctionException('user has no permission for this category!');
        }
        return new ApiFunctionResult(true, ArticleHandler::deleteArticle($articleId));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
