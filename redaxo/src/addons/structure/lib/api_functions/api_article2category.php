<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_article2category extends rex_api_function
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $categoryId = rex_article::get($articleId)->getCategoryId();
        $user = rex::getUser();

        // Check permissions
        if ($user->hasPerm('article2category[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            if (rex_article_service::article2category($articleId)) {
                return new rex_api_result(true, rex_i18n::msg('content_tocategory_ok'));
            }

            return new rex_api_result(false, rex_i18n::msg('content_tocategory_failed'));
        }
        throw new rex_api_exception('User has no permission for this article!');
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
