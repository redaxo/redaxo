<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5.2
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
 *     RewriteCond %{REQUEST_URI} !^media/.*
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
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @author office[at]vscope[dot]at Wolfgang Huttegger
 * @author <a href="http://www.vscope.at/">vscope new media</a>
 *
 * @author rn[at]gn2-netwerk[dot]de Rüdiger Nitzsche
 * @author <a href="http://www.gn2-netwerk.de/">GN2 Netwerk</a>
 */

class rex_url_rewriter_fullnames extends rex_url_rewriter
{
  private
    $PATHLIST,

    $use_levenshtein,
    $use_params_rewrite;

  // Konstruktor
  public function __construct($use_levenshtein = false, $use_params_rewrite = false)
  {
    $this->PATHLIST = rex_path::addonCache('url_rewrite', 'pathlist.php');

    $this->use_levenshtein = $use_levenshtein;
    $this->use_params_rewrite = $use_params_rewrite;

    // Parent Konstruktor aufrufen
    parent::__construct();

    if (rex::isBackend()) {
      // Die Pathnames bei folgenden Extension Points aktualisieren
      $extension = array($this, 'generatePathnames');
      $extensionPoints = array(
        'CAT_ADDED',   'CAT_UPDATED',   'CAT_DELETED',
        'ART_ADDED',   'ART_UPDATED',   'ART_DELETED',
        'ART_TO_CAT',  'CAT_TO_ART',    'ART_TO_STARTPAGE',
        'CLANG_ADDED', 'CLANG_UPDATED', 'CLANG_DELETED',
        'CACHE_DELETED', 'ART_META_UPDATED');

      foreach ($extensionPoints as $extensionPoint) {
        rex_extension::register($extensionPoint, $extension);
      }
    }
  }

  // Parameter aus der URL für das Script verarbeiten
  public function prepare()
  {
    global $REXPATH;

    $article_id = -1;
    $clang = rex_clang::getCurrentId();

    if (!file_exists($this->PATHLIST))
       $this->generatePathnames(array());

    // REXPATH wird auch im Backend benötigt, z.B. beim bearbeiten von Artikeln
    require_once $this->PATHLIST;

    if (!rex::isBackend()) {
      if (rex_request('article_id', 'int', 0) > 0) {
        $this->setArticleId(rex_request('article_id', 'int', rex::getProperty('start_article_id')));
        return true;
      }

      $script_path = str_replace(' ', '%20', dirname($_SERVER['PHP_SELF']));

      // ANDERE DIR_SEP ALS "/" ERSETZEN (WIN BACKSLASHES)
      $script_path = str_replace(DIRECTORY_SEPARATOR, '/', $script_path);

      $length = strlen($script_path);
      $path = substr($_SERVER['REQUEST_URI'], $length);

      // Serverdifferenzen angleichen
      if ($path{
        0
      } == '/')
        $path = substr($path, 1);

      // Parameter zählen nicht zum Pfad -> abschneiden
      if (($pos = strpos($path, '?')) !== false)
        $path = substr($path, 0, $pos);

      // Anker zählen nicht zum Pfad -> abschneiden
      if (($pos = strpos($path, '#')) !== false)
        $path = substr($path, 0, $pos);

      if (($path == '') || (rex_path::frontend($path) == rex_path::frontendController())) {
        $this->setArticleId(rex::getProperty('start_article_id'));
        return true;
      }

      // konvertiert params zu GET/REQUEST Variablen
      if ($this->use_params_rewrite) {
        if (strstr($path, '/+/')) {
          $tmp = explode('/+/', $path);
          $path = $tmp[0] . '/';
          $vars = explode('/', $tmp[1]);
          for ($c = 0; $c < count($vars); $c += 2) {
            if ($vars[$c] != '') {
              $_GET[$vars[$c]] = $vars[$c + 1];
              $_REQUEST[$vars[$c]] = $vars[$c + 1];
            }
          }
        }
      }

      // aktuellen pfad mit pfadarray vergleichen

      foreach ($REXPATH as $key => $var) {
        foreach ($var as $k => $v) {
          if ($path == $v) {
            $article_id = $key;
            $clang = $k;
          }
        }
      }

      // Check Clang StartArtikel
      if ($article_id == -1) {
        foreach (rex_clang::getAll() as $key => $var) {
          if ($var->getName() . '/' == $path || $var->getName() == $path) {
            $clang = $key;
          }
        }
      }

       // Check levenshtein
      if ($this->use_levenshtein && $article_id == -1) {
        foreach ($REXPATH as $key => $var) {
          foreach ($var as $k => $v) {
            $levenshtein[levenshtein($path, $v)] = $key . '#' . $k;
          }
        }

        ksort($levenshtein);
        $best = explode('#', array_shift($levenshtein));

        $article_id = $best[0];
        $clang = $best[1];

      } elseif ($article_id == -1) {
        // ----- EXTENSION POINT
        $article_info = rex_extension::registerPoint('URL_REWRITE_ARTICLE_ID_NOT_FOUND', '' );
        if (isset($article_info['article_id']) && $article_info['article_id'] > -1) {
          $article_id = $article_info['article_id'];

          if (isset($article_info['clang']) && $article_info['clang'] > -1) {
            $clang = $article_info['clang'];
          }
        }

        // Nochmals abfragen wegen EP
        if ($article_id == -1) {
          // Damit auch die "index.php?article_id=xxx" Aufrufe funktionieren
          if (rex_request('article_id', 'int', 0) > 0)
            $article_id = rex::getProperty('article_id');
          else
            $article_id = rex::getProperty('notfound_article_id');
        }
      }

      $this->setArticleId($article_id, $clang);
    }
  }


