<?php


/**
 * Funktionensammlung für die generierung der Artikel/Templates/Kategorien/Metainfos.. etc.
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------------------------------- Alles generieren

/**
 * Löscht den vollständigen Artikel-Cache.
 */
function rex_generateAll()
{
  global $REX, $I18N;

  // ----------------------------------------------------------- generated löschen
  rex_deleteDir($REX['INCLUDE_PATH'].'/generated', FALSE);
  
  // ----------------------------------------------------------- generiere clang
  if(($MSG = rex_generateClang()) !== TRUE)
  {
    return $MSG;
  }
  
  // ----------------------------------------------------------- message
  $MSG = $I18N->msg('delete_cache_message');

  // ----- EXTENSION POINT
  $MSG = rex_register_extension_point('ALL_GENERATED', $MSG);

  return $MSG;
}

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
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'articles'. DIRECTORY_SEPARATOR;

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
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'articles'. DIRECTORY_SEPARATOR;

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
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'articles'. DIRECTORY_SEPARATOR;

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
  
  foreach($REX['CLANG'] as $_clang => $clang_name)
  {
    if($clang !== null && $clang != $_clang)
      continue;
    
    $CONT = new rex_article_base();
    $CONT->setCLang($_clang);
    $CONT->setEval(FALSE); // Content nicht ausfŸhren, damit in Cachedatei gespeichert werden kann
    if (!$CONT->setArticleId($article_id)) return FALSE;

    // --------------------------------------------------- Artikelparameter speichern
    $params = array(
      'article_id' => $article_id,
      'last_update_stamp' => time()
    );

    $class_vars = OORedaxo::getClassVars();
    unset($class_vars[array_search('id', $class_vars)]);
    $db_fields = $class_vars;

    foreach($db_fields as $field)
      $params[$field] = $CONT->getValue($field);

    $content = '<?php'."\n";
    foreach($params as $name => $value)
    {
      $content .='$REX[\'ART\']['. $article_id .'][\''. $name .'\']['. $_clang .'] = \''. rex_addslashes($value,'\\\'') .'\';'."\n";
    }
    $content .= '?>';
    
    $article_file = $REX['INCLUDE_PATH']."/generated/articles/$article_id.$_clang.article";
    if (rex_put_file_contents($article_file, $content) === FALSE)
    {
      return $I18N->msg('article_could_not_be_generated')." ".$I18N->msg('check_rights_in_directory').$REX['INCLUDE_PATH']."/generated/articles/";
    }
    
    // damit die aktuellen änderungen sofort wirksam werden, einbinden!
    require ($article_file);
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
    $article_content_file = $REX['INCLUDE_PATH']."/generated/articles/$article_id.$_clang.content";
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
      return $I18N->msg('article_could_not_be_generated')." ".$I18N->msg('check_rights_in_directory').$REX['INCLUDE_PATH']."/generated/articles/";
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

    $article_list_file = $REX['INCLUDE_PATH']."/generated/articles/$re_id.$_clang.alist";
    if (rex_put_file_contents($article_list_file, $content) === FALSE)
    {
      return $I18N->msg('article_could_not_be_generated')." ".$I18N->msg('check_rights_in_directory').$REX['INCLUDE_PATH']."/generated/articles/";
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

    $article_categories_file = $REX['INCLUDE_PATH']."/generated/articles/$re_id.$_clang.clist";
    if (rex_put_file_contents($article_categories_file, $content) === FALSE)
    {
      return $I18N->msg('article_could_not_be_generated')." ".$I18N->msg('check_rights_in_directory').$REX['INCLUDE_PATH']."/generated/articles/";
    }
  }
  
  return TRUE;
}

/**
 * Löscht die gecachte Medium-Datei.
 *
 * @param $filename Dateiname
 * 
 * @return void
 */
function rex_deleteCacheMedia($filename)
{
  global $REX;
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'files'. DIRECTORY_SEPARATOR;
  @unlink($cachePath . $filename . '.media');
  rex_deleteCacheMediaLists();
}

/**
 * Löscht die gecachten Dateien der Media-Kategorie.
 *
 * @param $category_id Id der Media-Kategorie
 * 
 * @return void
 */
