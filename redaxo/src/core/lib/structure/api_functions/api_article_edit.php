<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_article_edit extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('editArticle[]')) {
            throw new ApiException('User has no permission to edit articles!');
        }

        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        // check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new ApiException('user has no permission for this category!');
        }

        // --------------------- ARTIKEL EDIT
        $data = [];
        $data['priority'] = rex_post('article-position', 'int');
        $data['name'] = rex_post('article-name', 'string');
        $data['template_id'] = rex_post('template_id', 'int');
        return new ApiResult(true, ArticleHandler::editArticle($articleId, $clang, $data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
