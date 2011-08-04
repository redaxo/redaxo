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

  $configFile = rex_path::src('config.yml');
  $config = rex_file::getConfig($configFile);
  $config['setup'] = true;
  // echo nl2br(htmlspecialchars($cont));
  if (rex_file::putConfig($configFile, $config) !== false)
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

  rex::setProperty('lang', $neu_lang);
  $configFile = rex_path::src('config.yml');
  $config = rex_file::getConfig($configFile);

  if(!rex_ooArticle::isValid($startArt))
  {
    $warning .= rex_i18n::msg('settings_invalid_sitestart_article')."<br />";
  }else
  {
    $config['start_article_id'] = $neu_startartikel;
    rex::setProperty('start_article_id', $neu_startartikel);
  }

  if(!rex_ooArticle::isValid($notFoundArt))
  {
    $warning .= rex_i18n::msg('settings_invalid_notfound_article')."<br />";
  }else
  {
	  $config['notfound_article_id'] = $neu_notfoundartikel;
    rex::setProperty('notfound_article_id', $neu_notfoundartikel);
  }

  $sql = rex_sql::factory();
  $sql->setQuery('SELECT * FROM '. rex::getTablePrefix() .'template WHERE id='. $neu_defaulttemplateid .' AND active=1');
  if($sql->getRows() != 1 && $neu_defaulttemplateid != 0)
  {
    $warning .= rex_i18n::msg('settings_invalid_default_template')."<br />";
  }else
	{
	  $config['default_template_id'] = $neu_defaulttemplateid;
    rex::setProperty('default_template_id', $neu_defaulttemplateid);
	}

  $config['error_email'] = $neu_error_emailaddress;
  $config['lang'] = $neu_lang;
  $config['server'] = $neu_SERVER;
  $config['servername'] = $neu_SERVERNAME;
  $config['mod_rewrite'] = $neu_modrewrite;

  if($warning == '')
  {
    if(rex_file::putConfig($configFile, $config) > 0)
    {
      $info = rex_i18n::msg('info_updated');

      // Zuweisungen für Wiederanzeige
      rex::setProperty('mod_rewrite', $neu_modrewrite === 'TRUE');
      rex::setProperty('error_email', $neu_error_emailaddress);
      rex::setProperty('server', $neu_SERVER);
      rex::setProperty('servername', $neu_SERVERNAME);
    }
  }
}

$sel_template = new rex_select();
$sel_template->setStyle('class="rex-form-select"');
$sel_template->setName('neu_defaulttemplateid');
$sel_template->setId('rex-form-default-template-id');
$sel_template->setSize(1);
$sel_template->setSelected(rex::getProperty('default_template_id'));

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
$sel_lang->setSelected(rex::getProperty('lang'));

foreach (rex_i18n::getLocales() as $l)
{
  $sel_lang->addOption($l, $l);
}

$sel_mod_rewrite = new rex_select();
$sel_mod_rewrite->setSize(1);
$sel_mod_rewrite->setStyle('class="rex-form-select"');
$sel_mod_rewrite->setName('neu_modrewrite');
$sel_mod_rewrite->setId('rex-form-mod-rewrite');
$sel_mod_rewrite->setSelected(rex::getProperty('mod_rewrite') === false ? 'FALSE' : 'TRUE');

$sel_mod_rewrite->addOption('TRUE', 'TRUE');
$sel_mod_rewrite->addOption('FALSE', 'FALSE');

if ($warning != '')
  echo rex_warning($warning);

if ($info != '')
  echo rex_info($info);

$dbconfig = rex::getProperty('db');



$version = rex_path::version();
if (strlen($version)>21)
	$version = substr($version,0,8)."..".substr($version,strlen($version)-13);


$headline_1 = rex_i18n::msg("system_features");
$headline_2 = rex_i18n::msg("system_settings");

$content_1 = '
						<h4 class="rex-hl3">'.rex_i18n::msg("delete_cache").'</h4>
						<p class="rex-tx1">'.rex_i18n::msg("delete_cache_description").'</p>
						<p class="rex-button"><a class="rex-button" href="index.php?page=system&amp;func=generate"><span><span>'.rex_i18n::msg("delete_cache").'</span></span></a></p>

						<h4 class="rex-hl3">'.rex_i18n::msg("setup").'</h4>
						<p class="rex-tx1">'.rex_i18n::msg("setup_text").'</p>
						<p class="rex-button"><a class="rex-button" href="index.php?page=system&amp;func=setup" onclick="return confirm(\''.rex_i18n::msg("setup").'?\');"><span><span>'.rex_i18n::msg("setup").'</span></span></a></p>

            <h4 class="rex-hl3">'.rex_i18n::msg("version").'</h4>
            <p class="rex-tx1">
            REDAXO: '.rex::getVersion().'<br />
            PHP: '.phpversion().' (<a href="index.php?page=system&amp;subpage=phpinfo" onclick="newWindow(\'phpinfo\', this.href, 800,600,\',status=yes,resizable=yes\');return false;">php_info</a>)</p>

            <h4 class="rex-hl3">'.rex_i18n::msg("database").'</h4>
            <p class="rex-tx1">MySQL: '.rex_sql::getServerVersion().'<br />'.rex_i18n::msg("name").': '.$dbconfig[1]['name'].'<br />'.rex_i18n::msg("host").': '.$dbconfig[1]['host'].'</p>';


