<?php

/**
 * Funktionensammlung für die Strukturverwaltung
 *
 * @package redaxo4
 * @version svn:$Id$
 */

/**
 * Erstellt eine neue Kategorie
 * 
 * @param int   $category_id KategorieId in der die neue Kategorie erstellt werden soll
 * @param array $data        Array mit den Daten der Kategorie
 * 
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_addCategory($category_id, $data)
{
  global $REX, $I18N;

  $success = false;
  $message = '';

  if(!is_array($data))
    trigger_error('Expecting $data to be an array!', E_USER_ERROR);

  $startpageTemplates = array();
  if ($category_id != "")
  {
    // TemplateId vom Startartikel der jeweiligen Sprache vererben
    $sql = rex_sql::factory();
    // $sql->debugsql = 1;
    $sql->setQuery("select clang,template_id from ".$REX['TABLE_PREFIX']."article where id=$category_id and startpage=1");
    for ($i = 0; $i < $sql->getRows(); $i++, $sql->next())
    {
      $startpageTemplates[$sql->getValue("clang")] = $sql->getValue("template_id");
    }
  }

  if(isset($data['catprior']))
  {
    if($data['catprior'] <= 0)
      $data['catprior'] = 1;
  }

  if(!isset($data['name']))
  {
    $data['name'] = $data['catname'];
  }

  if(!isset($data['status']))
  {
    $data['status'] = 0;
  }

  // Alle Templates der Kategorie
  $templates = OOCategory::getTemplates($category_id);
  // Kategorie in allen Sprachen anlegen
  $AART = rex_sql::factory();
  foreach($REX['CLANG'] as $key => $val)
  {
    $template_id = $REX['DEFAULT_TEMPLATE_ID'];
    if(isset ($startpageTemplates[$key]) && $startpageTemplates[$key] != '')
      $template_id = $startpageTemplates[$key];

    // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
    // oder leer setzen.
    if(!isset($templates[$template_id]))
    {
      $template_id = 0;
    	if(count($templates)>0)
    	 $template_id = key($templates);
    }

    $AART->setTable($REX['TABLE_PREFIX'].'article');
    if (!isset ($id))
      $id = $AART->setNewId('id');
    else
      $AART->setValue('id', $id);

    $AART->setValue('clang', $key);
    $AART->setValue('template_id', $template_id);
    $AART->setValue('name', $data['name']);
    $AART->setValue('catname', $data['catname']);
    $AART->setValue('attributes', '');
    $AART->setValue('catprior', $data['catprior']);
    $AART->setValue('re_id', $category_id);
    $AART->setValue('prior', 1);
    $AART->setValue('path', $data['path']);
    $AART->setValue('startpage', 1);
    $AART->setValue('status', $data['status']);
    $AART->addGlobalUpdateFields();
    $AART->addGlobalCreateFields();
    
    if($AART->insert())
    {
      // ----- PRIOR
      if(isset($data['catprior']))
      {
        rex_newCatPrio($category_id, $key, 0, $data['catprior']);
      }
      
      // ----- EXTENSION POINT
      // Objekte clonen, damit diese nicht von der extension veraendert werden koennen
      $message = rex_register_extension_point('CAT_ADDED', $message,
        array (
          'category' => clone($AART),
          'id' => $id,
          're_id' => $category_id,
          'clang' => $key,
          'name' => $data['catname'],
          'prior' => $data['catprior'],
          'path' => $data['path'],
          'status' => $data['status'],
          'article' => clone($AART),
          'data' => $data,
        )
      );
      
      $message = $I18N->msg("category_added_and_startarticle_created");
      $success = true;
    }
    else
    {
      $message = $AART->getError();
    }
  }
  
  return array($success, $message);
}

/**
 * Bearbeitet einer Kategorie
 * 
 * @param int   $category_id Id der Kategorie die verändert werden soll
 * @param int   $clang       Id der Sprache
 * @param array $data        Array mit den Daten der Kategorie 
 * 
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_editCategory($category_id, $clang, $data)
{
  global $REX, $I18N;

  $success = false;
  $message = '';

  if(!is_array($data))
    trigger_error('Expecting $data to be an array!', E_USER_ERROR);

  // --- Kategorie mit alten Daten selektieren
  $thisCat = rex_sql::factory();
  $thisCat->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'article WHERE startpage=1 and id='.$category_id.' and clang='. $clang);

  // --- Kategorie selbst updaten
  $EKAT = rex_sql::factory();
  $EKAT->setTable($REX['TABLE_PREFIX']."article");
  $EKAT->setWhere("id=$category_id AND startpage=1 AND clang=$clang");
  $EKAT->setValue('catname', $data['catname']);
  $EKAT->setValue('catprior', $data['catprior']);
  $EKAT->setValue('path', $data['path']);
  $EKAT->addGlobalUpdateFields();

  if($EKAT->update())
  {
    // --- Kategorie Kindelemente updaten
    if(isset($data['catname']))
    {
      $ArtSql = rex_sql::factory();
      $ArtSql->setQuery('SELECT id FROM '.$REX['TABLE_PREFIX'].'article WHERE re_id='.$category_id .' AND startpage=0 AND clang='.$clang);

      $EART = rex_sql::factory();
      for($i = 0; $i < $ArtSql->getRows(); $i++)
      {
        $EART->setTable($REX['TABLE_PREFIX'].'article');
        $EART->setWhere('id='. $ArtSql->getValue('id') .' AND startpage=0 AND clang='.$clang);
        $EART->setValue('catname', $data['catname']);
        $EART->addGlobalUpdateFields();

        if($EART->update())
        {
          rex_deleteCacheArticle($ArtSql->getValue('id'), $clang);
        }
        else
        {
          $message .= $EART->getError();
        }

        $ArtSql->next();
      }
    }

    // ----- PRIOR
    if(isset($data['catprior']))
    {
      $re_id = $thisCat->getValue('re_id');
      $old_prio = $thisCat->getValue('catprior');

      if($data['catprior'] <= 0)
        $data['catprior'] = 1;

      rex_newCatPrio($re_id, $clang, $data['catprior'], $old_prio);
    }

    $message = $I18N->msg('category_updated');

    rex_deleteCacheArticle($category_id, $clang);
    
    // ----- EXTENSION POINT
    // Objekte clonen, damit diese nicht von der extension veraendert werden koennen
    $message = rex_register_extension_point('CAT_UPDATED', $message,
      array (
        'id' => $category_id,

        'category' => clone($EKAT),
        'category_old' => clone($thisCat),
        'article' => clone($EKAT),
        
        're_id' => $thisCat->getValue('re_id'),
        'clang' => $clang,
        'name' => $thisCat->getValue('catname'),
        'prior' => $thisCat->getValue('catprior'),
        'path' => $thisCat->getValue('path'),
        'status' => $thisCat->getValue('status'),
        
        'data' => $data,
      )
    );

    $success = true;
  }
  else
  {
    $message = $EKAT->getError();
  }

  return array($success, $message);
}

/**
 * Löscht eine Kategorie und reorganisiert die Prioritäten verbleibender Geschwister-Kategorien
 * 
 * @param int $category_id Id der Kategorie die gelöscht werden soll
 * 
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_deleteCategoryReorganized($category_id)
{
  global $REX, $I18N;

	$return = array();
  $return['state'] = FALSE;
  $return['message'] = '';
  
  $clang = 0;

  $thisCat = rex_sql::factory();
  $thisCat->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'article WHERE id='.$category_id.' and clang='. $clang);

  // Prüfen ob die Kategorie existiert
  if ($thisCat->getRows() == 1)
  {
    $KAT = rex_sql::factory();
    $KAT->setQuery("select * from ".$REX['TABLE_PREFIX']."article where re_id='$category_id' and clang='$clang' and startpage=1");
    // Prüfen ob die Kategorie noch Unterkategorien besitzt
    if ($KAT->getRows() == 0)
    {
      $KAT->setQuery("select * from ".$REX['TABLE_PREFIX']."article where re_id='$category_id' and clang='$clang' and startpage=0");
      // Prüfen ob die Kategorie noch Artikel besitzt (ausser dem Startartikel)
      if ($KAT->getRows() == 0)
      {
        $thisCat = rex_sql::factory();
        $thisCat->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'article WHERE id='.$category_id);
        
        $re_id = $thisCat->getValue('re_id');
        $return = rex_deleteArticle($category_id);
        
        while($thisCat->hasNext())
        {
          $_clang = $thisCat->getValue('clang');
          
          // ----- PRIOR
          rex_newCatPrio($re_id, $_clang, 0, 1);
          
          // ----- EXTENSION POINT
          $return = rex_register_extension_point('CAT_DELETED', $return, array (
            'id'     => $category_id,
            're_id'  => $re_id,
            'clang'  => $_clang,
            'name'   => $thisCat->getValue('catname'),
            'prior'  => $thisCat->getValue('catprior'),
            'path'   => $thisCat->getValue('path'),
            'status' => $thisCat->getValue('status'),
          ));
          
          $thisCat->next();
        }

        $users = rex_sql::factory();
        $users->setQuery('UPDATE '. $REX['TABLE_PREFIX'] .'user SET rights = REPLACE(rights, "#csw['. $category_id .']#", "#")');

      }else
      {
        $return['message'] = $I18N->msg('category_could_not_be_deleted').' '.$I18N->msg('category_still_contains_articles');
      }
    }else
    {
      $return['message'] = $I18N->msg('category_could_not_be_deleted').' '.$I18N->msg('category_still_contains_subcategories');
    }
  }else
  {
    $return['message'] = $I18N->msg('category_could_not_be_deleted');
  }

  return array($return['state'],$return['message']);
}

/**
 * Ändert den Status der Kategorie
 * 
 * @param int       $category_id   Id der Kategorie die gelöscht werden soll
 * @param int       $clang         Id der Sprache
 * @param int|null  $status        Status auf den die Kategorie gesetzt werden soll, oder NULL wenn zum nächsten Status weitergeschaltet werden soll
 * 
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_categoryStatus($category_id, $clang, $status = null)
{
  global $REX, $I18N;

  $success = false;
  $message = '';
  $catStatusTypes = rex_categoryStatusTypes();

  $KAT = rex_sql::factory();
  $KAT->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id='$category_id' and clang=$clang and startpage=1");
  if ($KAT->getRows() == 1)
  {
    // Status wurde nicht von außen vorgegeben,
    // => zyklisch auf den nächsten Weiterschalten
    if(!$status)
      $newstatus = ($KAT->getValue('status') + 1) % count($catStatusTypes);
    else
      $newstatus = $status;

    $EKAT = rex_sql::factory();
    $EKAT->setTable($REX['TABLE_PREFIX'].'article');
    $EKAT->setWhere("id='$category_id' and clang=$clang and startpage=1");
    $EKAT->setValue("status", $newstatus);
    $EKAT->addGlobalCreateFields();

    if($EKAT->update())
    {
      $message = $I18N->msg('category_status_updated');
      rex_deleteCacheArticle($category_id, $clang);

      // ----- EXTENSION POINT
      $message = rex_register_extension_point('CAT_STATUS', $message, array (
        'id' => $category_id,
        'clang' => $clang,
        'status' => $newstatus
      ));

      $success = true;
    }
    else
    {
      $message = $EKAT->getError();
    }
  }
  else
  {
    $message = $I18N->msg("no_such_category");
  }

  return array($success, $message);
}

/**
 * Gibt alle Stati zurück, die für eine Kategorie gültig sind
 * 
 * @return array Array von Stati
 */
