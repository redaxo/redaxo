<?php

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Content\ContentHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

class rex_api_content_copy extends ApiFunction
{
    /**
     * @throws ApiException
     *
     * @return ApiResult
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
                return new ApiResult(true, I18n::msg('content_contentcopy'));
            }

            return new ApiResult(true, I18n::msg('content_errorcopy'));
        }

        throw new ApiException(I18n::msg('no_rights_to_this_function'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
