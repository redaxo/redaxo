<?php

namespace Redaxo\Core\Content\Api;

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class ArticleDeleteApi extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('deleteArticle[]')) {
            throw new ApiException('User has no permission to delete articles!');
        }

        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');

        // Check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new ApiException('user has no permission for this category!');
        }
        return new ApiResult(true, ArticleHandler::deleteArticle($articleId));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