function rex_deleteCacheMediaCategory($category_id)
{
  global $REX;
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'files'. DIRECTORY_SEPARATOR;
  @unlink($cachePath . $category_id . '.mcat');
  rex_deleteCacheMediaCategoryLists();
}

/**
 * Löscht die gecachten Media-Listen.
 * 
 * @return void
 */
function rex_deleteCacheMediaLists()
{
  global $REX;
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'files'. DIRECTORY_SEPARATOR;
  
  $glob = glob($cachePath . '*.mlist');
  if(is_array($glob))
  	foreach ($glob as $file)
    	@unlink($file);
  
  $glob = glob($cachePath . '*.mextlist');
  if(is_array($glob))
  	foreach ($glob as $file)
    	@unlink($file);
}

/**
 * Löscht die gecachte Liste mit den Media der Kategorie.
 *
 * @param $category_id Id der Media-Kategorie
 * 
 * @return void
 */
function rex_deleteCacheMediaList($category_id)
{
  global $REX;
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'files'. DIRECTORY_SEPARATOR;
  @unlink($cachePath . $category_id . '.mlist');
}

/**
 * Löscht die gecachten Media-Kategorien-Listen.
 * 
 * @return void
 */
function rex_deleteCacheMediaCategoryLists()
{
  global $REX;
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'files'. DIRECTORY_SEPARATOR;
  $glob = glob($cachePath . '*.mclist');
  if (is_array($glob))
    foreach ($glob as $file)
      @unlink($file);
}

/**
 * Löscht die gecachte Media-Kategorien-Liste.
 *
 * @param $category_id Id der Media-Kategorie
 * 
 * @return void
 */
function rex_deleteCacheMediaCategoryList($category_id)
{
  global $REX;
  
  $cachePath = $REX['INCLUDE_PATH']. DIRECTORY_SEPARATOR .'generated'. DIRECTORY_SEPARATOR .'files'. DIRECTORY_SEPARATOR;
  @unlink($cachePath . $category_id . '.mclist');
}

