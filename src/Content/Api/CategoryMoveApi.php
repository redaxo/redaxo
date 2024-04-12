<?php

namespace Redaxo\Core\Content\Api;

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class CategoryMoveApi extends ApiFunction
{
    public function execute()
    {
        // The category to move
        $articleId = rex_request('article_id', 'int');
        $categoryId = Article::get($articleId)->getCategoryId();
        // The destination category in which the given category will be moved
        $categoryIdNew = rex_request('category_id_new', 'int');

        $user = Core::requireUser();

        // Check permissions
        if (
            $user->hasPerm('moveCategory[]')
            && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)
            && $user->getComplexPerm('structure')->hasCategoryPerm($categoryIdNew)
        ) {
            if ($categoryId != $categoryIdNew && CategoryHandler::moveCategory($categoryId, $categoryIdNew)) {
                return new ApiResult(true, I18n::msg('category_moved'));
            }

            return new ApiResult(false, I18n::msg('content_error_movecategory'));
        }

        throw new ApiException('user has no permission for this category!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
