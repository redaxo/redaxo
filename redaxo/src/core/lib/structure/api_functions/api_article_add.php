<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_article_add extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('addArticle[]')) {
            throw new ApiException('User has no permission to add articles!');
        }

        $categoryId = rex_request('category_id', 'int');

        // check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new ApiException('user has no permission for this category!');
        }

        $data = [];
        $data['name'] = rex_post('article-name', 'string');
        $data['priority'] = rex_post('article-position', 'int');
        $data['template_id'] = rex_post('template_id', 'int');
        $data['category_id'] = $categoryId;
        return new ApiResult(true, ArticleHandler::addArticle($data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