/**
 * Generiert den Cache des Mediums.
 * 
 * @param $filename Dateiname des zu generierenden Mediums
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMedia($filename)
{
  global $REX;
  
  $query = 'SELECT * FROM ' . OOMedia :: _getTableName() . ' WHERE filename = "'.$filename.'"';
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);
  
  if ($sql->getRows() == 0)
    return false;
  
  $content = '<?php'."\n";
  foreach($sql->getFieldNames() as $fieldName)
  {
    $content .= '$REX[\'MEDIA\'][\'FILENAME\'][\''. $filename .'\'][\''. $fieldName .'\'] = \''. rex_addslashes($sql->getValue($fieldName),'\\\'') .'\';'."\n";
  }
  $content .= '?>';
  
  $media_file = $REX['INCLUDE_PATH']."/generated/files/$filename.media";
  if (rex_put_file_contents($media_file, $content))
    return true;
  
  return false;
}

/**
 * Generiert den Cache der Media-Kategorie.
 * 
 * @param $category_id Id des zu generierenden Media-Kategorie
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMediaCategory($category_id)
{
  global $REX;
  
  $query = 'SELECT * FROM ' . OOMediaCategory :: _getTableName() . ' WHERE id = '.$category_id;
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);
  
  if ($sql->getRows() == 0)
    return false;
  
  $content = '<?php'."\n";
  foreach($sql->getFieldNames() as $fieldName)
  {
    $content .= '$REX[\'MEDIA\'][\'CAT_ID\']['. $category_id .'][\''. $fieldName .'\'] = \''. rex_addslashes($sql->getValue($fieldName),'\\\'') .'\';'."\n";
  }
  $content .= '?>';
  
  $cat_file = $REX['INCLUDE_PATH']."/generated/files/$category_id.mcat";
  if (rex_put_file_contents($cat_file, $content))
    return true;
  
  return false;
}

/**
 * Generiert eine Liste mit den Media einer Kategorie.
 * 
 * @param $category_id Id der Kategorie
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMediaList($category_id)
{
  global $REX;
  
  $query = 'SELECT filename FROM ' . OOMedia :: _getTableName() . ' WHERE category_id = ' . $category_id;
  $sql = rex_sql::factory();
  $sql->setQuery($query);
  
  $content = '<?php'."\n";
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $content .= '$REX[\'MEDIA\'][\'MEDIA_CAT_ID\']['. $category_id .']['. $i .'] = \''. $sql->getValue('filename') .'\';'."\n";
    $sql->next();
  }
  $content .= '?>';
  
  $list_file = $REX['INCLUDE_PATH']."/generated/files/$category_id.mlist";
  if (rex_put_file_contents($list_file, $content))
    return true;
  
  return false;
}

/**
 * Generiert eine Liste mit den Kindkategorien einer Kategorie.
 * 
 * @param $category_id Id der Kategorie
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMediaCategoryList($category_id)
{
  global $REX;
  
  $query = 'SELECT id, cast( name AS SIGNED ) AS sort FROM ' . OOMediaCategory :: _getTableName() . ' WHERE re_id = ' . $category_id . ' ORDER BY sort, name';
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);
  
  $content = '<?php'."\n";
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $content .='$REX[\'MEDIA\'][\'RE_CAT_ID\']['. $category_id .']['. $i .'] = \''. $sql->getValue('id') .'\';'."\n";
    $sql->next();
  }
  $content .= '?>';
  
  $list_file = $REX['INCLUDE_PATH']."/generated/files/$category_id.mclist";
  if (rex_put_file_contents($list_file, $content))
    return true;
  
  return false;
}

/**
 * Generiert eine Liste mit allen Media einer Dateiendung
 * 
 * @param $extension Dateiendung der zu generierenden Liste
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMediaExtensionList($extension)
{
  global $REX;
  
  $query = 'SELECT filename FROM ' . OOMedia :: _getTableName() . ' WHERE SUBSTRING(filename,LOCATE( ".",filename)+1) = "' . $extension . '"';
  $sql = rex_sql::factory();
  $sql->setQuery($query);
  
  $content = '<?php'."\n";
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $content .= '$REX[\'MEDIA\'][\'EXTENSION\'][\''. $extension .'\']['. $i .'] = \''. $sql->getValue('filename') .'\';'."\n";
    $sql->next();
  }
  $content .= '?>';
  
  $list_file = $REX['INCLUDE_PATH']."/generated/files/$extension.mextlist";
  if (rex_put_file_contents($list_file, $content))
    return true;
  
  return false;
}

/**
 * Löscht einen Ordner/Datei mit Unterordnern
 *
 * @param $file Zu löschender Ordner/Datei
 * @param $delete_folders Ordner auch löschen? false => nein, true => ja
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_deleteDir($file, $delete_folders = FALSE)
{
  $debug = FALSE;
  $state = TRUE;
  
  $file = rtrim($file, DIRECTORY_SEPARATOR);

  if (file_exists($file))
  {
    // Fehler unterdrücken, falls keine Berechtigung
    if (@ is_dir($file))
    {
      $handle = opendir($file);
      if (!$handle)
      {
        if($debug)
          echo "Unable to open dir '$file'<br />\n";
          
        return FALSE;
      }

      while ($filename = readdir($handle))
      {
        if ($filename == '.' || $filename == '..')
        {
          continue;
        }

        if (!rex_deleteDir($file.DIRECTORY_SEPARATOR.$filename, $delete_folders))
        {
          $state = FALSE;
        }
      }
      closedir($handle);

      if ($state !== TRUE)
      {
        return FALSE;
      }
      

      // Ordner auch löschen?
      if ($delete_folders)
      {
        // Fehler unterdrücken, falls keine Berechtigung
        if (!@ rmdir($file))
        {
          if($debug)
            echo "Unable to delete folder '$file'<br />\n";
            
          return FALSE;
        }
      }
    }
    else
    {
      // Datei löschen
      // Fehler unterdrücken, falls keine Berechtigung
      if (!@ unlink($file))
      {
        if($debug)
          echo "Unable to delete file '$file'<br />\n";
            
        return FALSE;
      }
    }
  }
  else
  {
    if($debug)
      echo "file '$file'not found!<br />\n";
    // Datei/Ordner existiert nicht
    return FALSE;
  }

  return TRUE;
}



/**
 * Lösch allen Datei in einem Ordner
 *
 * @param $file Pfad zum Ordner
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_deleteFiles($file)
{
  $debug = FALSE;
  
  $file = rtrim($file, DIRECTORY_SEPARATOR);

  if (file_exists($file))
  {
    // Fehler unterdrücken, falls keine Berechtigung
    if (@ is_dir($file))
    {
      $handle = opendir($file);
      if (!$handle)
      {
        if($debug)
          echo "Unable to open dir '$file'<br />\n";
          
        return FALSE;
      }

      while ($filename = readdir($handle))
      {
        if ($filename == '.' || $filename == '..')
        {
          continue;
        }
        
	      if (!@ unlink($file))
	      {
	        if($debug)
	          echo "Unable to delete file '$file'<br />\n";
	            
	        return FALSE;
	      }
    
      }
      closedir($handle);     
    }
    else
    {
      // Datei löschen
      // Fehler unterdrücken, falls keine Berechtigung
    }
  }
  else
  {
    if($debug)
      echo "file '$file'not found!<br />\n";
    // Datei/Ordner existiert nicht
    return FALSE;
  }

  return TRUE;
}

/**
 * Kopiert eine Ordner von $srcdir nach $dstdir
 * 
 * @param $srcdir Zu kopierendes Verzeichnis
 * @param $dstdir Zielpfad
 * @param $startdir Pfad ab welchem erst neue Ordner generiert werden
 * 
 * @return TRUE bei Erfolg, FALSE bei Fehler
 */
