<?php

use Redaxo\Core\Api\ApiFunction;
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
            throw new rex_api_exception('User has no permission to delete categories!');
        }

        $catId = rex_request('category-id', 'int');

        // check permissions
        if (!Core::requireUser()->getComplexPerm('structure')->hasCategoryPerm($catId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        return new rex_api_result(true, CategoryHandler::deleteCategory($catId));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
