<?php

class rex_article_cache
{
  /**
   * Löscht die gecachten Dateien eines Artikels. Wenn keine clang angegeben, wird
   * der Artikel-Cache in allen Sprachen gelöscht.
   *
   * @param $id ArtikelId des Artikels
   * @param [$clang ClangId des Artikels]
   *
   * @return boolean True on success, False on errro
   */
  static public function delete($id, $clang = null)
  {
    // sanity check
    if($id < 0)
    {
      return false;
    }

    foreach(rex_clang::getAllIds() as $_clang)
    {
      if($clang !== null && $clang != $_clang)
      continue;

      self::deleteMeta($id, $clang);
      self::deleteContent($id, $clang);
      self::deleteLists($id, $clang);
    }

    return true;
  }

  /**
   * Löscht die gecachten Meta-Dateien eines Artikels. Wenn keine clang angegeben, wird
   * der Artikel in allen Sprachen gelöscht.
   *
   * @param $id ArtikelId des Artikels
   * @param [$clang ClangId des Artikels]
   *
   * @return boolean True on success, False on errro
   */
  static public function deleteMeta($id, $clang = null)
  {
    // sanity check
    if($id < 0)
    {
      return false;
    }

    $cachePath = rex_path::cache('articles/');

    foreach(rex_clang::getAllIds() as $_clang)
    {
      if($clang !== null && $clang != $_clang)
      continue;

      rex_file::delete($cachePath . $id .'.'. $_clang .'.article');
    }

    return true;
  }

  /**
   * Löscht die gecachten Content-Dateien eines Artikels. Wenn keine clang angegeben, wird
   * der Artikel in allen Sprachen gelöscht.
   *
   * @param $id ArtikelId des Artikels
   * @param [$clang ClangId des Artikels]
   *
   * @return boolean True on success, False on errro
   */
  static public function deleteContent($id, $clang = null)
  {
    // sanity check
    if($id < 0)
    {
      return false;
    }

    $cachePath = rex_path::cache('articles/');

    foreach(rex_clang::getAllIds() as $_clang)
    {
      if($clang !== null && $clang != $_clang)
      continue;

      rex_file::delete($cachePath . $id .'.'. $_clang .'.content');
    }

    return true;
  }

  /**
   * Löscht die gecachten List-Dateien eines Artikels. Wenn keine clang angegeben, wird
   * der Artikel in allen Sprachen gelöscht.
   *
   * @param $id ArtikelId des Artikels
   * @param [$clang ClangId des Artikels]
   *
   * @return boolean True on success, False on errro
   */
  static public function deleteLists($id, $clang = null)
  {
    // sanity check
    if($id < 0)
    {
      return false;
    }

    $cachePath = rex_path::cache('articles/');

    foreach(rex_clang::getAllIds() as $_clang)
    {
      if($clang !== null && $clang != $_clang)
      continue;

      rex_file::delete($cachePath . $id .'.'. $_clang .'.alist');
      rex_file::delete($cachePath . $id .'.'. $_clang .'.clist');
    }

    return true;
  }


  /**
   * Generiert den Artikel-Cache der Metainformationen.
   *
   * @param $article_id Id des zu generierenden Artikels
   * @param [$clang ClangId des Artikels]
   *
   * @return TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
   */
  static public function generateMeta($article_id, $clang = null)
  {
    // sanity check
    if($article_id <= 0)
    {
      return false;
    }

    $qry = 'SELECT * FROM '. rex::getTablePrefix() .'article WHERE id='. (int) $article_id;
    if($clang !== NULL)
    {
      $qry .= ' AND clang='. (int) $clang;
    }

    $sql = rex_sql::factory();
    $sql->setQuery($qry);
    foreach($sql as $row)
    {
      $_clang = $row->getValue('clang');

      // --------------------------------------------------- Artikelparameter speichern
      $params = array(
      'article_id' => $article_id,
      'last_update_stamp' => time()
      );

      $class_vars = rex_ooRedaxo::getClassVars();
      unset($class_vars[array_search('id', $class_vars)]);
      $db_fields = $class_vars;

      foreach($db_fields as $field)
      {
        $params[$field] = $row->getValue($field);
      }

      $cacheArray = array();
      foreach($params as $name => $value)
      {
        $cacheArray[$name][$_clang] = $value;
      }

      $article_file = rex_path::cache("articles/$article_id.$_clang.article");
      if (rex_file::putCache($article_file, $cacheArray) === FALSE)
      {
        return rex_i18n::msg('article_could_not_be_generated')." ".rex_i18n::msg('check_rights_in_directory').rex_path::cache('articles/');
      }
    }

    return TRUE;
  }

  /**
   * Generiert alle *.alist u. *.clist Dateien einer Kategorie/eines Artikels
   *
   * @param $re_id   KategorieId oder ArtikelId, die erneuert werden soll
   *
   * @return TRUE wenn der Artikel gelöscht wurde, sonst eine Fehlermeldung
   */
  static public function generateLists($re_id, $clang = null)
  {
    // sanity check
    if($re_id < 0)
    {
      return false;
    }


    // generiere listen
    //
    //
    // -> je nach clang
    // --> artikel listen
    // --> catgorie listen
    //

    foreach(rex_clang::getAllIds() as $_clang)
    {
      if($clang !== null && $clang != $_clang)
      continue;

      // --------------------------------------- ARTICLE LIST

      $GC = rex_sql::factory();
      // $GC->debugsql = 1;
      $GC->setQuery("select * from ".rex::getTablePrefix()."article where (re_id=$re_id and clang=$_clang and startpage=0) OR (id=$re_id and clang=$_clang and startpage=1) order by prior,name");

      $cacheArray = array();
      for ($i = 0; $i < $GC->getRows(); $i ++)
      {
        $cacheArray[$i] = $GC->getValue("id");
        $GC->next();
      }

      $article_list_file = rex_path::cache("articles/$re_id.$_clang.alist");
      if (rex_file::putCache($article_list_file, $cacheArray) === FALSE)
      {
        return rex_i18n::msg('article_could_not_be_generated')." ".rex_i18n::msg('check_rights_in_directory').rex_path::cache('articles/');
      }

      // --------------------------------------- CAT LIST

      $GC = rex_sql::factory();
      $GC->setQuery("select * from ".rex::getTablePrefix()."article where re_id=$re_id and clang=$_clang and startpage=1 order by catprior,name");

      $cacheArray = array();
      for ($i = 0; $i < $GC->getRows(); $i ++)
      {
        $cacheArray[$i] = $GC->getValue("id");
        $GC->next();
      }

      $article_categories_file = rex_path::cache("articles/$re_id.$_clang.clist");
      if (rex_file::putCache($article_categories_file, $cacheArray) === FALSE)
      {
        return rex_i18n::msg('article_could_not_be_generated')." ".rex_i18n::msg('check_rights_in_directory').rex_path::cache('articles/');
      }
    }

    return TRUE;
  }
}