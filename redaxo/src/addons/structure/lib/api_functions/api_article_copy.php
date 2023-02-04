<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_article_copy extends rex_api_function
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int', 1);
        // The destination category in which the given article will be copied
        $categoryCopyIdNew = rex_request('category_copy_id_new', 'int');
        $user = rex::requireUser();

        $context = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'clang' => $clang,
        ]);

        if ($user->hasPerm('copyArticle[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryCopyIdNew)) {
            if (false !== ($newId = rex_article_service::copyArticle($articleId, $categoryCopyIdNew))) {
                $result = new rex_api_result(true, rex_i18n::msg('content_articlecopied'));
                rex_response::sendRedirect($context->getUrl([
                    'article_id' => $newId,
                    'info' => $result->getMessage(),
                ], false));
            } else {
                $result = new rex_api_result(false, rex_i18n::msg('content_errorcopyarticle'));
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
