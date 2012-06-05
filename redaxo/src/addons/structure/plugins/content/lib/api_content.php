<?php

class rex_api_content_move_slice extends rex_api_function
{
  public function execute()
  {
    $article_id  = rex_request('article_id',  'int');
    $clang       = rex_request('clang',       'int');
    $slice_id    = rex_request('slice_id',    'int');
    $direction   = rex_request('direction',   'string');

    $ooArt = rex_article::getArticleById($article_id, $clang);
    if (!$ooArt instanceof rex_article) {
      throw new rex_api_exception('Unable to find article with id "' . $article_id . '" and clang "' . $clang . '"!');
    }
    $category_id = $ooArt->getCategoryId();

    /**
     * @var rex_user
     */
    $user = rex::getUser();

    // check permissions
    if (!$user->hasPerm('moveSlice[]')) {
      throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }

    if (!$user->getComplexPerm('structure')->hasCategoryPerm($category_id)) {
      throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
    }

    // modul und rechte vorhanden ?
    $CM = rex_sql::factory();
    $CM->setQuery('select * from ' . rex::getTablePrefix() . 'article_slice left join ' . rex::getTablePrefix() . 'module on ' . rex::getTablePrefix() . 'article_slice.modultyp_id=' . rex::getTablePrefix() . 'module.id where ' . rex::getTablePrefix() . "article_slice.id='$slice_id' and clang=$clang");
    if ($CM->getRows() != 1) {
      throw new rex_api_exception(rex_i18n::msg('module_not_found'));
    } else {
      $module_id = (int) $CM->getValue(rex::getTablePrefix() . 'article_slice.modultyp_id');

      // ----- RECHTE AM MODUL ?
      if ($user->getComplexPerm('modules')->hasPerm($module_id)) {
        $message = rex_content_service::moveSlice($slice_id, $clang, $direction);
      } else {
        throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
      }
    }

    $result = new rex_api_result(true, $message);
    return $result;
  }
}
