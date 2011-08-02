<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

rex_var::registerVar('rex_var_globals');
rex_var::registerVar('rex_var_article');
rex_var::registerVar('rex_var_category');

require_once dirname(__FILE__). '/functions/function_rex_url.inc.php';

if(rex_request('article_id', 'int') == 0)
  rex::setProperty('article_id', rex::getProperty('start_article_id'));
else
  rex::setProperty('article_id', rex_request('article_id','rex-article-id', rex::getProperty('notfound_article_id')));

rex_extension::register('CLANG_ADDED',
  function($params)
  {
    $firstLang = rex_sql::factory();
    $firstLang->setQuery("select * from ". rex::getTablePrefix() ."article where clang='0'");
    $fields = $firstLang->getFieldnames();

    $newLang = rex_sql::factory();
    // $newLang->debugsql = 1;
    foreach($firstLang as $firstLangArt)
    {
      $newLang->setTable(rex::getTablePrefix()."article");

      foreach($fields as $key => $value)
      {
        if ($value == 'pid')
          echo ''; // nix passiert
        elseif ($value == 'clang')
          $newLang->setValue('clang', $params['id']);
        elseif ($value == 'status')
          $newLang->setValue('status', '0'); // Alle neuen Artikel offline
        else
          $newLang->setValue($value, $firstLangArt->getValue($value));
      }

      $newLang->insert();
    }
  }
);

rex_extension::register('CLANG_DELETED',
  function($params)
  {
    $del = rex_sql::factory();
    $del->setQuery("delete from ". rex::getTablePrefix() ."article where clang='". $params['id'] ."'");
  }
);