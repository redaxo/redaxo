<?php
/**
 * @package redaxo\structure\content
 */
class rex_api_content_copy extends rex_api_function
{
    /**
     * @throws rex_api_exception
     *
     * @return rex_api_result
     */
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $sliceRevision = rex_request('slice_revision', 'int');
        $clangA = rex_request('clang_a', 'int');
        $clangB = rex_request('clang_b', 'int');

        $user = rex::getUser();

        // Check permissions
        if ($user->hasPerm('copyContent[]') &&
            $user->getComplexPerm('clang')->hasPerm($clangA) &&
            $user->getComplexPerm('clang')->hasPerm($clangB)
        ) {
            if (rex_content_service::copyContent($articleId, $articleId, $clangA, $clangB, $sliceRevision)) {
                $result = new rex_api_result(true, rex_i18n::msg('content_contentcopy'));
            } else {
                $result = new rex_api_result(true, rex_i18n::msg('content_errorcopy'));
            }

            return $result;
        }

        throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
