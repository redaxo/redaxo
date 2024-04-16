<?php

namespace Redaxo\Core\Content\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\ApiFunctionException;
use Redaxo\Core\ApiFunction\ApiFunctionResult;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class ArticleToCategory extends ApiFunction
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $categoryId = Article::get($articleId)->getCategoryId();
        $user = Core::requireUser();

        // Check permissions
        if ($user->hasPerm('article2category[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            if (ArticleHandler::article2category($articleId)) {
                return new ApiFunctionResult(true, I18n::msg('content_tocategory_ok'));
            }

            return new ApiFunctionResult(false, I18n::msg('content_tocategory_failed'));
        }
        throw new ApiFunctionException('User has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
