<?php

class rex_api_article_add extends rex_api_function
{
  public function execute()
  {
    $category_id = rex_request('category_id', 'rex-category-id');

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rex_api_exception('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
      throw new rex_api_exception('user has no permission for this category!');
    }

    $data = array();
    $data['name']        = rex_post('article-name', 'string');
    $data['prior']       = rex_post('article-position', 'int');
    $data['template_id'] = rex_post('template_id', 'rex-template-id');
    $data['category_id'] = $category_id;

    $result = new rex_api_result(true, rex_article_service::addArticle($data));
    return $result;
  }
}

class rex_api_article_edit extends rex_api_function
{
  public function execute()
  {
    $category_id = rex_request('category_id', 'rex-category-id');
    $article_id  = rex_request('article_id',  'rex-article-id');
    $clang       = rex_request('clang',       'rex-clang-id');

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rex_api_exception('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
      throw new rex_api_exception('user has no permission for this category!');
    }

    // --------------------- ARTIKEL EDIT
    $data = array();
    $data['prior']       = rex_post('article-position', 'int');
    $data['name']        = rex_post('article-name', 'string');
    $data['template_id'] = rex_post('template_id', 'rex-template-id');

    $result = new rex_api_result(true, rex_article_service::editArticle($article_id, $clang, $data));
    return $result;
  }
}

class rex_api_article_delete extends rex_api_function
{
  public function execute()
  {
    $category_id = rex_request('category_id', 'rex-category-id');
    $article_id  = rex_request('article_id',  'rex-article-id');

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rex_api_exception('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
      throw new rex_api_exception('user has no permission for this category!');
    }

    $result = new rex_api_result(true, rex_article_service::deleteArticle($article_id));
    return $result;
  }
}

class rex_api_article_status extends rex_api_function
{
  public function execute()
  {
    $catId       = rex_request('category-id', 'rex-category-id');
    $article_id  = rex_request('article_id',  'rex-article-id');
    $clang       = rex_request('clang',       'rex-clang-id');

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if($user->isAdmin() || $user->getComplexPerm('structure')->hasCategoryPerm($catId) && $user->hasPerm('publishArticle[]')) {
      $newStatus = rex_article_service::articleStatus($article_id, $clang);
      $oldStatus = rex_article_service::prevStatus($newStatus);
      $statusTypes = rex_article_service::statusTypes();

      $result = new rex_api_result(true, rex_i18n::msg('article_status_updated'));
      return $result;
    }
    else
    {
      throw new rex_api_exception('user has no permission for this article!');
    }
  }
}

class rex_api_article2category extends rex_api_function
{
  public function execute()
  {
    $article_id  = rex_request('article_id',  'rex-article-id');

    $ooArticle = rex_ooArticle::getArticleById($article_id);
    $category_id = $ooArticle->getCategoryId();
    
    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if($user->isAdmin() || ($user->hasPerm('article2category[]')  && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id))) {
      if(rex_article_service::article2category($article_id))
      {
        $result = new rex_api_result(true, rex_i18n::msg('content_tocategory_ok'));
      }
      else
      {
        $result = new rex_api_result(false, rex_i18n::msg('content_tocategory_failed'));
      }

      return $result;
    }
    else
    {
      throw new rex_api_exception('user has no permission for this article!');
    }
  }
}

class rex_api_category2article extends rex_api_function
{
  public function execute()
  {
    $article_id  = rex_request('article_id',  'rex-article-id');
    
    $ooArticle = rex_ooArticle::getArticleById($article_id);
    $category_id = $ooArticle->getCategoryId();

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // article2category und category2article verwenden das gleiche Recht: article2category
    if($user->isAdmin() || ($user->hasPerm('article2category[]')) && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
      if(rex_article_service::category2article($article_id))
      {
        $result = new rex_api_result(true, rex_i18n::msg('content_toarticle_ok'));
      }
      else
      {
        $result = new rex_api_result(false, rex_i18n::msg('content_toarticle_failed'));
      }

      return $result;
    }
    else
    {
      throw new rex_api_exception('user has no permission for this article!');
    }
  }
}

class rex_api_article2startpage extends rex_api_function
{
  public function execute()
  {
    $article_id  = rex_request('article_id',  'rex-article-id');
    
    $ooArticle = rex_ooArticle::getArticleById($article_id);
    $category_id = $ooArticle->getCategoryId();

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // article2category und category2article verwenden das gleiche Recht: article2category
    if($user->isAdmin() || ($user->hasPerm('article2startpage[]')) && rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
      if(rex_article_service::article2startpage($article_id))
      {
        $result = new rex_api_result(true, rex_i18n::msg('content_tostartarticle_ok'));
      }
      else
      {
        $result = new rex_api_result(false, rex_i18n::msg('content_tostartarticle_failed'));
      }

      return $result;
    }
    else
    {
      throw new rex_api_exception('user has no permission for this article!');
    }
  }
}
