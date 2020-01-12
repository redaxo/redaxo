<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_category_edit extends rex_api_function
{
    public function execute()
    {
        $catId = rex_request('category-id', 'int');
        $clangId = rex_request('clang', 'int');

        /**
         * @var rex_user
         */
        $user = rex::getUser();

        // check permissions
        if (!$user->getComplexPerm('structure')->hasCategoryPerm($catId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        // prepare and validate parameters
        $data = [];
        $data['catpriority'] = rex_post('category-position', 'int');
        $data['catname'] = rex_post('category-name', 'string');

        $result = new rex_api_result(true, rex_category_service::editCategory($catId, $clangId, $data));
        return $result;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
