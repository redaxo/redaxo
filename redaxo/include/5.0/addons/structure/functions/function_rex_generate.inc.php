<?php

// ----------------------------------------- ARTICLE

/**
 * Löscht die gecachten Dateien eines Artikels. Wenn keine clang angegeben, wird
 * der Artikel in allen Sprachen gelöscht.
 *
 * @param $id ArtikelId des Artikels
 * @param [$clang ClangId des Artikels]
 * 
 * @return void
 */
function rex_deleteCacheArticle($id, $clang = null)
{
  global $REX;
  
  foreach($REX['CLANG'] as $_clang => $clang_name)
  {
    if($clang !== null && $clang != $_clang)
      continue;
      
    rex_deleteCacheArticleMeta($id, $clang);
    rex_deleteCacheArticleContent($id, $clang);
    rex_deleteCacheArticleLists($id, $clang);
  }
}

/**
 * Löscht die gecachten Meta-Dateien eines Artikels. Wenn keine clang angegeben, wird
 * der Artikel in allen Sprachen gelöscht.
 *
 * @param $id ArtikelId des Artikels
 * @param [$clang ClangId des Artikels]
 * 
 * @return void
 */
function rex_deleteCacheArticleMeta($id, $clang = null)
{
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/articles'. DIRECTORY_SEPARATOR;

  foreach($REX['CLANG'] as $_clang => $clang_name)
  {
    if($clang !== null && $clang != $_clang)
      continue;
      
    @unlink($cachePath . $id .'.'. $_clang .'.article');
  }
}

/**
 * Löscht die gecachten Content-Dateien eines Artikels. Wenn keine clang angegeben, wird
 * der Artikel in allen Sprachen gelöscht.
 *
 * @param $id ArtikelId des Artikels
 * @param [$clang ClangId des Artikels]
 * 
 * @return void
 */
function rex_deleteCacheArticleContent($id, $clang = null)
{
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/articles'. DIRECTORY_SEPARATOR;

  foreach($REX['CLANG'] as $_clang => $clang_name)
  {
    if($clang !== null && $clang != $_clang)
      continue;
      
    @unlink($cachePath . $id .'.'. $_clang .'.content');
  }
}

/**
 * Löscht die gecachten List-Dateien eines Artikels. Wenn keine clang angegeben, wird
 * der Artikel in allen Sprachen gelöscht.
 *
 * @param $id ArtikelId des Artikels
 * @param [$clang ClangId des Artikels]
 * 
 * @return void
 */
function rex_deleteCacheArticleLists($id, $clang = null)
{
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/articles'. DIRECTORY_SEPARATOR;

  foreach($REX['CLANG'] as $_clang => $clang_name)
  {
    if($clang !== null && $clang != $_clang)
      continue;
      
    @unlink($cachePath . $id .'.'. $_clang .'.alist');
    @unlink($cachePath . $id .'.'. $_clang .'.clist');
  }
}


/**
 * Generiert den Artikel-Cache der Metainformationen.
 * 
 * @param $article_id Id des zu generierenden Artikels
 * @param [$clang ClangId des Artikels]
 * 
 * @return TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
 */
function rex_generateArticleMeta($article_id, $clang = null)
{
  global $REX, $I18N;
  
  $qry = 'SELECT * FROM '. $REX['TABLE_PREFIX'] .'article WHERE article_id='. (int) $article_id;
  if($clang !== NULL)
  {
    $qry .= ' AND '. (int) $clang;
  }
  
  $sql = rex_sql::factory();
  $sql->setQuery($qry);
  while($sql->hasNext())
  {
    $_clang = $sql->getValue('clang');
    
    // --------------------------------------------------- Artikelparameter speichern
    $params = array(
      'article_id' => $article_id,
      'last_update_stamp' => time()
    );

    $class_vars = OORedaxo::getClassVars();
    unset($class_vars[array_search('id', $class_vars)]);
    $db_fields = $class_vars;

    foreach($db_fields as $field)
      $params[$field] = $sql->getValue($field);

    $content = '<?php'."\n";
    foreach($params as $name => $value)
    {
      $content .='$REX[\'ART\']['. $article_id .'][\''. $name .'\']['. $_clang .'] = \''. rex_addslashes($value,'\\\'') .'\';'."\n";
    }
    $content .= '?>';
    
    $article_file = $REX['SRC_PATH']."/generated/articles/$article_id.$_clang.article";
    if (rex_put_file_contents($article_file, $content) === FALSE)
    {
      return $I18N->msg('article_could_not_be_generated')." ".$I18N->msg('check_rights_in_directory').$REX['SRC_PATH'] .'/generated/articles/';
    }
    
    // damit die aktuellen änderungen sofort wirksam werden, einbinden!
    require ($article_file);
    
    $sql->next();
  }
  
  return TRUE;
}

