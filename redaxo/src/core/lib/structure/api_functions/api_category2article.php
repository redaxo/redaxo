<?php

use Redaxo\Core\Core;
use Redaxo\Core\Structure\Article;
use Redaxo\Core\Structure\ArticleHandler;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_category2Article extends rex_api_function
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $categoryId = Article::get($articleId)->getCategoryId();
        $user = Core::requireUser();

        // Check permissions: article2category and category2article share the same permission: article2category
        if ($user->hasPerm('article2category[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            if (ArticleHandler::category2article($articleId)) {
                return new rex_api_result(true, I18n::msg('content_toarticle_ok'));
            }

            return new rex_api_result(false, I18n::msg('content_toarticle_failed'));
        }
        throw new rex_api_exception('User has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