$content_2 = '
						<fieldset class="rex-form-col-1">
							<legend>'. rex_i18n::msg("general_info_header").'</legend>

							<div class="rex-form-wrapper">

            <!--
							<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex-form-version">Version</label>
										<span class="rex-form-read" id="rex-form-version">'. rex::getVersion().'</span>
									</p>
								</div>
						-->

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-server">$REX[\'SERVER\']</label>
										<input class="rex-form-text" type="text" id="rex-form-server" name="neu_SERVER" value="'. htmlspecialchars(rex::getProperty('server')).'" />
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-servername">$REX[\'SERVERNAME\']</label>
										<input class="rex-form-text" type="text" id="rex-form-servername" name="neu_SERVERNAME" value="'. htmlspecialchars(rex::getProperty('servername')).'" />
									</p>
								</div>
							</div>
            <!--
						</fieldset>
						-->

						<!--
						<fieldset class="rex-form-col-1">
							<legend>'. rex_i18n::msg("db1_can_only_be_changed_by_setup").'</legend>

							<div class="rex-form-wrapper">

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex-form-db-host">$REX[\'DB\'][\'1\'][\'HOST\']</label>
										<span class="rex-form-read" id="rex-form-db-host">&quot;'. $dbconfig[1]['host'].'&quot;</span>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-db-login">$REX[\'DB\'][\'1\'][\'LOGIN\']</label>
										<span id="rex-form-db-login">&quot;'. $dbconfig[1]['login'].'&quot;</span>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex-form-db-psw">$REX[\'DB\'][\'1\'][\'PSW\']</label>
										<span class="rex-form-read" id="rex-form-db-psw">&quot;****&quot;</span>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex-form-db-name">$REX[\'DB\'][\'1\'][\'NAME\']</label>
										<span class="rex-form-read" id="rex-form-db-name">&quot;'. htmlspecialchars($dbconfig[1]['name']).'&quot;</span>
									</p>
								</div>
							</div>
						</fieldset>
						-->

						<!--
						<fieldset class="rex-form-col-1">
							<legend>'.rex_i18n::msg("system_others").'</legend>

							<div class="rex-form-wrapper">
						-->

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex_src_path">rex_path::version()</label>
										<span class="rex-form-read" id="rex_src_path" title="'. rex_path::version() .'">&quot;'.$version.'&quot;</span>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-error-email">$REX[\'ERROR_EMAIL\']</label>
										<input class="rex-form-text" type="text" id="rex-form-error-email" name="neu_error_emailaddress" value="'. htmlspecialchars(rex::getProperty('error_email')).'" />
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-widget">
										<label for="rex-form-startarticle-id">$REX[\'START_ARTICLE_ID\']</label>
										'. rex_var_link::_getLinkButton('neu_startartikel', 1, rex::getProperty('start_article_id')).'
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-widget">
										<label for="rex-form-notfound-article-id">$REX[\'NOTFOUND_ARTICLE_ID\']</label>
                    '. rex_var_link::_getLinkButton('neu_notfoundartikel', 2, rex::getProperty('notfound_article_id')).'
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-select">
										<label for="rex-form-default-template-id">$REX[\'DEFAULT_TEMPLATE_ID\']</label>
										'. $sel_template->get().'
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-select">
										<label for="rex-form-lang">$REX[\'LANG\']</label>
										'. $sel_lang->get().'
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-select">
										<label for="rex-form-mod-rewrite">$REX[\'MOD_REWRITE\']</label>
										'. $sel_mod_rewrite->get().'
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-submit">
										<input type="submit" class="rex-form-submit" name="sendit" value="'. rex_i18n::msg("system_update").'" '. rex::getAccesskey(rex_i18n::msg('system_update'), 'save').' />
									</p>
								</div>

            <!--
								</div>
						-->
						</fieldset>';

?>

<div class="rex-form" id="rex-form-system-setup">
	<form action="index.php" method="post">
  	<input type="hidden" name="page" value="system" />
  	<input type="hidden" name="func" value="updateinfos" />

<?php
$fragment = new rex_fragment();
$fragment->setVar('headline_1', $headline_1, false);
$fragment->setVar('headline_2', $headline_2, false);
$fragment->setVar('content_1', $content_1, false);
$fragment->setVar('content_2', $content_2, false);
echo $fragment->parse('section_grid2col');
unset($fragment);
?>
	</form>
</div>