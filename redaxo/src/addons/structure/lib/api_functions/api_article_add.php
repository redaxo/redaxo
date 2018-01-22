<?php
/**
 * @package redaxo\structure
 *
 * @internal
 */
class rex_api_article_add extends rex_api_function
{
    public function execute()
    {
        $category_id = rex_request('category_id', 'int');

        // check permissions
        if (!rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
            throw new rex_api_exception('user has no permission for this category!');
        }

        $data = [];
        $data['name'] = rex_post('article-name', 'string');
        $data['priority'] = rex_post('article-position', 'int');
        $data['template_id'] = rex_post('template_id', 'int');
        $data['category_id'] = $category_id;

        $result = new rex_api_result(true, rex_article_service::addArticle($data));
        return $result;
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
