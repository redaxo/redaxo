<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$info = '';
$warning = '';

if ($func == 'setup')
{
  // REACTIVATE SETUP

  $master_file = rex_path::src('config/master.inc.php');
  $cont = rex_file::get($master_file);
  $cont = preg_replace("@(REX\['SETUP'\].?\=.?)[^;]*@", '$1true', $cont);
  // echo nl2br(htmlspecialchars($cont));
  if (rex_file::put($master_file, $cont) !== false)
  {
    $info = rex_i18n::msg('setup_error1', '<a href="index.php">', '</a>');
  }
  else
  {
    $warning = rex_i18n::msg('setup_error2');
  }
}elseif ($func == 'generate')
{
  // generate all articles,cats,templates,caches
  $info = rex_generateAll();
}
elseif ($func == 'updateinfos')
{
  $neu_startartikel       = rex_post('neu_startartikel', 'int');
  $neu_notfoundartikel    = rex_post('neu_notfoundartikel', 'int');
  $neu_defaulttemplateid  = rex_post('neu_defaulttemplateid', 'int');
  $neu_lang               = rex_post('neu_lang', 'string');
  // ' darf nichtg escaped werden, da in der Datei der Schlüssel nur zwischen " steht
  $neu_error_emailaddress = str_replace("\'", "'", rex_post('neu_error_emailaddress', 'string'));
  $neu_SERVER             = str_replace("\'", "'", rex_post('neu_SERVER', 'string'));
  $neu_SERVERNAME         = str_replace("\'", "'", rex_post('neu_SERVERNAME', 'string'));
  $neu_modrewrite         = rex_post('neu_modrewrite', 'string');

  $startArt = rex_ooArticle::getArticleById($neu_startartikel);
  $notFoundArt = rex_ooArticle::getArticleById($neu_notfoundartikel);

  $REX['LANG'] = $neu_lang;
  $master_file = rex_path::src('config/master.inc.php');
  $cont = rex_file::get($master_file);

  if(!rex_ooArticle::isValid($startArt))
  {
    $warning .= rex_i18n::msg('settings_invalid_sitestart_article')."<br />";
  }else
  {
    $cont = preg_replace("@(REX\['START_ARTICLE_ID'\].?\=.?)[^;]*@", '${1}'.strtolower($neu_startartikel), $cont);
    $REX['START_ARTICLE_ID'] = $neu_startartikel;
  }

  if(!rex_ooArticle::isValid($notFoundArt))
  {
    $warning .= rex_i18n::msg('settings_invalid_notfound_article')."<br />";
  }else
  {
	  $cont = preg_replace("@(REX\['NOTFOUND_ARTICLE_ID'\].?\=.?)[^;]*@", '${1}'.strtolower($neu_notfoundartikel), $cont);
    $REX['NOTFOUND_ARTICLE_ID'] = $neu_notfoundartikel;
  }

  $sql = rex_sql::factory();
  $sql->setQuery('SELECT * FROM '. $REX['TABLE_PREFIX'] .'template WHERE id='. $neu_defaulttemplateid .' AND active=1');
  if($sql->getRows() != 1 && $neu_defaulttemplateid != 0)
  {
    $warning .= rex_i18n::msg('settings_invalid_default_template')."<br />";
  }else
	{
	  $cont = preg_replace("@(REX\['DEFAULT_TEMPLATE_ID'\].?\=.?)[^;]*@", '${1}'.strtolower($neu_defaulttemplateid), $cont);
    $REX['DEFAULT_TEMPLATE_ID'] = $neu_defaulttemplateid;
	}

  $cont = preg_replace("@(REX\['ERROR_EMAIL'\].?\=.?)[^;]*@", '$1"'.strtolower($neu_error_emailaddress).'"', $cont);
  $cont = preg_replace("@(REX\['LANG'\].?\=.?)[^;]*@", '$1"'.$neu_lang.'"', $cont);
  $cont = preg_replace("@(REX\['SERVER'\].?\=.?)[^;]*@", '$1"'. ($neu_SERVER).'"', $cont);
  $cont = preg_replace("@(REX\['SERVERNAME'\].?\=.?)[^;]*@", '$1"'. ($neu_SERVERNAME).'"', $cont);
  $cont = preg_replace("@(REX\['MOD_REWRITE'\].?\=.?)[^;]*@",'$1'.strtolower($neu_modrewrite),$cont);

  if($warning == '')
  {
    if(rex_file::put($master_file, $cont) > 0)
    {
      $info = rex_i18n::msg('info_updated');

      // Zuweisungen für Wiederanzeige
      $REX['MOD_REWRITE'] = $neu_modrewrite === 'TRUE';
      $REX['ERROR_EMAIL'] = $neu_error_emailaddress;
      $REX['SERVER'] = $neu_SERVER;
      $REX['SERVERNAME'] = $neu_SERVERNAME;
    }
  }
}

$sel_template = new rex_select();
$sel_template->setStyle('class="rex-form-select"');
$sel_template->setName('neu_defaulttemplateid');
$sel_template->setId('rex-form-default-template-id');
$sel_template->setSize(1);
$sel_template->setSelected($REX['DEFAULT_TEMPLATE_ID']);

$templates = rex_ooCategory::getTemplates(0);
if (empty($templates))
  $sel_template->addOption(rex_i18n::msg('option_no_template'), 0);
else
  $sel_template->addArrayOptions($templates);

$sel_lang = new rex_select();
$sel_lang->setStyle('class="rex-form-select"');
$sel_lang->setName('neu_lang');
$sel_lang->setId('rex-form-lang');
$sel_lang->setSize(1);
$sel_lang->setSelected($REX['LANG']);

foreach (rex_i18n::getLocales() as $l)
{
  $sel_lang->addOption($l, $l);
}

$sel_mod_rewrite = new rex_select();
$sel_mod_rewrite->setSize(1);
$sel_mod_rewrite->setStyle('class="rex-form-select"');
$sel_mod_rewrite->setName('neu_modrewrite');
$sel_mod_rewrite->setId('rex-form-mod-rewrite');
$sel_mod_rewrite->setSelected($REX['MOD_REWRITE'] === false ? 'FALSE' : 'TRUE');

$sel_mod_rewrite->addOption('TRUE', 'TRUE');
$sel_mod_rewrite->addOption('FALSE', 'FALSE');

$dbconfig = rex_file::getConfig(rex_path::backend('src/dbconfig.yml'));

if ($warning != '')
  echo rex_warning($warning);

if ($info != '')
  echo rex_info($info);



$fragment = new rex_fragment();
$fragment->setVar('dbconfig', $dbconfig);
$fragment->setVar('template', $sel_template->get(), false);
$fragment->setVar('language', $sel_lang->get(), false);
$fragment->setVar('mod_rewrite', $sel_mod_rewrite->get(), false);
echo $fragment->parse('core_system_settings');
unset($fragment);
