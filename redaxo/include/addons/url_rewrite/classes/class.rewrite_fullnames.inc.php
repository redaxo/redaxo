<?php

define('FULLNAMES_PATHLIST', $REX['INCLUDE_PATH'].'/generated/files/pathlist.php');

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 */

/**
 * URL Fullnames Rewrite Anleitung:
 *
 *   1) .htaccess file in das root verzeichnis:
 *     RewriteEngine On
 *     #RewriteCond %{HTTP_HOST} ^domain.tld [NC]
 *     #RewriteRule ^(.*)$ http://www.domain.tld/$1 [L,R=301]
 *     #RewriteBase /
 *     RewriteCond %{REQUEST_FILENAME} !-f
 *     RewriteCond %{REQUEST_FILENAME} !-d
 *     RewriteCond %{REQUEST_FILENAME} !-l
 *     RewriteCond %{REQUEST_URI} !^redaxo/.*
 *     RewriteCond %{REQUEST_URI} !^files/.*
 *     RewriteRule ^(.*)$ index.php?%{QUERY_STRING} [L]
 *
 *   2) .htaccess file in das redaxo/ verzeichnis:
 *     RewriteEngine Off
 *
 *   3) im Template folgende Zeile AM ANFANG des <head> ergänzen:
 *   <base href="http://www.meine_domain.de/pfad/zum/frontend" />
 *
 *   4) Specials->Regenerate All starten
 *
 *   5) ggf. Rewrite-Base der .htaccess Datei anpassen
 *
 * @author staab[at]public-4u[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @author office[at]vscope[dot]at Wolfgang Huttegger
 * @author <a href="http://www.vscope.at/">vscope new media</a>
 *
 * @author rn[at]gn2-netwerk[dot]de Rüdiger Nitzsche
 * @author <a href="http://www.gn2-netwerk.de/">GN2 Netwerk</a>
 */

class myUrlRewriter extends rexUrlRewriter
{
  var $use_levenshtein;
  var $use_params_rewrite;

  // Konstruktor
  function myUrlRewriter($use_levenshtein = false, $use_params_rewrite = false)
  {
    $this->use_levenshtein = $use_levenshtein;
    $this->use_params_rewrite = $use_params_rewrite;

    // Parent Konstruktor aufrufen
    parent::rexUrlRewriter();
  }

