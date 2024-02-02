<?php

/**
 * @package redaxo\structure\content
 *
 * @internal
 */
class rex_api_content_slice_status extends rex_api_function
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        $article = rex_article::get($articleId, $clang);
        if (!$article instanceof rex_article) {
            throw new rex_api_exception('Unable to find article with id "' . $articleId . '" and clang "' . $clang . '"!');
        }

        $user = rex::requireUser();
        $categoryId = $article->getCategoryId();

        if (!$user->hasPerm('publishSlice[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
        }

        $sliceId = rex_request('slice_id', 'int');
        $status = rex_request('status', 'int');

        rex_content_service::sliceStatus($sliceId, $status);

        return new rex_api_result(true);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
