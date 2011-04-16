<?php

class rex_api_article_add extends rex_api_function
{
  public function execute()
  {
    global $REX;
    
    $category_id = rex_request('category_id', 'rex-category-id');
        
    /**
     * @var rex_login_sql
     */
    $user = $REX['USER'];

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rexApiException('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->hasCategoryPerm($category_id)) {
      throw new rexApiException('user has no permission for this category!');
    }
    
    // parent may be null, when adding in the root cat
    $parent = rex_ooCategory::getCategoryById($category_id);
    // TODO refactor path-build-code into article-service
    if($parent)
    {
      $path = $parent->getPath();
      $path .= $parent->getId(). '|';
    }
    else
    {
      $path = '|';
    }
    
    $data = array();
    $data['name']        = rex_post('article-name', 'string');
    $data['prior']       = rex_post('article-position', 'int');
    $data['template_id'] = rex_post('template_id', 'rex-template-id');
    $data['category_id'] = $category_id;
    $data['path']        = $path;
  
    return rex_article_service::addArticle($data);
  }
}

class rex_api_article_edit extends rex_api_function
{
  public function execute()
  {
    global $REX;
    
    $category_id = rex_request('category_id', 'rex-category-id');
    $article_id  = rex_request('article_id',  'rex-article-id');
    $clang       = rex_request('clang',       'rex-clang-id');
    
    /**
     * @var rex_login_sql
     */
    $user = $REX['USER'];

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rexApiException('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->hasCategoryPerm($category_id)) {
      throw new rexApiException('user has no permission for this category!');
    }
    
    // --------------------- ARTIKEL EDIT
    $data = array();
    $data['prior']       = rex_post('article-position', 'int');
    $data['name']        = rex_post('article-name', 'string');
    $data['template_id'] = rex_post('template_id', 'rex-template-id');
  
    return rex_article_service::editArticle($article_id, $clang, $data);
  }
}

class rex_api_article_delete extends rex_api_function
{
  public function execute()
  {
    global $REX;
    
    $category_id = rex_request('category_id', 'rex-category-id');
    $article_id  = rex_request('article_id',  'rex-article-id');
    
    /**
     * @var rex_login_sql
     */
    $user = $REX['USER'];

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rexApiException('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->hasCategoryPerm($category_id)) {
      throw new rexApiException('user has no permission for this category!');
    }
    
    return rex_article_service::deleteArticle($article_id);
  }
}

class rex_api_article_status extends rex_api_function
{
  public function execute()
  {
    global $REX;
    
    $catId       = rex_request('category-id', 'rex-category-id');
    $article_id  = rex_request('article_id',  'rex-article-id');
    $clang       = rex_request('clang',       'rex-clang-id');
    
    /**
     * @var rex_login_sql
     */
    $user = $REX['USER'];

    // check permissions
    if($user->isAdmin() || $user->hasCategoryPerm($catId) && $user->hasPerm('publishArticle[]')) {
      return rex_article_service::articleStatus($article_id, $clang);
    }
    else
    {
      throw new rexApiException('user has no permission for this article!');
    }
  }
}