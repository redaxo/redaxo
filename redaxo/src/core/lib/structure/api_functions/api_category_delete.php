<?php

use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_category_delete extends rex_api_function
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

        return new rex_api_result(true, rex_category_service::deleteCategory($catId));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
