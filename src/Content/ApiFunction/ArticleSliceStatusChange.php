<?php

namespace Redaxo\Core\Content\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class ArticleSliceStatusChange extends ApiFunction
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        $article = Article::get($articleId, $clang);
        if (!$article instanceof Article) {
            throw new ApiFunctionException('Unable to find article with id "' . $articleId . '" and clang "' . $clang . '"!');
        }

        $user = Core::requireUser();
        $categoryId = $article->getCategoryId();

        if (!$user->hasPerm('publishSlice[]') || !$user->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new ApiFunctionException(I18n::msg('no_rights_to_this_function'));
        }

        $sliceId = rex_request('slice_id', 'int');
        $status = rex_request('status', 'int');

        ContentHandler::sliceStatus($sliceId, $status);

        return new Result(true);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}