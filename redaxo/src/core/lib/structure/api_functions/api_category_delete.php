<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_category_delete extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('deleteCategory[]')) {
            throw new ApiException('User has no permission to delete categories!');
        }

        $catId = rex_request('category-id', 'int');

        // check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($catId)) {
            throw new ApiException('user has no permission for this category!');
        }

        return new ApiResult(true, CategoryHandler::deleteCategory($catId));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
