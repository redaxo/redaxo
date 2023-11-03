<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_article_edit extends rex_api_function
{
    public function execute()
    {
        if (!rex::requireUser()->hasPerm('editArticle[]')) {
            throw new rex_api_exception('User has no permission to edit articles!');
        }

        $categoryId = rex_request('category_id', 'int');
        $articleId = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        // check permissions
        if (!rex::requireUser()->getComplexPerm('structure')->hasCategoryPerm($categoryId)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        // --------------------- ARTIKEL EDIT
        $data = [];
        $data['priority'] = rex_post('article-position', 'int');
        $data['name'] = rex_post('article-name', 'string');
        $data['template_id'] = rex_post('template_id', 'int');
        return new rex_api_result(true, rex_article_service::editArticle($articleId, $clang, $data));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