function rex_copyDir($srcdir, $dstdir, $startdir = "")
{
  global $REX;
  
  $debug = FALSE;
  $state = TRUE;
  
  if(!is_dir($dstdir))
  {
    $dir = '';
    foreach(explode(DIRECTORY_SEPARATOR, $dstdir) as $dirPart)
    {
      $dir .= $dirPart . DIRECTORY_SEPARATOR;
      if(strpos($startdir,$dir) !== 0 && !is_dir($dir))
      {
        if($debug)
          echo "Create dir '$dir'<br />\n";
          
        mkdir($dir);
        chmod($dir, $REX['DIRPERM']);
      }
    }
  }
  
  if($curdir = opendir($srcdir))
  {
    while($file = readdir($curdir))
    {
      if($file != '.' && $file != '..' && $file != '.svn')
      {
        $srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;    
        $dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;    
        if(is_file($srcfile))
        {
          $isNewer = TRUE;
          if(is_file($dstfile))
          {
            $isNewer = (filemtime($srcfile) - filemtime($dstfile)) > 0;
          }
            
          if($isNewer)
          {
            if($debug)
              echo "Copying '$srcfile' to '$dstfile'...";
            if(copy($srcfile, $dstfile))
            {
              touch($dstfile, filemtime($srcfile));
              chmod($dstfile, $REX['FILEPERM']);
              if($debug)
                echo "OK<br />\n";
            }
            else
            {
              if($debug)
               echo "Error: File '$srcfile' could not be copied!<br />\n";
              return FALSE;
            }
          }
        }
        else if(is_dir($srcfile))
        {
          $state = rex_copyDir($srcfile, $dstfile, $startdir) && $state;
        }
      }
    }
    closedir($curdir);
  }
  return $state;
}

// ----------------------------------------- CLANG

