<?php

namespace Redaxo\Core\Content\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class ArticleEdit extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('editArticle[]')) {
            throw new ApiFunctionException('User has no permission to edit articles!');
        }

        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        // check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new ApiFunctionException('user has no permission for this category!');
        }

        // --------------------- ARTIKEL EDIT
        $data = [];
        $data['priority'] = rex_post('article-position', 'int');
        $data['name'] = rex_post('article-name', 'string');
        $data['template_id'] = rex_post('template_id', 'int');
        return new Result(true, ArticleHandler::editArticle($articleId, $clang, $data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
