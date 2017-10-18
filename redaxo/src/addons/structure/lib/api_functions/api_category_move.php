<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_category_move extends rex_api_function
{
    public function execute()
    {
        // the category to move
        $catId = rex_request('category-id', 'int');
        // the destination category in which the given category will be moved
        $newCatId = rex_request('new-category-id', 'int');
        // a optional priority for the moved category
        $newPriority = rex_request('new-priority', 'int', 0);

        $user = rex::getUser();

        // check permissions
        if ($user->getComplexPerm('structure')->hasCategoryPerm($catId) && $user->getComplexPerm('structure')->hasCategoryPerm($newCatId)) {
            rex_category_service::moveCategory($catId, $newCatId);

            // doesnt matter which clang id
            $data['catpriority'] = $newPriority;
            rex_category_service::editCategory($catId, 1, $data);

            $result = new rex_api_result(true, rex_i18n::msg('category_status_updated'));
            return $result;
        }
        throw new rex_api_exception('user has no permission for this category!');
    }
}