/**
 * Löscht eine Clang
 *
 * @param $id Zu löschende ClangId
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_deleteCLang($clang)
{
  global $REX;

  if ($clang == 0 || !isset($REX['CLANG'][$clang]))
    return FALSE;

  $clangName = $REX['CLANG'][$clang];
  unset ($REX['CLANG'][$clang]);

  $del = rex_sql::factory();
  $del->setQuery("delete from ".$REX['TABLE_PREFIX']."article where clang='$clang'");
  $del->setQuery("delete from ".$REX['TABLE_PREFIX']."article_slice where clang='$clang'");
  $del->setQuery("delete from ".$REX['TABLE_PREFIX']."clang where id='$clang'");
  
  // ----- EXTENSION POINT
  rex_register_extension_point('CLANG_DELETED','',
    array (
      'id' => $clang,
      'name' => $clangName,
    )
  );

  rex_generateAll();
  
  return TRUE;
}

/**
 * Erstellt eine Clang
 *
 * @param $id   Id der Clang
 * @param $name Name der Clang
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_addCLang($id, $name)
{
  global $REX;
  
  if(isset($REX['CLANG'][$id])) return FALSE;

  $REX['CLANG'][$id] = $name;
  $file = $REX['INCLUDE_PATH']."/clang.inc.php";
  rex_replace_dynamic_contents($file, "\$REX['CLANG'] = ". var_export($REX['CLANG'], TRUE) .";\n");
  
  $firstLang = rex_sql::factory();
  $firstLang->setQuery("select * from ".$REX['TABLE_PREFIX']."article where clang='0'");
  $fields = $firstLang->getFieldnames();

  $newLang = rex_sql::factory();
  // $newLang->debugsql = 1;
  while($firstLang->hasNext())
  {
    $newLang->setTable($REX['TABLE_PREFIX']."article");

    foreach($fields as $key => $value)
    {
      if ($value == 'pid')
        echo ''; // nix passiert
      else
        if ($value == 'clang')
          $newLang->setValue('clang', $id);
        else
          if ($value == 'status')
            $newLang->setValue('status', '0'); // Alle neuen Artikel offline
      else
        $newLang->setValue($value, $firstLang->escape($firstLang->getValue($value)));
    }

    $newLang->insert();
    $firstLang->next();
  }
  $firstLang->freeResult();

  $newLang = rex_sql::factory();
  $newLang->setQuery("insert into ".$REX['TABLE_PREFIX']."clang set id='$id',name='$name'");

  // ----- EXTENSION POINT
  rex_register_extension_point('CLANG_ADDED','',array ('id' => $id, 'name' => $name));
  
  return TRUE;
}

/**
 * Ändert eine Clang
 *
 * @param $id   Id der Clang
 * @param $name Name der Clang
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_editCLang($id, $name)
{
  global $REX;
  
  if(!isset($REX['CLANG'][$id])) return false;

  $REX['CLANG'][$id] = $name;
  $file = $REX['INCLUDE_PATH']."/clang.inc.php";
  rex_replace_dynamic_contents($file, "\$REX['CLANG'] = ". var_export($REX['CLANG'], TRUE) .";\n");

  $edit = rex_sql::factory();
  $edit->setQuery("update ".$REX['TABLE_PREFIX']."clang set name='$name' where id='$id'");

  // ----- EXTENSION POINT
  rex_register_extension_point('CLANG_UPDATED','',array ('id' => $id, 'name' => $name));
  
  return TRUE;
}

/**
 * Schreibt Addoneigenschaften in die Datei include/addons.inc.php
 * 
 * @param array Array mit den Namen der Addons aus dem Verzeichnis addons/
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateAddons($ADDONS)
{
  global $REX;
  natsort($ADDONS);

  $content = "";
  foreach ($ADDONS as $addon)
  {
    if (!OOAddon :: isInstalled($addon))
      OOAddon::setProperty($addon, 'install', 0);

    if (!OOAddon :: isActivated($addon))
      OOAddon::setProperty($addon, 'status', 0);

    foreach(array('install', 'status') as $prop)
    {
      $content .= sprintf(
        "\$REX['ADDON']['%s']['%s'] = '%d';\n",
        $prop,
        $addon,
        OOAddon::getProperty($addon, $prop)
      );
    }
    $content .= "\n";      
  }

  // Da dieser Funktion öfter pro request aufgerufen werden kann,
  // hier die caches löschen
  clearstatcache();

  $file = $REX['INCLUDE_PATH']."/addons.inc.php";
  if(rex_replace_dynamic_contents($file, $content) === FALSE)
  {
    return 'Datei "'.$file.'" hat keine Schreibrechte';
  }
  return TRUE;
}

/**
 * Schreibt Plugineigenschaften in die Datei include/plugins.inc.php
 * 
 * @param array Array mit den Namen der Plugins aus dem Verzeichnis addons/plugins
 * 
 * @return TRUE bei Erfolg, sonst eine Fehlermeldung
 */
