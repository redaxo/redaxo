<?php

/**
 *
 * @package redaxo5
 */

// --------------------------------------------- SETUP FUNCTIONS

/**
 * Ausgabe des Setup spezifischen Titels
 */
function rex_setup_title($title)
{
  echo rex_view::title($title);

  echo '<div id="rex-setup" class="rex-area">';
}

// --------------------------------------------- END: SETUP FUNCTIONS

$MSG['err'] = "";
$err_msg = '';

$checkmodus = rex_request('checkmodus', 'float');
$send       = rex_request('send', 'string');
$dbanlegen  = rex_request('dbanlegen', 'string');
$noadmin    = rex_request('noadmin', 'string');
$lang       = rex_request('lang', 'string');

// ---------------------------------- MODUS 0 | Start
if (!($checkmodus > 0 && $checkmodus < 10))
{
  rex_setup::init();

  $saveLocale = rex_i18n::getLocale();
  $langs = array();
  foreach(rex_i18n::getLocales() as $locale)
  {
    rex_i18n::setLocale($locale,FALSE); // Locale nicht neu setzen
    $label = rex_i18n::msg('lang');
    $langs[$locale] = '<li><a href="index.php?checkmodus=0.5&amp;lang='.$locale.'">'.$label.'</a></li>';
  }
  rex_i18n::setLocale($saveLocale, false);

  // wenn nur eine Sprache -> direkte weiterleitung
  if (count(rex_i18n::getLocales())==1)
  {
    header('Location: index.php?checkmodus=0.5&lang='.key($langs));
    exit();
  }

  rex_setup_title('SETUP: SELECT LANGUAGE');

  echo '<h2 class="rex-hl2">Please choose a language!</h2>
      <div class="rex-area-content">
        <ul class="rex-setup-language">'. implode('', $langs) .'</ul>
      </div>';
}

// ---------------------------------- MODUS 0 | Start

if ($checkmodus == '0.5')
{
  rex_setup_title('SETUP: START');

  rex::setProperty('lang', $lang);

  echo rex_i18n::msg('setup_005', '<h2 class="rex-hl2">', '</h2>');
  echo '<div class="rex-area-content">';

  echo rex_i18n::msg('setup_005_1', '<h3 class="rex-hl3">', '</h3>', ' class="rex-ul1"');
  echo '<div class="rex-area-scroll">';

  $license_file = rex_path::base('_license.txt');
  $license = '<p class="rex-tx1">'.nl2br(rex_file::get($license_file)).'</p>';

  echo $license;

  echo '</div>
      </div>
      <div class="rex-area-footer">
        <p class="rex-algn-rght"><a href="index.php?page=setup&amp;checkmodus=1&amp;lang='.$lang.'">&raquo; '.rex_i18n::msg("setup_006").'</a></p>
      </div>';

  $checkmodus = 0;
}

// ---------------------------------- MODUS 1 | Versionscheck - Rechtecheck

if ($checkmodus == 1)
{
  // -------------------------- ENV CHECK
  foreach(rex_setup::checkEnvironment() as $error)
  {
    $MSG['err'] .= $error;
  }

  // -------------------------- SCHREIBRECHTE
  $res = rex_setup::checkFilesystem();

  if(count($res) > 0)
  {
    $MSG['err'] .= '<li>';
    foreach($res as $key => $messages)
    {
      if(count($messages) > 0)
      {
        $MSG['err'] .= '<h3 class="rex-hl3">'.rex_i18n::msg($key, '<span class="rex-error">', '</span>') .'</h3>';
        $MSG['err'] .= '<ul>';
        foreach($messages as $message)
        {
          $MSG['err'] .= '<li>'. $message .'</li>';
        }
        $MSG['err'] .= '</ul>';
      }
    }
    $MSG['err'] .= '</li>';
  }
}

