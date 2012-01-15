<?php

class rex_api_content_move_slice extends rex_api_function
{
  public function execute()
  {
    $article_id  = rex_request('article_id',  'rex-article-id');
    $clang       = rex_request('clang',       'rex-clang-id');
    $slice_id    = rex_request('slice_id',    'int');
    $direction   = rex_request('direction',   'string');
    
    $ooArt = rex_ooArticle::getArticleById($article_id, $clang);
    if(!rex_ooArticle::isValid($ooArt))
    {
      throw new rex_api_exception('Unable to find article with id "'. $article_id .'" and clang "'. $clang .'"!');
    }
    $category_id = $ooArt->getCategoryId();
    
    /**
     * @var rex_user
     */
    $user = rex::getUser();
    
    // check permissions
    if(!$user->hasPerm('moveSlice[]')) {
      throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }

    if(!$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
      throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }
    
    // modul und rechte vorhanden ?
    $CM = rex_sql::factory();
    $CM->setQuery("select * from " . rex::getTablePrefix() . "article_slice left join " . rex::getTablePrefix() . "module on " . rex::getTablePrefix() . "article_slice.modultyp_id=" . rex::getTablePrefix() . "module.id where " . rex::getTablePrefix() . "article_slice.id='$slice_id' and clang=$clang");
    if ($CM->getRows() != 1)
    {
      throw new rex_api_exception(rex_i18n::msg('module_not_found'));
    }
    else
    {
      $module_id = (int) $CM->getValue(rex::getTablePrefix()."article_slice.modultyp_id");

      // ----- RECHTE AM MODUL ?
      if ($user->isAdmin() || $user->getComplexPerm('modules')->hasPerm($module_id))
      {
        $message = rex_content_service::moveSlice($slice_id, $clang, $direction);
      }
      else 
      {
        throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
      }
    }
    
    $result = new rex_api_result(true, $message);
    return $result;
  }
}

class rex_api_content_copy_content extends rex_api_function
{
  public function execute()
  {
    $article_id        = rex_request('article_id',  'rex-article-id');
    $slice_revision    = rex_request('slice_revision',    'int');
    $clang_a           = rex_request('clang_a', 'rex-clang-id');
    $clang_b           = rex_request('clang_b', 'rex-clang-id');
    
    /**
     * @var rex_user
     */
    $user = rex::getUser();
    $success = false;
    $message = rex_i18n::msg('content_errorcopy');
    if ($user->isAdmin() || ($user->hasPerm('copyContent[]') && $user->getComplexPerm('clang')->hasPerm($clang_a) && $user->getComplexPerm('clang')->hasPerm($clang_b)))
    {
      if (rex_content_service::copyContent($article_id, $article_id, $clang_a, $clang_b, 0, $slice_revision))
      {
        $message = rex_i18n::msg('content_contentcopy');
      }
    }
    else
    {
      throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }
    
    $result = new rex_api_result($success, $message);
    return $result;
  }
}

class rex_api_content_move_article extends rex_api_function
{
  public function execute()
  {
    $article_id        = rex_request('article_id',  'rex-article-id');
    $clang             = rex_request('clang',       'rex-clang-id');
    $category_id_new   = rex_request('category_id_new', 'rex-category-id');
    
    $ooArt = rex_ooArticle::getArticleById($article_id, $clang);
    if(!rex_ooArticle::isValid($ooArt))
    {
      throw new rex_api_exception('Unable to find article with id "'. $article_id .'" and clang "'. $clang .'"!');
    }
    $category_id = $ooArt->getCategoryId();
    
    if ($category_id != $article_id)
    {
      /**
       * @var rex_user
       */
      $user = rex::getUser();
      
      if ($user->isAdmin() || ($user->hasPerm('moveArticle[]') && $user->getComplexPerm('structure')->hasCategoryPerm($category_id_new)))
      {
        if (rex_content_service::moveArticle($article_id, $category_id, $category_id_new))
        {
          $result = new rex_api_result(true, rex_i18n::msg('content_articlemoved'));
          return $result;
        }
      }
      else
      {
        throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
      }
    }
    throw new rex_api_exception(rex_i18n::msg('content_errormovearticle'));
  }
}

/*
TODO
we have to figure out, how this could work when rex-actions are configured.
untils this is solved, we cannot publish this api function!

class rex_api_content_delete_slice extends rex_api_function
{
  public function execute()
  {
    $clang       = rex_request('clang',       'rex-clang-id');
    $slice_id    = rex_request('slice_id',    'int');
    
    $CM = rex_sql::factory();
    $CM->setQuery("SELECT * FROM " . rex::getTablePrefix() . "article_slice LEFT JOIN " . rex::getTablePrefix() . "module ON " . rex::getTablePrefix() . "article_slice.modultyp_id=" . rex::getTablePrefix() . "module.id WHERE " . rex::getTablePrefix() . "article_slice.id='$slice_id' AND clang=$clang");
    if ($CM->getRows() != 1)
    {
      throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }
    $module_id = $CM->getValue("" . rex::getTablePrefix() . "article_slice.modultyp_id");
    
    $user = rex::getUser();
    
    if (!($user->isAdmin() || $user->getComplexPerm('modules')->hasPerm($module_id)))
    {
      throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }    
    
    $success = false;
    $message = rex_i18n::msg('block_not_deleted');
    if(rex_content_service::deleteSlice($slice_id))
    {
      $message = rex_i18n::msg('block_deleted');
      $success = true;
    }
    
    $result = new rex_api_result($success, $message);
    return $result;
  }
}
*/