function rex_categoryStatusTypes()
{
  global $I18N;

  static $catStatusTypes;

  if(!$catStatusTypes)
  {
    $catStatusTypes = array(
      // Name, CSS-Class
      array($I18N->msg('status_offline'), 'rex-offline'),
      array($I18N->msg('status_online'), 'rex-online')
    );

    // ----- EXTENSION POINT
    $catStatusTypes = rex_register_extension_point('CAT_STATUS_TYPES', $catStatusTypes);
  }

  return $catStatusTypes;
}

/**
 * Erstellt einen neuen Artikel
 * 
 * @param array $data Array mit den Daten des Artikels
 * 
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_addArticle($data)
{
  global $REX, $I18N;

  $success = true;
  $message = '';

  if(!is_array($data))
    trigger_error('Expecting $data to be an array!', E_USER_ERROR);

  if(isset($data['prior']))
  {
    if($data['prior'] <= 0)
      $data['prior'] = 1;
  }

  $templates = OOCategory::getTemplates($data['category_id']);
  
  // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
  // oder leer setzen.
  if(!isset($templates[$data['template_id']]))
  {
    $data['template_id'] = 0;
    if(count($templates)>0)
     $data['template_id'] = key($templates);
  }
  
  $message = $I18N->msg('article_added');

  $AART = rex_sql::factory();
  foreach($REX['CLANG'] as $key => $val)
  {
    // ------- Kategorienamen holen
    $category = OOCategory::getCategoryById($data['category_id'], $key);
  
    $category_name = '';
    if($category)
      $category_name = addslashes($category->getName());
      
    $AART->setTable($REX['TABLE_PREFIX'].'article');
    if (!isset ($id) or !$id)
      $id = $AART->setNewId('id');
    else
      $AART->setValue('id', $id);
    $AART->setValue('name', $data['name']);
    $AART->setValue('catname', $category_name);
    $AART->setValue('attributes', '');
    $AART->setValue('clang', $key);
    $AART->setValue('re_id', $data['category_id']);
    $AART->setValue('prior', $data['prior']);
    $AART->setValue('path', $data['path']);
    $AART->setValue('startpage', 0);
    $AART->setValue('status', 0);
    $AART->setValue('template_id', $data['template_id']);
    $AART->addGlobalCreateFields();
    $AART->addGlobalUpdateFields();

    if($AART->insert())
    {
      // ----- PRIOR
      rex_newArtPrio($data['category_id'], $key, 0, $data['prior']);
    }
    else
    {
      $success = false;
      $message = $AART->getError();
    }
    
    // ----- EXTENSION POINT
    $message = rex_register_extension_point('ART_ADDED', $message,
      array (
        'id' => $id,
        'clang' => $key,
        'status' => 0,
        'name' => $data['name'],
        're_id' => $data['category_id'],
        'prior' => $data['prior'],
        'path' => $data['path'],
        'template_id' => $data['template_id'],
        'data' => $data,
      )
    );
  }

  return array($success, $message);
}

/**
 * Bearbeitet einen Artikel
 * 
 * @param int   $article_id  Id des Artikels der verändert werden soll
 * @param int   $clang       Id der Sprache
 * @param array $data        Array mit den Daten des Artikels 
 * 
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_editArticle($article_id, $clang, $data)
{
  global $REX, $I18N;

  $success = false;
  $message = '';

  if(!is_array($data))
    trigger_error('Expecting $data to be an array!', E_USER_ERROR);

  $templates = OOCategory::getTemplates($data['category_id']);
  
  // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
  // oder leer setzen.
  if(!isset($templates[$data['template_id']]))
  {
    $data['template_id'] = 0;
    if(count($templates)>0)
     $data['template_id'] = key($templates);
  }
    
  // Artikel mit alten Daten selektieren
  $thisArt = rex_sql::factory();
  $thisArt->setQuery('select * from '.$REX['TABLE_PREFIX'].'article where id='.$article_id.' and clang='. $clang);

  if(isset($data['prior']))
  {
    if($data['prior'] <= 0)
      $data['prior'] = 1;
  }

  $EA = rex_sql::factory();
  $EA->setTable($REX['TABLE_PREFIX']."article");
  $EA->setWhere("id='$article_id' and clang=$clang");
  $EA->setValue('name', $data['name']);
  $EA->setValue('template_id', $data['template_id']);
  $EA->setValue('prior', $data['prior']);
  $EA->addGlobalUpdateFields();

  if($EA->update())
  {
    $message = $I18N->msg('article_updated');
    
    // ----- PRIOR
    rex_newArtPrio($data['category_id'], $clang, $data['prior'], $thisArt->getValue('prior'));
    rex_deleteCacheArticle($article_id, $clang);
        
    // ----- EXTENSION POINT
    $message = rex_register_extension_point('ART_UPDATED', $message,
      array (
        'id' => $article_id,

				'article' => clone($EA),
				'article_old' => clone($thisArt),

        'status' => $thisArt->getValue('status'),
        'name' => $data['name'],
        'clang' => $clang,
        're_id' => $data['category_id'],
        'prior' => $data['prior'],
        'path' => $data['path'],
        'template_id' => $data['template_id'],
        
        'data' => $data,
      )
    );

    $success = true;
  }
  else
  {
    $message = $EA->getError();
  }

  return array($success, $message);
}

/**
 * Löscht einen Artikel und reorganisiert die Prioritäten verbleibender Geschwister-Artikel
 * 
 * @param int $article_id Id des Artikels die gelöscht werden soll
 * 
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_deleteArticleReorganized($article_id)
{
  global $REX;

  $return = array();
  $return['state'] = FALSE;
  $return['message'] = '';

  $Art = rex_sql::factory();
  $Art->setQuery('select * from '.$REX['TABLE_PREFIX'].'article where id='.$article_id.' and startpage=0');

  if ($Art->getRows() == count($REX['CLANG']))
  {
    $return = rex_deleteArticle($article_id);
    $re_id = $Art->getValue("re_id");

    foreach($REX['CLANG'] as $clang => $clang_name)
    {
      // ----- PRIOR
      rex_newArtPrio($Art->getValue("re_id"), $clang, 0, 1);
      
      // ----- EXTENSION POINT
      $return = rex_register_extension_point('ART_DELETED', $return,
        array (
          "id"          => $article_id,
          "clang"       => $clang,
          "re_id"       => $re_id,
          'name'        => $Art->getValue('name'),
          'status'      => $Art->getValue('status'),
          'prior'       => $Art->getValue('prior'),
          'path'        => $Art->getValue('path'),
          'template_id' => $Art->getValue('template_id'),
        )
      );
      
      $Art->next();
    }
  }
  return array($return['state'],$return['message']);
}

/**
 * Ändert den Status des Artikels
 * 
 * @param int       $article_id Id des Artikels die gelöscht werden soll
 * @param int       $clang      Id der Sprache
 * @param int|null  $status     Status auf den der Artikel gesetzt werden soll, oder NULL wenn zum nächsten Status weitergeschaltet werden soll
 * 
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_articleStatus($article_id, $clang, $status = null)
{
  global $REX, $I18N;

  $success = false;
  $message = '';
  $artStatusTypes = rex_articleStatusTypes();

  $GA = rex_sql::factory();
  $GA->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id='$article_id' and clang=$clang");
  if ($GA->getRows() == 1)
  {
    // Status wurde nicht von außen vorgegeben,
    // => zyklisch auf den nächsten Weiterschalten
    if(!$status)
      $newstatus = ($GA->getValue('status') + 1) % count($artStatusTypes);
    else
      $newstatus = $status;

    $EA = rex_sql::factory();
    $EA->setTable($REX['TABLE_PREFIX']."article");
    $EA->setWhere("id='$article_id' and clang=$clang");
    $EA->setValue('status', $newstatus);
    $EA->addGlobalUpdateFields();

    if($EA->update())
    {
      $message = $I18N->msg('article_status_updated');
      rex_deleteCacheArticle($article_id, $clang);

      // ----- EXTENSION POINT
      $message = rex_register_extension_point('ART_STATUS', $message, array (
        'id' => $article_id,
        'clang' => $clang,
        'status' => $newstatus
      ));

      $success = true;
    }
    else
    {
      $message = $EA->getError();
    }
  }
  else
  {
    $message = $I18N->msg("no_such_category");
  }

  return array($success, $message);
}

/**
 * Gibt alle Stati zurück, die für einen Artikel gültig sind
 * 
 * @return array Array von Stati
 */
function rex_articleStatusTypes()
{
  global $I18N;

  static $artStatusTypes;

  if(!$artStatusTypes)
  {
    $artStatusTypes = array(
      // Name, CSS-Class
      array($I18N->msg('status_offline'), 'rex-offline'),
      array($I18N->msg('status_online'), 'rex-online')
    );

    // ----- EXTENSION POINT
    $artStatusTypes = rex_register_extension_point('ART_STATUS_TYPES', $artStatusTypes);
  }

  return $artStatusTypes;
}