if ($MSG['err'] == '' && $checkmodus == 1)
{
  rex_setup_title(rex_i18n::msg('setup_step1'));

  echo rex_i18n::msg('setup_016', '<h2 class="rex-hl2">', '</h2>');
  echo '<div class="rex-area-content">';

  echo rex_i18n::msg('setup_016_1', ' class="rex-ul1"', '<span class="rex-ok">', '</span>');
  echo '<div class="rex-message"><p class="rex-warning" id="security_warning" style="display: none;"><span>'. rex_i18n::msg('setup_security_msg') .'</span></p></div>
        <noscript><div class="rex-message"><p class="rex-warning"><span>'. rex_i18n::msg('setup_no_js_security_msg') .'</span></p></div></noscript>
     </div>
     <div class="rex-area-footer">
       <p id="nextstep" class="rex-algn-rght">
         <a href="index.php?page=setup&amp;checkmodus=2&amp;lang='.$lang.'">&raquo; '.rex_i18n::msg('setup_017').'</a>
       </p>
     </div>';

}
elseif ($MSG['err'] != "")
{

  rex_setup_title(rex_i18n::msg('setup_step1'));

  echo '<h2 class="rex-hl2">'.rex_i18n::msg('setup_headline1').'</h2>
      <div class="rex-area-content">
        <ul class="rex-ul1">'.$MSG['err'].'</ul>
        <p class="rex-tx1">'.rex_i18n::msg('setup_018').'</p>
      </div>
      <div class="rex-area-footer">
        <p class="rex-algn-rght">
          <a href="index.php?page=setup&amp;checkmodus=1&amp;lang='.$lang.'">&raquo; '.rex_i18n::msg('setup_017').'</a>
        </p>
      </div>';
}

// ---------------------------------- MODUS 2 | master.inc.php - Datenbankcheck

if($checkmodus == 2)
{
  $configFile = rex_path::data('config.yml');
  $config = rex_file::getConfig($configFile);
}

if ($checkmodus == 2 && $send == 1)
{
  $config['server']            = rex_post('serveraddress', 'string');
  $config['servername']        = rex_post('serverbezeichnung', 'string');
  $config['lang']              = $lang;
  $config['error_email']       = rex_post('error_email', 'string');
  $config['timezone']          = rex_post('timezone', 'string');
  $config['db'][1]['host']     = rex_post('mysql_host', 'string');
  $config['db'][1]['login']    = rex_post('redaxo_db_user_login', 'string');
  $config['db'][1]['password'] = rex_post('redaxo_db_user_pass', 'string');
  $config['db'][1]['name']     = rex_post('dbname', 'string');
  $redaxo_db_create            = rex_post('redaxo_db_create', 'boolean');
  if(empty($config['instname']))
  {
    $config['instname'] = 'rex'. date('YmdHis');
  }

  // check if timezone is valid
  if(@date_default_timezone_set($config['timezone']) === false)
  {
    $err_msg = rex_i18n::msg('setup_invalid_timezone');
  }

  if(empty($config['error_email']))
  {
    $err_msg = rex_i18n::msg('error_email_required');
  }

  foreach($config as $key => $value)
  {
    if(in_array($key, array('fileperm', 'dirperm')))
    {
      $value = octdec($value);
    }
    rex::setProperty($key, $value);
  }

  if($err_msg == '')
  {
    if(!rex_file::putConfig($configFile, $config, 3))
    {
      $err_msg = rex_i18n::msg('setup_020', '<b>', '</b>');
    }
  }

  // -------------------------- DATENBANKZUGRIFF CHECK
  if($err_msg == '')
  {
    $err = rex_setup::checkDb($config, $redaxo_db_create);
    if($err != '')
    {
      $err_msg = $err;
    }
  }

  // everything went fine, advance to the next setup step
  if($err_msg == '')
  {
    $checkmodus = 3;
    $send = "";
  }
}

