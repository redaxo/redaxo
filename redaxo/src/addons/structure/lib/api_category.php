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

    $result = new rex_api_result(true, rex_category_service::addCategory($parentId, $data));
    return $result;
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

    $result = new rex_api_result(true, rex_category_service::editCategory($catId, $clangId, $data));
    return $result;
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

    $result = new rex_api_result(true, rex_category_service::deleteCategory($catId));
    // delete row from DOM
    $result->addRenderResult('', '', 'tr', rex_api_result::MODE_REPLACE);
    return $result;
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
      $newStatus = rex_category_service::categoryStatus($catId, $clangId);
      $oldStatus = rex_category_service::prevStatus($newStatus);
      $statusTypes = rex_category_service::statusTypes();

      $result = new rex_api_result(true, rex_i18n::msg('category_status_updated'));
      // replace link-text
      $result->addRenderResult('this', $statusTypes[$newStatus][0], '', null, $statusTypes[$newStatus][1], $statusTypes[$oldStatus][1]);
      return $result;
    }
    else
    {
      throw new rex_api_exception('user has no permission for this category!');
    }
  }
}

class rex_api_category_move extends rex_api_function
{
  public function execute()
  {
    $catId      = rex_request('category-id', 'rex-category-id');
    $newCatId   = rex_request('new-category-id', 'rex-category-id');
    $newPrior   = rex_request('new-prior', 'int', 0);

    /**
     * @var rex_user
     */
    $user = rex::getUser();

      // check permissions
    if($user->hasPerm('editContentOnly[]')) {
      throw new rex_api_exception('api call not allowed for user with "editContentOnly[]"-option!');
    }
    
    // check permissions
    if($user->isAdmin() || $user->getComplexPerm('structure')->hasCategoryPerm($catId) && $user->getComplexPerm('structure')->hasCategoryPerm($newCatId)) {
      $newStatus = rex_category_service::categoryStatus($catId, $clangId);
      $oldStatus = rex_category_service::prevStatus($newStatus);
      $statusTypes = rex_category_service::statusTypes();

      $result = new rex_api_result(true, rex_i18n::msg('category_status_updated'));
      // replace link-text
      $result->addRenderResult('this', $statusTypes[$newStatus][0], '', null, $statusTypes[$newStatus][1], $statusTypes[$oldStatus][1]);
      return $result;
    }
    else
    {
      throw new rex_api_exception('user has no permission for this category!');
    }
  }
}