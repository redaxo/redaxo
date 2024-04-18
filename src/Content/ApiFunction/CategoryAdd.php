<?php

namespace Redaxo\Core\Content\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Content\CategoryHandler;
use Redaxo\Core\Core;

/**
 * @internal
 */
class CategoryAdd extends ApiFunction
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('addCategory[]')) {
            throw new ApiFunctionException('User has no permission to add categories!');
        }

        $parentId = rex_request('parent-category-id', 'int');

        // check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($parentId)) {
            throw new ApiFunctionException('user has no permission for this category!');
        }

        // prepare and validate parameters
        $data = [];
        $data['catpriority'] = rex_post('category-position', 'int');
        $data['catname'] = rex_post('category-name', 'string');
        return new Result(true, CategoryHandler::addCategory($parentId, $data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}