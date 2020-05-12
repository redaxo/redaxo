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
        $category_id = rex_request('category_id', 'int');
        $article_id = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');
        $status = rex_request('art_status', 'int', null);
        $user = rex::getUser();

        // check permissions
        if ($user->getComplexPerm('structure')->hasCategoryPerm($category_id) && $user->hasPerm('publishArticle[]')) {
            rex_article_service::articleStatus($article_id, $clang, $status);

            $result = new rex_api_result(true, rex_i18n::msg('article_status_updated'));

            return $result;
        }

        throw new rex_api_exception('user has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
