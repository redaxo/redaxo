<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_category_status extends ApiFunction
{
    public function execute()
    {
        $categoryId = rex_request('category-id', 'int');
        $clang = rex_request('clang', 'int');
        $status = rex_request('cat_status', 'int', null);
        $user = Core::requireUser();

        // Check permissions
        if ($user->getComplexPerm('structure')->hasCategoryPerm($categoryId) && $user->hasPerm('publishCategory[]')) {
            CategoryHandler::categoryStatus($categoryId, $clang, $status);
            return new ApiResult(true, I18n::msg('category_status_updated'));
        }

        throw new ApiException('User has no permission for this category!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
