<?php

use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ArticleHandler;
use Redaxo\Core\Core;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_article_move extends ApiFunction
{
    /**
     * @throws rex_api_exception
     *
     * @return rex_api_result
     */
    public function execute()
    {
        // The article to move
        $articleId = rex_request('article_id', 'int');
        $categoryId = Article::get($articleId)->getCategoryId();
        // The destination category in which the given category will be moved
        $categoryIdNew = rex_request('category_id_new', 'int');

        $user = Core::requireUser();

        // Check permissions
        if ($user->hasPerm('moveArticle[]') && $user->getComplexPerm('structure')->hasCategoryPerm($categoryIdNew)) {
            if (ArticleHandler::moveArticle($articleId, $categoryId, $categoryIdNew)) {
                return new rex_api_result(true, I18n::msg('content_articlemoved'));
            }

            return new rex_api_result(false, I18n::msg('content_errormovearticle'));
        }

        throw new rex_api_exception(I18n::msg('no_rights_to_this_function'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