function rex_generatePlugins($PLUGINS)
{
  global $REX;
  
  $content = "";
  foreach ($PLUGINS as $addon => $_plugins)
  {
    foreach($_plugins as $plugin)
    {
      if (!OOPlugin :: isInstalled($addon, $plugin))
        OOPlugin::setProperty($addon, $plugin, 'install', 0);
  
      if (!OOPlugin :: isActivated($addon, $plugin))
        OOPlugin::setProperty($addon, $plugin, 'status', 0);
  
      foreach(array('install', 'status') as $prop)
      {
        $content .= sprintf(
          "\$REX['ADDON']['plugins']['%s']['%s']['%s'] = '%d';\n",
          $addon,
          $prop,
          $plugin,
          OOPlugin::getProperty($addon, $plugin, $prop)
        );
      }
      $content .= "\n";
    }
  }

  // Da dieser Funktion öfter pro request aufgerufen werden kann,
  // hier die caches löschen
  clearstatcache();

  $file = $REX['INCLUDE_PATH']."/plugins.inc.php";
  if(rex_replace_dynamic_contents($file, $content) === false)
  {
    return 'Datei "'.$file.'" hat keine Schreibrechte';
  }
  return TRUE;
}

/**
 * Schreibt Spracheigenschaften in die Datei include/clang.inc.php
 * 
 * @return TRUE bei Erfolg, sonst eine Fehlermeldung
 */
function rex_generateClang()
{
  global $REX;
  
  $lg = rex_sql::factory();
  $lg->setQuery("select * from ".$REX['TABLE_PREFIX']."clang order by id");
  
  $REX['CLANG'] = array();
  while($lg->hasNext())
  {
    $REX['CLANG'][$lg->getValue("id")] = $lg->getValue("name"); 
    $lg->next();
  }
  
  $file = $REX['INCLUDE_PATH']."/clang.inc.php";
  if(rex_replace_dynamic_contents($file, "\$REX['CLANG'] = ". var_export($REX['CLANG'], TRUE) .";\n") === FALSE)
  {
    return 'Datei "'.$file.'" hat keine Schreibrechte';
  }
  return TRUE;
}

/**
 * Generiert den TemplateCache im Filesystem
 * 
 * @param $template_id Id des zu generierenden Templates
 * 
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateTemplate($template_id)
{
  global $REX;

  $sql = rex_sql::factory();
  $qry = 'SELECT * FROM '. $REX['TABLE_PREFIX']  .'template WHERE id = '.$template_id;
  $sql->setQuery($qry);

  if($sql->getRows() == 1)
  {
    $templatesDir = rex_template::getTemplatesDir();
    $templateFile = rex_template::getFilePath($template_id);

  	$content = $sql->getValue('content');
  	foreach($REX['VARIABLES'] as $var)
  	{
  		$content = $var->getTemplate($content);
  	}
    if(rex_put_file_contents($templateFile, $content) !== FALSE)
    {
      return TRUE;
    }
    else
    {
      trigger_error('Unable to generate template '. $template_id .'!', E_USER_ERROR);

      if(!is_writable())
        trigger_error('directory "'. $templatesDir .'" is not writable!', E_USER_ERROR);
    }
  }
  else
  {
    trigger_error('Template with id "'. $template_id .'" does not exist!', E_USER_ERROR);
  }

  return FALSE;
}

// ----------------------------------------- generate helpers

/**
 * Escaped einen String
 *
 * @param $string Zu escapender String
 */
function rex_addslashes($string, $flag = '\\\'\"')
{
  if ($flag == '\\\'\"')
  {
    $string = str_replace('\\', '\\\\', $string);
    $string = str_replace('\'', '\\\'', $string);
    $string = str_replace('"', '\"', $string);
  }elseif ($flag == '\\\'')
  {
    $string = str_replace('\\', '\\\\', $string);
    $string = str_replace('\'', '\\\'', $string);
  }
  return $string;
}