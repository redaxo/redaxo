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
class rex_api_category2Article extends ApiFunction
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $categoryId = Article::get($articleId)->getCategoryId();
        $user = Core::requireUser();

        // Check permissions: article2category and category2article share the same permission: article2category
        if ($user->hasPerm('article2category[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            if (ArticleHandler::category2article($articleId)) {
                return new ApiResult(true, I18n::msg('content_toarticle_ok'));
            }

            return new ApiResult(false, I18n::msg('content_toarticle_failed'));
        }
        throw new ApiException('User has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