if($checkmodus == 2)
{
  rex_setup_title(rex_i18n::msg('setup_step2'));

  echo '<h2 class="rex-hl2">'.rex_i18n::msg('setup_023').'</h2>
      <div class="rex-form" id="rex-form-setup-step-2">
      <form action="index.php" method="post">
      <fieldset class="rex-form-col-1">
        <input type="hidden" name="page" value="setup" />
        <input type="hidden" name="checkmodus" value="2" />
        <input type="hidden" name="send" value="1" />
        <input type="hidden" name="lang" value="'.$lang.'" />';

  if ($err_msg != '') {
    echo rex_view::warning($err_msg);
  }

  $timezone_sel = new rex_select;
  $timezone_sel->setId('timezone');
  $timezone_sel->setName('timezone');
  $timezone_sel->setSize(1);
  $timezone_sel->addOptions(DateTimeZone::listIdentifiers(), true);
  $timezone_sel->setSelected($config['timezone']);

  $db_create_checked = rex_post('redaxo_db_create', 'boolean') ? ' checked="checked"' : '';

  echo '
          <legend>'.rex_i18n::msg("setup_0201").'</legend>

          <div class="rex-form-wrapper">
            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-text">
                <label for="serveraddress">'.rex_i18n::msg("setup_024").'</label>
                <input class="rex-form-text" type="text" id="serveraddress" name="serveraddress" value="'.$config['server'].'" />
              </p>
            </div>

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-text">
                <label for="serverbezeichnung">'.rex_i18n::msg("setup_025").'</label>
                <input class="rex-form-text" type="text" id="serverbezeichnung" name="serverbezeichnung" value="'.$config['servername'].'" />
              </p>
            </div>

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-text">
                <label for="error_email">'.rex_i18n::msg("error_email").'</label>
                <input class="rex-form-text" type="text" id="error_email" name="error_email" value="'.$config['error_email'].'" />
              </p>
            </div>

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-text">
                <label for="timezone">'.rex_i18n::msg("setup_timezone").'</label>
                '. $timezone_sel->get() .'
              </p>
            </div>
        </div>
        </fieldset>

        <fieldset class="rex-form-col-1">
          <legend>'.rex_i18n::msg("setup_0202").'</legend>
          <div class="rex-form-wrapper">
            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-text">
                <label for="dbname">'.rex_i18n::msg("setup_027").'</label>
                <input class="rex-form-text" type="text" value="'.$config['db'][1]['name'].'" id="dbname" name="dbname" />
              </p>
            </div>

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-text">
                <label for="mysql_host">MySQL Host</label>
                <input class="rex-form-text" type="text" id="mysql_host" name="mysql_host" value="'.$config['db'][1]['host'].'" />
              </p>
            </div>

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-text">
                <label for="redaxo_db_user_login">Login</label>
                <input class="rex-form-text" type="text" id="redaxo_db_user_login" name="redaxo_db_user_login" value="'.$config['db'][1]['login'].'" />
              </p>
            </div>

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-text">
                <label for="redaxo_db_user_pass">'.rex_i18n::msg("setup_028").'</label>
                <input class="rex-form-text" type="text" id="redaxo_db_user_pass" name="redaxo_db_user_pass" value="'.$config['db'][1]['password'].'" />
              </p>
            </div>

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-checkbox">
                <label for="redaxo_db_create">'.rex_i18n::msg("setup_create_db").'</label>
                <input class="rex-form-checkbox" type="checkbox" id="redaxo_db_create" name="redaxo_db_create" value="1"'. $db_create_checked .' />
              </p>
            </div>
          </div>
        </fieldset>

        <fieldset class="rex-form-col-1">
          <div class="rex-form-wrapper">
            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-submit">
                <input class="rex-form-submit" type="submit" value="'.rex_i18n::msg("setup_029").'" />
              </p>
            </div>

          </div>
        </fieldset>
      </form>
      </div>
      <script type="text/javascript">
         <!--
        jQuery(function($) {
          $("#serveraddress").focus();
        });
         //-->
      </script>';
}

