<?php

use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_api_category_edit extends rex_api_function
{
    public function execute()
    {
        if (!Core::requireUser()->hasPerm('editCategory[]')) {
            throw new rex_api_exception('User has no permission to edit categories!');
        }

        $catId = rex_request('category-id', 'int');
        $clangId = rex_request('clang', 'int');

        $user = Core::requireUser();

        // check permissions
        if (!$user->getComplexPerm('structure')->hasCategoryPerm($catId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        // prepare and validate parameters
        $data = [];
        $data['catpriority'] = rex_post('category-position', 'int');
        $data['catname'] = rex_post('category-name', 'string');
        return new rex_api_result(true, rex_category_service::editCategory($catId, $clangId, $data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