  // Parameter aus der URL für das Script verarbeiten
  function prepare()
  {
    global $REX, $REXPATH;

    $article_id = -1;
    $clang = $REX["CUR_CLANG"];

    if(!file_exists(FULLNAMES_PATHLIST))
       rex_rewriter_generate_pathnames(array());

    // REXPATH wird auch im Backend benötigt, z.B. beim bearbeiten von Artikeln
    require_once (FULLNAMES_PATHLIST);
    
    if(!$REX['REDAXO'])
    {
      $script_path = str_replace(' ', '%20', dirname($_SERVER['PHP_SELF']));

      // ANDERE DIR_SEP ALS "/" ERSETZEN (WIN BACKSLASHES)
      $script_path = str_replace(DIRECTORY_SEPARATOR, '/', $script_path);

      $length = strlen($script_path);
      $path = substr($_SERVER['REQUEST_URI'], $length);
      
      // Serverdifferenzen angleichen
      if ($path{0}=='/')
        $path = substr($path, 1);
        
      // Parameter zählen nicht zum Pfad -> abschneiden
      if(($pos = strpos($path, '?')) !== false)
        $path = substr($path, 0, $pos);
  
      // Anker zählen nicht zum Pfad -> abschneiden
      if(($pos = strpos($path, '#')) !== false)
        $path = substr($path, 0, $pos);
  
      if (($path == '') || ($path == $REX['FRONTEND_FILE']))
      {
        $this->setArticleId($REX['START_ARTICLE_ID']);
        return true;
      }
  
      // konvertiert params zu GET/REQUEST Variablen
      if($this->use_params_rewrite)
      {
        if(strstr($path,'/+/'))
        {
          $tmp = explode('/+/',$path);
          $path = $tmp[0].'/';
          $vars = explode('/',$tmp[1]);
          for($c=0;$c<count($vars);$c+=2)
          {
            if($vars[$c]!='')
            {
              $_GET[$vars[$c]] = $vars[$c+1];
              $_REQUEST[$vars[$c]] = $vars[$c+1];
            }
          }
        }
      }  

			// aktuellen pfad mit pfadarray vergleichen

      foreach ($REXPATH as $key => $var)
      {
        foreach ($var as $k => $v)
        {
          if ($path == $v)
          {
            $article_id = $key;
            $clang = $k;
          }
        }
      }
      
      // Check Clang StartArtikel
      if ($article_id == -1)
      {
        foreach ($REX['CLANG'] as $key => $var)
        {
          if ($var.'/' == $path || $var == $path)
          {
            $clang = $key;
          }
        }
      }
      
 			// Check levenshtein
      if ($this->use_levenshtein && $article_id == -1)
      {
        foreach ($REXPATH as $key => $var)
        {
          foreach ($var as $k => $v)
          {
            $levenshtein[levenshtein($path, $v)] = $key.'#'.$k;
          }
        }
  
        ksort($levenshtein);
        $best = explode('#', array_shift($levenshtein));
        
        $article_id = $best[0];
        $clang = $best[1];
      
      }elseif($article_id == -1)
      {
				// ----- EXTENSION POINT
				$article_info = rex_register_extension_point('URL_REWRITE_ARTICLE_ID_NOT_FOUND', '' );
				if (isset($article_info['article_id']) && $article_info['article_id'] > -1)
				{
					$article_id = $article_info['article_id'];

					if (isset($article_info['clang']) && $article_info['clang'] > -1)
					{
						$clang = $article_info['clang'];
					}
				}
				
				// Nochmals abfragen wegen EP
				if($article_id == -1)
	      {
					// Damit auch die "index.php?article_id=xxx" Aufrufe funktionieren
					if(rex_request('article_id', 'int', 0) > 0)
						$article_id = $REX['ARTICLE_ID'];
					else
						$article_id = $REX['NOTFOUND_ARTICLE_ID'];
				}
      }
      
      $this->setArticleId($article_id,$clang);
    }
  }
  
  
  /*private*/ function setArticleId($art_id, $clang_id = -1)
  {
    global $REX;
    $REX['ARTICLE_ID'] = $art_id;
    if($clang_id > -1)
    	$REX['CUR_CLANG'] = $clang_id;
  }

  // Url neu schreiben
  function rewrite($params)
  {
    // Url wurde von einer anderen Extension bereits gesetzt
    if($params['subject'] != '')
  		return $params['subject'];

    global $REX, $REXPATH;
    
    $id         = $params['id'];
    $name       = $params['name'];
    $clang      = $params['clang'];
    $divider    = $params['divider'];
    $urlparams  = $params['params'];

    // params umformatieren neue Syntax suchmaschienen freundlich
    if($this->use_params_rewrite)
    {
      $urlparams = str_replace($divider,'/',$urlparams);
      $urlparams = str_replace('=','/',$urlparams);
      $urlparams = $urlparams == '' ? '' : '/'.'+'.$urlparams.'/';
    }else
    {
      $urlparams = $urlparams == '' ? '' : '?'.$urlparams;
    }

    $urlparams = str_replace('/amp;','/',$urlparams);
    $url = $REXPATH[$id][$clang].$urlparams;

    $baseDir = str_replace(' ', '%20', dirname($_SERVER['PHP_SELF']));
    // ANDERE DIR_SEP ALS "/" ERSETZEN (WIN BACKSLASHES)
    $baseDir = str_replace(DIRECTORY_SEPARATOR, '/', $baseDir);
    if (substr($baseDir, -1) !="/" ) 
      $baseDir .= "/";

    if($REX['REDAXO'])
    {
      $baseDir = '';
    }

    // immer absolute Urls erzeugen, da relative mit rex_redirect() nicht funktionieren
    // da dieser den <base href="" /> nicht kennt.
    return $baseDir .$url;
  }
}

/*
 * Allgemeine EP Definitionen
 */

