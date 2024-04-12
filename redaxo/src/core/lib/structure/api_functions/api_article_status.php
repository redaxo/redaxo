<?php

use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_article_status extends ApiFunction
{
    public function execute()
    {
        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');
        $status = rex_request('art_status', 'int', null);
        $user = Core::requireUser();

        // check permissions
        if ($user->getComplexPerm('structure')->hasCategoryPerm($categoryId) && $user->hasPerm('publishArticle[]')) {
            ArticleHandler::articleStatus($articleId, $clang, $status);

            return new rex_api_result(true, I18n::msg('article_status_updated'));
        }

        throw new rex_api_exception('user has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