// ---------------------------------- MODUS 3 | Datenbank anlegen ...

if ($checkmodus == 3 && $send == 1)
{
  $dbanlegen = rex_post('dbanlegen', 'int', '');

  // -------------------------- Benötigte Tabellen prüfen

  if ($dbanlegen == 4)
  {
    $err_msg .= rex_setup_importer::updateFromPrevious();
  }
  elseif ($dbanlegen == 3)
  {
    $import_name = rex_post('import_name', 'string');
    
    $err_msg .= rex_setup_importer::loadExistingImport($import_name);
  }
  elseif ($dbanlegen == 2)
  {
    $err_msg .=rex_setup_importer::databaseAlreadyExists();
  }
  elseif ($dbanlegen == 1)
  {
    $err_msg .=rex_setup_importer::overrideExisting();
  }
  elseif ($dbanlegen == 0)
  {
    $err_msg .=rex_setup_importer::prepareEmptyDb();
  }

  if($err_msg == "" && $dbanlegen !== '')
  {
    $err_msg .= rex_setup_importer::verifyDbSchema();
  }

  if ($err_msg == "")
  {
    rex_clang_service::generateCache();
    $send = "";
    $checkmodus = 4;
  }
}

if ($checkmodus == 3)
{
  $dbanlegen = rex_post('dbanlegen', 'int', '');
  rex_setup_title(rex_i18n::msg('setup_step3'));

  echo '<div class="rex-form rex-form-setup-step-database">
      <form action="index.php" method="post">
      <fieldset class="rex-form-col-1">
        <input type="hidden" name="page" value="setup" />
        <input type="hidden" name="checkmodus" value="3" />
        <input type="hidden" name="send" value="1" />
        <input type="hidden" name="lang" value="'.$lang.'" />

        <legend>'.rex_i18n::msg('setup_030_headline').'</legend>
          <div class="rex-form-wrapper">
      ';

  if ($err_msg != '')
    echo rex_view::warning($err_msg.'<br />'.rex_i18n::msg('setup_033'));

  $dbchecked = array_fill(0, 6, '');
  switch ($dbanlegen)
  {
    case 1 :
    case 2 :
    case 3 :
    case 4 :
      $dbchecked[$dbanlegen] = ' checked="checked"';
      break;
    default :
      $dbchecked[0] = ' checked="checked"';
  }

  // Vorhandene Exporte auslesen
  $sel_export = new rex_select();
  $sel_export->setName('import_name');
  $sel_export->setId('import_name');
  $sel_export->setStyle('class="rex-form-select"');
  $sel_export->setAttribute('onclick', 'checkInput(\'dbanlegen_3\')');
  $export_dir = getImportDir();
  $exports_found = false;

  if (is_dir($export_dir))
  {
    if ($handle = opendir($export_dir))
    {
      $export_archives = array ();
      $export_sqls = array ();

      while (($file = readdir($handle)) !== false)
      {
        if ($file == '.' || $file == '..')
        {
          continue;
        }

        $isSql = (substr($file, strlen($file) - 4) == '.sql');
        $isArchive = (substr($file, strlen($file) - 7) == '.tar.gz');

        if ($isSql)
        {
          // endung .sql abschneiden
          $export_sqls[] = substr($file, 0, -4);
          $exports_found = true;
        }
        elseif ($isArchive)
        {
          // endung .tar.gz abschneiden
          $export_archives[] = substr($file, 0, -7);
          $exports_found = true;
        }
      }
      closedir($handle);
    }

    foreach ($export_sqls as $sql_export)
    {
      // Es ist ein Export Archiv + SQL File vorhanden
      if (in_array($sql_export, $export_archives))
      {
        $sel_export->addOption($sql_export, $sql_export);
      }
    }
  }

  echo '

  <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-radio rex-form-label-right">
      <input class="rex-form-radio" type="radio" id="dbanlegen_0" name="dbanlegen" value="0"'.$dbchecked[0].' />
      <label for="dbanlegen_0">'.rex_i18n::msg('setup_034').'</label>
    </p>
  </div>

  <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-radio rex-form-label-right">
      <input class="rex-form-radio" type="radio" id="dbanlegen_1" name="dbanlegen" value="1"'.$dbchecked[1] .' />
      <label for="dbanlegen_1">'.rex_i18n::msg('setup_035', '<b>', '</b>').'</label>
    </p>
  </div>

  <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-radio rex-form-label-right">
      <input class="rex-form-radio" type="radio" id="dbanlegen_2" name="dbanlegen" value="2"'.$dbchecked[2] .' />
      <label for="dbanlegen_2">'.rex_i18n::msg('setup_036').'</label>
    </p>
  </div>';

  /*
  <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-radio rex-form-label-right">
      <input class="rex-form-radio" type="radio" id="dbanlegen_4" name="dbanlegen" value="4"'.$dbchecked[4] .' />
      <label for="dbanlegen_4">'.rex_i18n::msg('setup_038').'</label>
    </p>
  </div>';
  */

  if($exports_found)
  {
    echo '
  <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-radio rex-form-label-right">
      <input class="rex-form-radio" type="radio" id="dbanlegen_3" name="dbanlegen" value="3"'.$dbchecked[3] .' />
      <label for="dbanlegen_3">'.rex_i18n::msg('setup_037').'</label>
    </p>
    <p class="rex-form-col-a rex-form-select rex-form-radio-select">'. $sel_export->get() .'</p>
  </div>';
  }

  echo '
    </div>
    </fieldset>
    <fieldset class="rex-form-col-1">
      <div class="rex-form-wrapper">
        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-submit">
            <input class="rex-form-submit" type="submit" value="'.rex_i18n::msg('setup_039').'" />
          </p>
        </div>
      </div>
    </fieldset>
</form>
</div>
';
}

