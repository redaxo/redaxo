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
        $article_id = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        $article = rex_article::get($article_id, $clang);
        if (!$article instanceof rex_article) {
            throw new rex_api_exception('Unable to find article with id "' . $article_id . '" and clang "' . $clang . '"!');
        }

        $category_id = $article->getCategoryId();

        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
        }

        $slice_id = rex_request('slice_id', 'int');
        $status = rex_request('status', 'int');

        rex_content_service::sliceStatus($slice_id, $status);

        return new rex_api_result(true);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
