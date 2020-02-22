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
        $article_id = rex_request('article_id', 'int');
        $slice_revision = rex_request('slice_revision', 'int');
        $clang_a = rex_request('clang_a', 'int');
        $clang_b = rex_request('clang_b', 'int');

        $user = rex::getUser();

        // Check permissions
        if ($user->hasPerm('copyContent[]') &&
            $user->getComplexPerm('clang')->hasPerm($clang_a) &&
            $user->getComplexPerm('clang')->hasPerm($clang_b)
        ) {
            if (rex_content_service::copyContent($article_id, $article_id, $clang_a, $clang_b, $slice_revision)) {
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
