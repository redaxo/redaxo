<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_article2category extends ApiFunction
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $categoryId = Article::get($articleId)->getCategoryId();
        $user = Core::requireUser();

        // Check permissions
        if ($user->hasPerm('article2category[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            if (ArticleHandler::article2category($articleId)) {
                return new ApiResult(true, I18n::msg('content_tocategory_ok'));
            }

            return new ApiResult(false, I18n::msg('content_tocategory_failed'));
        }
        throw new ApiException('User has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
