<?php

use Redaxo\Core\Core;
use Redaxo\Core\Structure\Article;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_content_slice_status extends rex_api_function
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        $article = Article::get($articleId, $clang);
        if (!$article instanceof Article) {
            throw new rex_api_exception('Unable to find article with id "' . $articleId . '" and clang "' . $clang . '"!');
        }

        $user = Core::requireUser();
        $categoryId = $article->getCategoryId();

        if (!$user->hasPerm('publishSlice[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new rex_api_exception(I18n::msg('no_rights_to_this_function'));
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
