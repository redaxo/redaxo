<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2017 studio ahoi
 */

class rex_api_article_move extends rex_api_function
{
    public function execute()
    {
        $article_id = rex_request('article_id', 'int');
        $category_id = rex_article::get($article_id)->getCategoryId();
        $category_id_new = rex_request('category_id_new', 'int');
        $user = rex::getUser();

        if (
            $user->hasPerm('moveArticle[]') &&
            $user->getComplexPerm('structure')->hasCategoryPerm($category_id_new)
        ) {
            if (rex_article_service::moveArticle($article_id, $category_id, $category_id_new)) {
                $result = new rex_api_result(true, rex_i18n::msg('content_articlemoved'));
                #rex_response::sendRedirect($context->getUrl(['info' => $info], false));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_errormovearticle'));
            }

            return $result;
        }

        throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }
}
