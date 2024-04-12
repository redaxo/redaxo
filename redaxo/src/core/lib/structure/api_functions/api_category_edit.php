<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_category_edit extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('editCategory[]')) {
            throw new ApiException('User has no permission to edit categories!');
        }

        $catId = rex_request('category-id', 'int');
        $clangId = rex_request('clang', 'int');

        $user = Core::requireUser();

        // check permissions
        if (!$user->getComplexPerm('structure')->hasCategoryPerm($catId)) {
            throw new ApiException('user has no permission for this category!');
        }

        // prepare and validate parameters
        $data = [];
        $data['catpriority'] = rex_post('category-position', 'int');
        $data['catname'] = rex_post('category-name', 'string');
        return new ApiResult(true, CategoryHandler::editCategory($catId, $clangId, $data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
