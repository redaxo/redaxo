<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
 */

class rex_api_category_move extends rex_api_function
{
    public function execute()
    {
        $category_id = rex_request('category_id', 'int');
        $category_id_new = rex_request('category_id_new', 'int');
        $user = rex::getUser();

        if ($user->hasPerm('moveCategory[]') &&
            $user->getComplexPerm('structure')->hasCategoryPerm($category_id) &&
            $user->getComplexPerm('structure')->hasCategoryPerm($category_id_new)
        ) {
            if ($category_id != $category_id_new && rex_category_service::moveCategory($category_id, $category_id_new)) {
                $result = new rex_api_result(true, rex_i18n::msg('category_moved'));
                #rex_response::sendRedirect($context->getUrl(['info' => $info], false));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_error_movecategory'));
            }

            return $result;
        }

        throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }
}

// It seems, this class was not used anywhere, instead the code before this was used

/**
 * @package redaxo\structure
 *
 * @internal
 */
/*
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
            rex_category_service::editCategory($catId, 0, $data);

            $result = new rex_api_result(true, rex_i18n::msg('category_status_updated'));
            return $result;
        }
        throw new rex_api_exception('user has no permission for this category!');
    }
}*/
