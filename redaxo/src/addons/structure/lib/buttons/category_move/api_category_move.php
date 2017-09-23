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