/**
 * Generiert den Artikel-Cache des Artikelinhalts.
 * 
 * @param $article_id Id des zu generierenden Artikels
 * @param [$clang ClangId des Artikels]
 * 
 * @return TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
 */
function rex_generateArticleContent($article_id, $clang = null)
{
  global $REX, $I18N;
  
  foreach($REX['CLANG'] as $_clang => $clang_name)
  {
    if($clang !== null && $clang != $_clang)
      continue;
      
    $CONT = new rex_article_base();
    $CONT->setCLang($_clang);
    $CONT->setEval(FALSE); // Content nicht ausführen, damit in Cachedatei gespeichert werden kann
    if (!$CONT->setArticleId($article_id)) return FALSE;
  
    // --------------------------------------------------- Artikelcontent speichern
    $article_content_file = $REX['SRC_PATH']."/generated/articles/$article_id.$_clang.content";
    $article_content = "?>".$CONT->getArticle();
  
    // ----- EXTENSION POINT
    $article_content = rex_register_extension_point('GENERATE_FILTER', $article_content,
      array (
        'id' => $article_id,
        'clang' => $_clang,
        'article' => $CONT
      )
    );
  
    if (rex_put_file_contents($article_content_file, $article_content) === FALSE)
    {
      return $I18N->msg('article_could_not_be_generated')." ".$I18N->msg('check_rights_in_directory').$REX['SRC_PATH'] .'/generated/articles/';
    }
  }
  
  return TRUE;
}

/**
 * Generiert alle *.article u. *.content Dateien eines Artikels/einer Kategorie
 *
 * @param $id ArtikelId des Artikels, der generiert werden soll
 * @param $refreshall Boolean Bei True wird der Inhalte auch komplett neu generiert, bei False nur die Metainfos
 * 
 * @return TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird
 */
function rex_generateArticle($id, $refreshall = true)
{
  global $REX, $I18N;

  // artikel generieren
  // vorraussetzung: articel steht schon in der datenbank
  //
  // -> infos schreiben -> abhaengig von clang
  // --> artikel infos / einzelartikel metadaten
  // --> artikel content / einzelartikel content
  // --> listen generieren // wenn startpage = 1
  // ---> artikel liste
  // ---> category liste

  foreach($REX['CLANG'] as $clang => $clang_name)
  {
    $CONT = new rex_article_base();
    $CONT->setCLang($clang);
    $CONT->setEval(FALSE); // Content nicht ausführen, damit in Cachedatei gespeichert werden kann
    if (!$CONT->setArticleId($id)) return FALSE;
      
    // ----------------------- generiere generated/articles/xx.article
    $MSG = rex_generateArticleMeta($id, $clang);
    if($MSG === FALSE) return FALSE;
    
    if($refreshall)
    {
      // ----------------------- generiere generated/articles/xx.content
      $MSG = rex_generateArticleContent($id, $clang);
      if($MSG === FALSE) return FALSE;
    }

    // ----- EXTENSION POINT
    $MSG = rex_register_extension_point('CLANG_ARTICLE_GENERATED', '',
      array (
        'id' => $id,
        'clang' => $clang,
        'article' => $CONT
      )
    );

    if ($MSG != '')
      echo rex_warning($MSG);

    // --------------------------------------------------- Listen generieren
    if ($CONT->getValue("startpage") == 1)
    {
      rex_generateLists($id);
      rex_generateLists($CONT->getValue("re_id"));
    }
    else
    {
      rex_generateLists($CONT->getValue("re_id"));
    }

  }

  // ----- EXTENSION POINT
  $MSG = rex_register_extension_point('ARTICLE_GENERATED','',array ('id' => $id));

  return TRUE;
}

/**
 * Löscht einen Artikel
 *
 * @param $id ArtikelId des Artikels, der gelöscht werden soll
 * 
 * @return Erfolgsmeldung bzw. Fehlermeldung bei Fehlern.
 */
function rex_deleteArticle($id)
{
  global $I18N;

  $return = _rex_deleteArticle($id);
  return $return;
}

/**
 * Löscht einen Artikel
 * 
 * @param $id ArtikelId des Artikels, der gelöscht werden soll
 * 
 * @return TRUE wenn der Artikel gelöscht wurde, sonst eine Fehlermeldung
 */
