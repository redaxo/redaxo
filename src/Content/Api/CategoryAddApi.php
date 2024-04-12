<?php

namespace Redaxo\Core\Content\Api;

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class CategoryAddApi extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('addCategory[]')) {
            throw new ApiException('User has no permission to add categories!');
        }

        $parentId = rex_request('parent-category-id', 'int');

        // check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($parentId)) {
            throw new ApiException('user has no permission for this category!');
        }

        // prepare and validate parameters
        $data = [];
        $data['catpriority'] = rex_post('category-position', 'int');
        $data['catname'] = rex_post('category-name', 'string');
        return new ApiResult(true, CategoryHandler::addCategory($parentId, $data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
