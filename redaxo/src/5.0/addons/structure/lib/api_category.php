<?php

class rex_api_category_add extends rex_api_function
{
  public function execute()
  {
    $parentId = rex_request('parent-category-id', 'rex-category-id');

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rex_api_exception('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->getComplexPerm('structure')->hasCategoryPerm($parentId)) {
      throw new rex_api_exception('user has no permission for this category!');
    }

    // prepare and validate parameters
    $data = array();
    $data['catprior'] = rex_post('category-position', 'int');
    $data['catname']  = rex_post('category-name', 'string');

    return rex_category_service::addCategory($parentId, $data);
  }
}

class rex_api_category_edit extends rex_api_function
{
  public function execute()
  {
    $catId   = rex_request('category-id', 'rex-category-id');
    $clangId = rex_request('clang', 'rex-clang-id');

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if(!$user->getComplexPerm('structure')->hasCategoryPerm($catId)) {
      throw new rex_api_exception('user has no permission for this category!');
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
    $catId   = rex_request('category-id', 'rex-category-id');

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rex_api_exception('api call not allowed for user with "editContentOnly[]"-option!');
    }

    if(!$user->getComplexPerm('structure')->hasCategoryPerm($catId)) {
      throw new rex_api_exception('user has no permission for this category!');
    }

    return rex_category_service::deleteCategory($catId);
  }
}

class rex_api_category_status extends rex_api_function
{
  public function execute()
  {
    $catId   = rex_request('category-id', 'rex-category-id');
    $clangId = rex_request('clang', 'rex-clang-id');

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if($user->isAdmin() || $user->getComplexPerm('structure')->hasCategoryPerm($catId) && $user->hasPerm('publishArticle[]')) {
      return rex_category_service::categoryStatus($catId, $clangId);
    }
    else
    {
      throw new rex_api_exception('user has no permission for this category!');
    }
  }
}