function _rex_deleteArticle($id)
{
  global $REX, $I18N;

  // artikel loeschen
  //
  // kontrolle ob erlaubnis nicht hier.. muss vorher geschehen
  //
  // -> startpage = 0
  // --> artikelfiles löschen
  // ---> article
  // ---> content
  // ---> clist
  // ---> alist
  // -> startpage = 1
  // --> rekursiv aufrufen

  $return = array();
  $return['state'] = FALSE;

  if ($id == $REX['START_ARTICLE_ID'])
  {
    $return['message'] = $I18N->msg('cant_delete_sitestartarticle');
    return $return;
  }
  if ($id == $REX['NOTFOUND_ARTICLE_ID'])
  {
    $return['message'] = $I18N->msg('cant_delete_notfoundarticle');
    return $return;
  }

  $ART = rex_sql::factory();
  $ART->setQuery('select * from '.$REX['TABLE_PREFIX'].'article where id='.$id.' and clang=0');

  if ($ART->getRows() > 0)
  {
    $re_id = $ART->getValue('re_id');
    $return['state'] = true;
    
    $return = rex_register_extension_point('ART_PRE_DELETED', $return, array (
                    "id"          => $id,
                    "re_id"       => $re_id,
                    'name'        => $ART->getValue('name'),
                    'status'      => $ART->getValue('status'),
                    'prior'       => $ART->getValue('prior'),
                    'path'        => $ART->getValue('path'),
                    'template_id' => $ART->getValue('template_id')
                )
            );

    if(!$return["state"])
    {
      return $return;
    }
    
    if ($ART->getValue('startpage') == 1)
    {
      $return['message'] = $I18N->msg('category_deleted');
      $SART = rex_sql::factory();
      $SART->setQuery('select * from '.$REX['TABLE_PREFIX'].'article where re_id='.$id.' and clang=0');
      for ($i = 0; $i < $SART->getRows(); $i ++)
      {
        $return['state'] = _rex_deleteArticle($id);
        $SART->next();
      }
    }else
    {
      $return['message'] = $I18N->msg('article_deleted');
    }

    // Rekursion über alle Kindkategorien ergab keine Fehler
    // => löschen erlaubt
    if($return['state'] === true)
    {
      rex_deleteCacheArticle($id);
      $ART->setQuery('delete from '.$REX['TABLE_PREFIX'].'article where id='.$id);
      $ART->setQuery('delete from '.$REX['TABLE_PREFIX'].'article_slice where article_id='.$id);

      // --------------------------------------------------- Listen generieren
      rex_generateLists($re_id);
    }

    return $return;
  }
  else
  {
    $return['message'] = $I18N->msg('category_doesnt_exist');
    return $return;
  }
}

/**
 * Generiert alle *.alist u. *.clist Dateien einer Kategorie/eines Artikels
 *
 * @param $re_id   KategorieId oder ArtikelId, die erneuert werden soll
 * 
 * @return TRUE wenn der Artikel gelöscht wurde, sonst eine Fehlermeldung
 */
function rex_generateLists($re_id, $clang = null)
{
  global $REX, $I18N;

  // generiere listen
  //
  //
  // -> je nach clang
  // --> artikel listen
  // --> catgorie listen
  //

  foreach($REX['CLANG'] as $_clang => $clang_name)
  {
    if($clang !== null && $clang != $_clang)
      continue;
        
    // --------------------------------------- ARTICLE LIST

    $GC = rex_sql::factory();
    // $GC->debugsql = 1;
    $GC->setQuery("select * from ".$REX['TABLE_PREFIX']."article where (re_id=$re_id and clang=$_clang and startpage=0) OR (id=$re_id and clang=$_clang and startpage=1) order by prior,name");
    $content = "<?php\n";
    for ($i = 0; $i < $GC->getRows(); $i ++)
    {
      $id = $GC->getValue("id");
      $content .= "\$REX['RE_ID']['$re_id']['$i'] = \"".$GC->getValue("id")."\";\n";
      $GC->next();
    }
    $content .= "\n?>";

    $article_list_file = $REX['SRC_PATH']."/generated/articles/$re_id.$_clang.alist";
    if (rex_put_file_contents($article_list_file, $content) === FALSE)
    {
      return $I18N->msg('article_could_not_be_generated')." ".$I18N->msg('check_rights_in_directory').$REX['SRC_PATH'] .'/generated/articles/';
    }

    // --------------------------------------- CAT LIST

    $GC = rex_sql::factory();
    $GC->setQuery("select * from ".$REX['TABLE_PREFIX']."article where re_id=$re_id and clang=$_clang and startpage=1 order by catprior,name");
    $content = "<?php\n";
    for ($i = 0; $i < $GC->getRows(); $i ++)
    {
      $id = $GC->getValue("id");
      $content .= "\$REX['RE_CAT_ID']['$re_id']['$i'] = \"".$GC->getValue("id")."\";\n";
      $GC->next();
    }
    $content .= "\n?>";

    $article_categories_file = $REX['SRC_PATH']."/generated/articles/$re_id.$_clang.clist";
    if (rex_put_file_contents($article_categories_file, $content) === FALSE)
    {
      return $I18N->msg('article_could_not_be_generated')." ".$I18N->msg('check_rights_in_directory').$REX['SRC_PATH'] .'/generated/articles/';
    }
  }
  
  return TRUE;
}