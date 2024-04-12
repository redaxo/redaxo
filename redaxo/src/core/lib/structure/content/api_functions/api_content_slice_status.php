<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_content_slice_status extends ApiFunction
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        $article = Article::get($articleId, $clang);
        if (!$article instanceof Article) {
            throw new ApiException('Unable to find article with id "' . $articleId . '" and clang "' . $clang . '"!');
        }

        $user = Core::requireUser();
        $categoryId = $article->getCategoryId();

        if (!$user->hasPerm('publishSlice[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new ApiException(I18n::msg('no_rights_to_this_function'));
        }

        $sliceId = rex_request('slice_id', 'int');
        $status = rex_request('status', 'int');

        ContentHandler::sliceStatus($sliceId, $status);

        return new ApiResult(true);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
