<?php

use Redaxo\Core\Core;
use Redaxo\Core\Structure\Article;
use Redaxo\Core\Structure\ArticleHandler;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_article2category extends rex_api_function
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $categoryId = Article::get($articleId)->getCategoryId();
        $user = Core::requireUser();

        // Check permissions
        if ($user->hasPerm('article2category[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            if (ArticleHandler::article2category($articleId)) {
                return new rex_api_result(true, I18n::msg('content_tocategory_ok'));
            }

            return new rex_api_result(false, I18n::msg('content_tocategory_failed'));
        }
        throw new rex_api_exception('User has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
