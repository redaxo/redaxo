<?php
/**
 * @package redaxo\structure
 */
class rex_api_article_move extends rex_api_function
{
    /**
     * @throws rex_api_exception
     *
     * @return rex_api_result
     */
    public function execute()
    {
        // The article to move
        $article_id = rex_request('article_id', 'int');
        $category_id = rex_article::get($article_id)->getCategoryId();
        // The destination category in which the given category will be moved
        $category_id_new = rex_request('category_id_new', 'int');

        $user = rex::getUser();

        // Check permissions
        if ($user->hasPerm('moveArticle[]') &&
            $user->getComplexPerm('structure')->hasCategoryPerm($category_id_new)
        ) {
            if (rex_article_service::moveArticle($article_id, $category_id, $category_id_new)) {
                $result = new rex_api_result(true, rex_i18n::msg('content_articlemoved'));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_errormovearticle'));
            }

            return $result;
        }

        throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
