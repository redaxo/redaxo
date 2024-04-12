<?php

use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_article2startarticle extends ApiFunction
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $categoryId = Article::get($articleId)->getCategoryId();
        $user = Core::requireUser();

        // Check permissions
        if ($user->hasPerm('article2startarticle[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            if (ArticleHandler::article2startarticle($articleId)) {
                return new rex_api_result(true, I18n::msg('content_tostartarticle_ok'));
            }

            return new rex_api_result(false, I18n::msg('content_tostartarticle_failed'));
        }

        throw new rex_api_exception('user has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
