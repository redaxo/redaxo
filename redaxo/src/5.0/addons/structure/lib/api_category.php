<?php

class rex_api_category_add extends rex_api_function
{
  public function execute()
  {
    global $REX;

    $parentId = rex_request('parent-category-id', 'rex-category-id');

    /**
     * @var rex_login_sql
     */
    $user = $REX['USER'];

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rexApiException('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->hasCategoryPerm($parentId)) {
      throw new rexApiException('user has no permission for this category!');
    }

    // parent may be null, when adding in the root cat
    $parent = rex_ooCategory::getCategoryById($parentId);
    // TODO refactor path-build-code into category-service
    if($parent)
    {
      $path = $parent->getPath();
      $path .= $parent->getId(). '|';
    }
    else
    {
      $path = '|';
    }

    // prepare and validate parameters
    $data = array();
    $data['catprior'] = rex_post('category-position', 'int');
    $data['catname']  = rex_post('category-name', 'string');
    $data['path']     = $path;

    return rex_category_service::addCategory($parentId, $data);
  }
}

class rex_api_category_edit extends rex_api_function
{
  public function execute()
  {
    global $REX;

    $catId   = rex_request('category-id', 'rex-category-id');
    $clangId = rex_request('clang', 'rex-clang-id');
    
    /**
     * @var rex_login_sql
     */
    $user = $REX['USER'];

    // check permissions
    if(!$user->hasCategoryPerm($catId)) {
      throw new rexApiException('user has no permission for this category!');
    }

    // prepare and validate parameters
    $data = array();
    $data['catprior'] = rex_post('category-position', 'int');
    $data['catname']  = rex_post('category-name', 'string');
    
    return rex_category_service::editCategory($catId, $clangId, $data);
  }
}

class rex_api_category_delete extends rex_api_function
{
  public function execute()
  {
    global $REX;

    $catId   = rex_request('category-id', 'rex-category-id');
    
    /**
     * @var rex_login_sql
     */
    $user = $REX['USER'];

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rexApiException('api call not allowed for user with "editContentOnly[]"-option!');
    }
    
    if(!$user->hasCategoryPerm($catId)) {
      throw new rexApiException('user has no permission for this category!');
    }

    return rex_category_service::deleteCategory($catId);
  }
}

class rex_api_category_status extends rex_api_function
{
  public function execute()
  {
    global $REX;

    $catId   = rex_request('category-id', 'rex-category-id');
    $clangId = rex_request('clang', 'rex-clang-id');
    
    /**
     * @var rex_login_sql
     */
    $user = $REX['USER'];
    
    // check permissions
    if($user->isAdmin() || $user->hasCategoryPerm($catId) && $user->hasPerm('publishArticle[]')) {
      return rex_category_service::categoryStatus($catId, $clangId);
    }
    else
    {
      throw new rexApiException('user has no permission for this category!');
    }
  }
}