<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_article2startarticle extends rex_api_function
{
    public function execute()
    {
        $article_id = rex_request('article_id', 'int');
        $category_id = rex_article::get($article_id)->getCategoryId();
        $user = rex::getUser();

        // Check permissions
        if ($user->hasPerm('article2startarticle[]') && $user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            if (rex_article_service::article2startarticle($article_id)) {
                $result = new rex_api_result(true, rex_i18n::msg('content_tostartarticle_ok'));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_tostartarticle_failed'));
            }

            return $result;
        }

        throw new rex_api_exception('user has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
