<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_article_status extends rex_api_function
{
    public function execute()
    {
        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');
        $status = rex_request('art_status', 'int', null);
        $user = rex::requireUser();

        // check permissions
        if ($user->getComplexPerm('structure')->hasCategoryPerm($categoryId) && $user->hasPerm('publishArticle[]')) {
            rex_article_service::articleStatus($articleId, $clang, $status);

            return new rex_api_result(true, rex_i18n::msg('article_status_updated'));
        }

        throw new rex_api_exception('user has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
