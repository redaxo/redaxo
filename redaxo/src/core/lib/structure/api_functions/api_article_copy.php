<?php

use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_article_copy extends ApiFunction
{
    public function execute()
    {
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int', 1);
        // The destination category in which the given article will be copied
        $categoryCopyIdNew = rex_request('category_copy_id_new', 'int');
        $user = Core::requireUser();

        $context = new rex_context([
            'page' => Controller::getCurrentPage(),
            'clang' => $clang,
        ]);

        if ($user->hasPerm('copyArticle[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryCopyIdNew)) {
            if (false !== ($newId = ArticleHandler::copyArticle($articleId, $categoryCopyIdNew))) {
                $result = new rex_api_result(true, I18n::msg('content_articlecopied'));
                rex_response::sendRedirect($context->getUrl([
                    'article_id' => $newId,
                    'info' => $result->getMessage(),
                ]));
            } else {
                $result = new rex_api_result(false, I18n::msg('content_errorcopyarticle'));
            }

            return $result;
        }

        throw new rex_api_exception(I18n::msg('no_rights_to_this_function'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