  private function setArticleId($art_id, $clang_id = -1)
  {
    rex::setProperty('article_id', $art_id);
    if ($clang_id > -1)
      rex_clang::setCurrentId($clang_id);
  }

  // Url neu schreiben
  public function rewrite(array $params)
  {
    // Url wurde von einer anderen Extension bereits gesetzt
    if ($params['subject'] != '')
      return $params['subject'];

    global $REX, $REXPATH;

    $id         = $params['id'];
    $name       = $params['name'];
    $clang      = $params['clang'];
    $divider    = $params['divider'];
    $urlparams  = $params['params'];

    // params umformatieren neue Syntax suchmaschienen freundlich
    if ($this->use_params_rewrite) {
      $urlparams = str_replace($divider, '/', $urlparams);
      $urlparams = str_replace('=', '/', $urlparams);
      $urlparams = $urlparams == '' ? '' : '/' . '+' . $urlparams . '/';
    } else {
      $urlparams = $urlparams == '' ? '' : '?' . $urlparams;
    }

    $urlparams = str_replace('/amp;', '/', $urlparams);
    $url = $REXPATH[$id][$clang] . $urlparams;

    $baseDir = str_replace(' ', '%20', dirname($_SERVER['PHP_SELF']));
    // ANDERE DIR_SEP ALS "/" ERSETZEN (WIN BACKSLASHES)
    $baseDir = str_replace(DIRECTORY_SEPARATOR, '/', $baseDir);
    if (substr($baseDir, -1) != '/' )
      $baseDir .= '/';

    if (rex::isBackend()) {
      $baseDir = '';
    }

    // immer absolute Urls erzeugen, da relative mit rex_redirect() nicht funktionieren
    // da dieser den <base href="" /> nicht kennt.
    return $baseDir . $url;
  }


  /**
   * generiert die Pathlist, abhŠngig von Aktion
   * @author markus.staab[at]redaxo[dot]de Markus Staab
   * @package redaxo5.2
   */
  public function generatePathnames($params)
  {
    global $REX, $REXPATH;

    if (file_exists($this->PATHLIST)) {
      require_once $this->PATHLIST;
    }

    if (!isset($REXPATH))
      $REXPATH = array();

    if (!isset($params['extension_point']))
      $params['extension_point'] = '';

    $where = '';
    switch ($params['extension_point']) {
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
        $where = '(id=' . $params['id'] . ' AND clang=' . $params['clang'] . ') OR (path LIKE "%|' . $params['id'] . '|%" AND clang=' . $params['clang'] . ')';
        break;
      // ------- alles aktualisieren
      case 'CLANG_ADDED':
      case 'CLANG_UPDATED':
      case 'CLANG_DELETED':
      case 'ART_TO_STARTPAGE':
      case 'CACHE_DELETED':
      default:
        $REXPATH = array();
        $where = '1=1';
        break;
    }

    if ($where != '') {
      $db = rex_sql::factory();
      // $db->debugsql=true;
      $db->setQuery('SELECT id,clang,path,startpage FROM ' . rex::getTablePrefix() . 'article WHERE ' . $where . ' and revision=0');

      foreach ($db as $art) {
        $clang = $art->getValue('clang');
        $pathname = '';
        if (rex_clang::count() > 1) {
          $pathname = rex_clang::get($clang)->getName() . '/';
        }

        // pfad über kategorien bauen
        $path = trim($art->getValue('path'), '|');
        if ($path != '') {
          $path = explode('|', $path);
          foreach ($path as $p) {
            $ooc = rex_category::getCategoryById($p, $clang);
            $name = $ooc->getName();
            unset($ooc); // speicher freigeben

            $pathname = self::appendToPath($pathname, $name);
          }
        }

        $ooa = rex_article::getArticleById($art->getValue('id'), $clang);
        if ($ooa->isStartArticle()) {
          $ooc = $ooa->getCategory();
          $catname = $ooc->getName();
          unset($ooc); // speicher freigeben
          $pathname = self::appendToPath($pathname, $catname);
        }

        // eigentlicher artikel anhängen
        $name = $ooa->getName();
        unset($ooa); // speicher freigeben
        $pathname = self::appendToPath($pathname, $name);

        $pathname = substr($pathname, 0, strlen($pathname) - 1) . '.html';
        $REXPATH[$art->getValue('id')][$art->getValue('clang')] = $pathname;
      }
    }

    rex_file::put($this->PATHLIST, "<?php\n\$REXPATH = " . var_export($REXPATH, true) . ";\n");
  }

  static private function appendToPath($path, $name)
  {
    if ($name != '') {
      $name = strtolower(rex_parse_article_name($name));
      $path .= $name . '/';
    }
    return $path;
  }
}
