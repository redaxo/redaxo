<?php

use Redaxo\Core\Core;
use Redaxo\Core\Structure\ArticleHandler;

/**
 * @internal
 */
class rex_api_article_add extends rex_api_function
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('addArticle[]')) {
            throw new rex_api_exception('User has no permission to add articles!');
        }

        $categoryId = rex_request('category_id', 'int');

        // check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        $data = [];
        $data['name'] = rex_post('article-name', 'string');
        $data['priority'] = rex_post('article-position', 'int');
        $data['template_id'] = rex_post('template_id', 'int');
        $data['category_id'] = $categoryId;
        return new rex_api_result(true, ArticleHandler::addArticle($data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
