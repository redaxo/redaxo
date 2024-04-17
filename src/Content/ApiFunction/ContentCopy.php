<?php

namespace Redaxo\Core\Content\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\ApiFunctionResult;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

class ContentCopy extends ApiFunction
{
    /**
     * @throws ApiFunctionException
     *
     * @return ApiFunctionResult
     */
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $clangA = rex_request('clang_a', 'int');
        $clangB = rex_request('clang_b', 'int');

        $user = Core::requireUser();

        // Check permissions
        if (
            $user->hasPerm('copyContent[]')
            && $user->getComplexPerm('clang')->hasPerm($clangA)
            && $user->getComplexPerm('clang')->hasPerm($clangB)
        ) {
            if (ContentHandler::copyContent($articleId, $articleId, $clangA, $clangB)) {
                return new ApiFunctionResult(true, I18n::msg('content_contentcopy'));
            }

            return new ApiFunctionResult(true, I18n::msg('content_errorcopy'));
        }

        throw new ApiFunctionException(I18n::msg('no_rights_to_this_function'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
