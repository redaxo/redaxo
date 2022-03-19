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
        $articleId = rex_request('article_id', 'int');
        $categoryId = rex_article::get($articleId)->getCategoryId();
        $user = rex::getUser();

        // Check permissions
        if ($user->hasPerm('article2startarticle[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            if (rex_article_service::article2startarticle($articleId)) {
                return new rex_api_result(true, rex_i18n::msg('content_tostartarticle_ok'));
            }

            return new rex_api_result(false, rex_i18n::msg('content_tostartarticle_failed'));
        }

        throw new rex_api_exception('user has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
