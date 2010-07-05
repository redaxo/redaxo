<?php

/**
 * URL Funktionen
 * @package redaxo4
 * @version svn:$Id$
 */

function rex_parse_article_name($name)
{
  static $firstCall = true;
  static $search, $replace;

  if($firstCall)
  {
    global $REX, $I18N;

    // Im Frontend gibts kein I18N
    if(!$I18N)
      $I18N = rex_create_lang($REX['LANG']);

    // Sprachspezifische Sonderzeichen Filtern
    $search = explode('|', $I18N->msg('special_chars'));
    $replace = explode('|', $I18N->msg('special_chars_rewrite'));

    $firstCall = false;
  }

  return 
    // + durch - ersetzen
    str_replace('+','-',
        // ggf uebrige zeichen url-codieren
        urlencode(
          // mehrfach hintereinander auftretende spaces auf eines reduzieren
          preg_replace('/ {2,}/',' ',
            // alle sonderzeichen raus 
            preg_replace('/[^a-zA-Z_\-0-9 ]/', '',
              // sprachspezifische zeichen umschreiben 
              str_replace($search, $replace, $name)
            )
          )
        )
    );
}

/**
 * Baut einen Parameter String anhand des array $params
 */
function rex_param_string($params, $divider = '&amp;')
{
  $param_string = '';

  if (is_array($params))
  {
    foreach ($params as $key => $value)
    {
      $param_string .= $divider.urlencode($key).'='.urlencode($value);
    }
  }
  elseif ($params != '')
  {
    $param_string = $params;
  }

  return $param_string;
}

/**
 * Gibt eine Url zu einem Artikel zurück
 *
 * @param [$_id] ArtikelId des Artikels
 * @param [$_clang] SprachId des Artikels
 * @param [$_params] Array von Parametern
 * @param [$_divider] Trennzeichen für Parameter
 * (z.B. &amp; für HTML, & für Javascript)
 */
function rex_getUrl($_id = '', $_clang = '', $_params = '', $_divider = '&amp;')
{
  global $REX;

  $id = (int) $_id;
  $clang = (int) $_clang;

  // ----- get id
  if ($id == 0)
    $id = $REX["ARTICLE_ID"];

  // ----- get clang
  // Wenn eine rexExtension vorhanden ist, immer die clang mitgeben!
  // Die rexExtension muss selbst entscheiden was sie damit macht
  if ($_clang === '' && (count($REX['CLANG']) > 1 || rex_extension_is_registered( 'URL_REWRITE')))
    $clang = $REX['CUR_CLANG'];

  // ----- get params
  $param_string = rex_param_string($_params, $_divider);

  $name = 'NoName';
  if ($id != 0)
  {
    $ooa = OOArticle :: getArticleById($id, $clang);
    if ($ooa)
      $name = rex_parse_article_name($ooa->getName());
  }

  // ----- EXTENSION POINT
  $url = rex_register_extension_point('URL_REWRITE', '', array ('id' => $id, 'name' => $name, 'clang' => $clang, 'params' => $param_string, 'divider' => $_divider));

  if ($url == '')
  {
    // ----- get rewrite function
    if ($REX['MOD_REWRITE'] === true || $REX['MOD_REWRITE'] == 'true')
      $rewrite_fn = 'rex_apache_rewrite';
    else
      $rewrite_fn = 'rex_no_rewrite';

    $url = call_user_func($rewrite_fn, $id, $name, $clang, $param_string, $_divider);
  }

  return $url;
}

// ----------------------------------------- Rewrite functions

/**
 * Standard Rewriter, gibt normale Urls zurück im Format
 * index.php?article_id=$article_id[&clang=$clang&$params]
 */
function rex_no_rewrite($id, $name, $clang, $param_string, $divider)
{
  global $REX;
  $_clang = '';

  if (count($REX['CLANG']) > 1)
  {
    $_clang .= $divider.'clang='.$clang;
  }

  return $REX["FRONTEND_FILE"].'?article_id='.$id .$_clang.$param_string;
}

/**
 * Standard Rewriter, gibt umschrieben Urls im Format
 *
 * <id>-<clang>-<name>.html[?<params>]
 */
function rex_apache_rewrite($id, $name, $clang, $params, $divider)
{
  if ($params != '')
  {
    // strip first "&"
    $params = '?'.substr($params, strpos($params, $divider) + strlen($divider));
  }

  return $id.'-'.$clang.'-'.$name.'.html'.$params;
}