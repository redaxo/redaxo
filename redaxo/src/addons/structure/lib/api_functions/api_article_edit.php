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
        if (!rex::getUser()->hasPerm('editArticle[]')) {
            throw new rex_api_exception('User has no permission to edit articles!');
        }

        $category_id = rex_request('category_id', 'int');
        $article_id = rex_request('article_id', 'int');
        $clang = rex_request('clang', 'int');

        // check permissions
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        // --------------------- ARTIKEL EDIT
        $data = [];
        $data['priority'] = rex_post('article-position', 'int');
        $data['name'] = rex_post('article-name', 'string');
        $data['template_id'] = rex_post('template_id', 'int');

        $result = new rex_api_result(true, rex_article_service::editArticle($article_id, $clang, $data));
        return $result;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