if ($REX['REDAXO'])
{
  // Die Pathnames bei folgenden Extension Points aktualisieren
  $extension = 'rex_rewriter_generate_pathnames';
  $extensionPoints = array(
    'CAT_ADDED',   'CAT_UPDATED',   'CAT_DELETED',
    'ART_ADDED',   'ART_UPDATED',   'ART_DELETED',
    'ART_TO_CAT',  'CAT_TO_ART',    'ART_TO_STARTPAGE',
    'CLANG_ADDED', 'CLANG_UPDATED', 'CLANG_DELETED',
    'ALL_GENERATED', 'ART_META_UPDATED');

  foreach($extensionPoints as $extensionPoint)
  {
    rex_register_extension($extensionPoint, $extension);
  }
}


/**
 * rex_rewriter_generate_pathnames
 * generiert die Pathlist, abhŠngig von Aktion
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 */

function rex_rewriter_generate_pathnames($params)
{
  global $REX, $REXPATH;

  if(file_exists(FULLNAMES_PATHLIST))
  {
    require_once (FULLNAMES_PATHLIST);
  }
  
  if(!isset($REXPATH)) 
    $REXPATH = array();
  
  if(!isset($params['extension_point']))
    $params['extension_point'] = '';
    
  $where = '';
  switch($params['extension_point'])
  {
    // ------- sprachabhängig, einen artikel aktualisieren
    case 'CAT_DELETED':
    case 'ART_DELETED':
      unset($REXPATH[$params['id']]);
      break;
    case 'CAT_ADDED':
    case 'CAT_UPDATED':
    case 'ART_ADDED':
    case 'ART_UPDATED':
    case 'ART_TO_CAT':
    case 'CAT_TO_ART':
    case 'ART_META_UPDATED':
      $where = '(id='. $params['id'] .' AND clang='. $params['clang'] .') OR (path LIKE "%|'. $params['id'] .'|%" AND clang='. $params['clang'] .')';
      break;
    // ------- alles aktualisieren
    case 'CLANG_ADDED':
    case 'CLANG_UPDATED':
    case 'CLANG_DELETED':
    case 'ART_TO_STARTPAGE':
    case 'ALL_GENERATED':
    default:
      $REXPATH = array();
      $where = '1=1';
			break;
  }
  
  if($where != '')
  {
    $db = rex_sql::factory();
    // $db->debugsql=true;
    $db->setQuery('SELECT id,clang,path,startpage FROM '. $REX['TABLE_PREFIX'] .'article WHERE '. $where.' and revision=0');
    
    while($db->hasNext())
    {
      $clang = $db->getValue('clang');
      $pathname = '';
      if (count($REX['CLANG']) > 1)
      {
        $pathname = $REX['CLANG'][$clang].'/';
      }
      
      // pfad über kategorien bauen
      $path = trim($db->getValue('path'), '|');
      if($path != '')
      {
        $path = explode('|', $path);
        foreach ($path as $p)
        {
          $ooc = OOCategory::getCategoryById($p, $clang);
          $name = $ooc->getName();
          unset($ooc); // speicher freigeben
          
          $pathname = rex_rewriter_appendToPath($pathname, $name);
        }
      }
      
      $ooa = OOArticle::getArticleById($db->getValue('id'), $clang);
      if($ooa->isStartArticle())
      {
        $ooc = $ooa->getCategory();
        $catname = $ooc->getName();
        unset($ooc); // speicher freigeben
        $pathname = rex_rewriter_appendToPath($pathname, $catname);
      }
      
      // eigentlicher artikel anhängen
      $name = $ooa->getName();
      unset($ooa); // speicher freigeben
      $pathname = rex_rewriter_appendToPath($pathname, $name);
      
      $pathname = substr($pathname,0,strlen($pathname)-1).'.html';
      $REXPATH[$db->getValue('id')][$db->getValue('clang')] = $pathname;
      
      $db->next();
    }
  }
  
  rex_put_file_contents(FULLNAMES_PATHLIST, "<?php\n\$REXPATH = ". var_export($REXPATH, true) .";\n");
}

function rex_rewriter_appendToPath($path, $name)
{
  if ($name != '')
  {
    $name = strtolower(rex_parse_article_name($name));
    $path .= $name.'/';
  }
  return $path;
}