// ---------------------------------- MODUS 4 | User anlegen ...

if ($checkmodus == 4 && $send == 1)
{
  $noadmin           = rex_post('noadmin', 'int');
  $redaxo_user_login = rex_post('redaxo_user_login', 'string');
  $redaxo_user_pass  = rex_post('redaxo_user_pass', 'string');

  $err_msg = "";
  if ($noadmin != 1)
  {
    if ($redaxo_user_login == '')
    {
      $err_msg .= rex_i18n::msg('setup_040');
    }

    if ($redaxo_user_pass == '')
    {
      // Falls auch kein Login eingegeben wurde, die Fehlermeldungen mit " " trennen
      if($err_msg != '') $err_msg .= ' ';

      $err_msg .= rex_i18n::msg('setup_041');
    }

    if ($err_msg == "")
    {
      $ga = rex_sql::factory();
      $ga->setQuery("select * from ".rex::getTablePrefix()."user where login='$redaxo_user_login'");

      if ($ga->getRows() > 0)
      {
        $err_msg .= rex_i18n::msg('setup_042');
      }
      else
      {
        $login = new rex_backend_login();
        $redaxo_user_pass = $login->encryptPassword($redaxo_user_pass);

        $user = rex_sql::factory();
        // $user->debugsql = true;
        $user->setTable(rex::getTablePrefix().'user');
        $user->setValue('name', 'Administrator');
        $user->setValue('login', $redaxo_user_login);
        $user->setValue('password', $redaxo_user_pass);
        $user->setValue('admin', 1);
        $user->addGlobalCreateFields('setup');
        $user->setValue('status', '1');
        if (!$user->insert())
        {
          $err_msg .= rex_i18n::msg("setup_043");
        }
      }
    }
  }
  else
  {
    $gu = rex_sql::factory();
    $gu->setQuery("select * from ".rex::getTablePrefix()."user LIMIT 1");
    if ($gu->getRows() == 0)
    $err_msg .= rex_i18n::msg('setup_044');
  }

  if ($err_msg == '')
  {
    $checkmodus = 5;
    $send = '';
  }
}

