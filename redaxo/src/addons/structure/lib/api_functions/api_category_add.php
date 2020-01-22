<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_category_add extends rex_api_function
{
    public function execute()
    {
        if (!rex::getUser()->hasPerm('addCategory[]')) {
            throw new rex_api_exception('User has no permission to add categories!');
        }

        $parentId = rex_request('parent-category-id', 'int');

        // check permissions
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($parentId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        // prepare and validate parameters
        $data = [];
        $data['catpriority'] = rex_post('category-position', 'int');
        $data['catname'] = rex_post('category-name', 'string');

        $result = new rex_api_result(true, rex_category_service::addCategory($parentId, $data));
        return $result;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
