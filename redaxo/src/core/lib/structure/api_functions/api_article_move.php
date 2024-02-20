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
        $articleId = rex_request('article_id', 'int');
        $categoryId = rex_article::get($articleId)->getCategoryId();
        // The destination category in which the given category will be moved
        $categoryIdNew = rex_request('category_id_new', 'int');

        $user = rex::requireUser();

        // Check permissions
        if ($user->hasPerm('moveArticle[]') &&
            $user->getComplexPerm('structure')->hasCategoryPerm($categoryIdNew)
        ) {
            if (rex_article_service::moveArticle($articleId, $categoryId, $categoryIdNew)) {
                return new rex_api_result(true, rex_i18n::msg('content_articlemoved'));
            }

            return new rex_api_result(false, rex_i18n::msg('content_errormovearticle'));
        }

        throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