if ($checkmodus == 4)
{
  $user_sql = rex_sql::factory();
  $user_sql->setQuery("select * from ".rex::getTablePrefix()."user LIMIT 1");

  rex_setup_title(rex_i18n::msg("setup_step4"));

  echo '
  <div class="rex-form rex-form-setup-admin">
  <form action="index.php" method="post" autocomplete="off" id="createadminform">
    <input type="hidden" name="javascript" value="0" id="javascript" />
    <fieldset class="rex-form-col-1">
      <input type="hidden" name="page" value="setup" />
      <input type="hidden" name="checkmodus" value="4" />
      <input type="hidden" name="send" value="1" />
      <input type="hidden" name="lang" value="'.$lang.'" />
      <legend>'.rex_i18n::msg("setup_045").'</legend>
      <div class="rex-form-wrapper">
      ';

  if ($err_msg != "")
    echo rex_view::warning($err_msg);

  $redaxo_user_login = rex_post('redaxo_user_login', 'string');
  $redaxo_user_pass  = rex_post('redaxo_user_pass', 'string');

  echo '
    <div class="rex-form-row">
      <p class="rex-form-col-a rex-form-text">
        <label for="redaxo_user_login">'.rex_i18n::msg("setup_046").':</label>
        <input class="rex-form-text" type="text" value="'.$redaxo_user_login.'" id="redaxo_user_login" name="redaxo_user_login" />
      </p>
    </div>
    <div class="rex-form-row">
      <p class="rex-form-col-a rex-form-text">
        <label for="redaxo_user_pass">'.rex_i18n::msg("setup_047").':</label>
        <input class="rex-form-text" type="password" value="'.$redaxo_user_pass.'" id="redaxo_user_pass" name="redaxo_user_pass" />
      </p>
    </div>';

  if($user_sql->getRows() > 0)
  {
    echo '
    <div class="rex-form-row">
      <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
        <input class="rex-form-checkbox" type="checkbox" id="noadmin" name="noadmin" value="1" />
        <label for="noadmin">'.rex_i18n::msg("setup_048").'</label>
      </p>
    </div>';
  }

  echo '
    </div>
    </fieldset>
    <fieldset class="rex-form-col-1">
      <div class="rex-form-wrapper">
        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-submit">
            <input class="rex-form-submit" type="submit" value="'.rex_i18n::msg("setup_049").'" />
          </p>
        </div>
      </div>
    </fieldset>
  </form>
  </div>
  <script type="text/javascript">
     <!--
    jQuery(function($) {
      $("#redaxo_user_login").focus();
      $("#createadminform")
        .submit(function(){
          var pwInp = $("#redaxo_user_pass");
          if(pwInp.val() != "")
          {
            $("#createadminform").append(\'<input type="hidden" name="\'+pwInp.attr("name")+\'" value="\'+Sha1.hash(pwInp.val())+\'" />\');
          }
      });

      $("#javascript").val("1");
    });
   //-->
  </script>';
}

// ---------------------------------- MODUS 5 | Setup verschieben ...

if ($checkmodus == 5)
{
  $configFile = rex_path::data('config.yml');
  $config = rex_file::getConfig($configFile);
  $config['setup'] = false;

  if(rex_file::putConfig($configFile, $config, 3))
  {
    $errmsg = "";
  }
  else
  {
    $errmsg = rex_i18n::msg('setup_050');
  }

  rex_setup_title(rex_i18n::msg('setup_step5'));
  echo rex_i18n::msg('setup_051', '<h2 class="rex-hl2">', '</h2>');
  echo '<div class="rex-area-content">';
  echo rex_i18n::msg('setup_052', '<h3 class="rex-hl3">', '</h3>', ' class="rex-ul1"', '<a href="index.php">', '</a>');
  echo '<p class="rex-tx1">'.rex_i18n::msg('setup_053').'</p>';
  echo '</div>';

}
echo '</div>';
