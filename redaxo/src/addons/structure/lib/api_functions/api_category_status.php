<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_category_status extends rex_api_function
{
    public function execute()
    {
        $categoryId = rex_request('category-id', 'int');
        $clang = rex_request('clang', 'int');
        $status = rex_request('cat_status', 'int', null);
        $user = rex::getUser();

        // Check permissions
        if ($user->getComplexPerm('structure')->hasCategoryPerm($categoryId) && $user->hasPerm('publishCategory[]')) {
            rex_category_service::categoryStatus($categoryId, $clang, $status);

            $result = new rex_api_result(true, rex_i18n::msg('category_status_updated'));
            return $result;
        }

        throw new rex_api_exception('User has no permission for this category!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
