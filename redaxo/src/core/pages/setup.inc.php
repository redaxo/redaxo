<?php

/**
 *
 * @package redaxo5
 */


// --------------------------------------------- END: SETUP FUNCTIONS

$step       = rex_request('step', 'int', 1);
$send       = rex_request('send', 'string');
$createdb   = rex_request('createdb', 'string');
$noadmin    = rex_request('noadmin', 'string');
$lang       = rex_request('lang', 'string');

// ---------------------------------- Step 1 . Language
if ($step == 1) {

  rex_setup::init();

  $saveLocale = rex_i18n::getLocale();
  $langs = array();
  foreach (rex_i18n::getLocales() as $locale) {
    rex_i18n::setLocale($locale, false); // Locale nicht neu setzen
    $label = rex_i18n::msg('lang');
    $langs[$locale] = '<li><a class="rex-button" href="' . rex_path::backendController() . '?step=2&amp;lang=' . $locale . '">' . $label . '</a></li>';
  }
  rex_i18n::setLocale($saveLocale, false);

  $headline = rex_view::title('Setup: Step 1 / select language');
  $content = '<h2>please choose a language</h2>';
  $content .= '<ul class="rex-setup-language">' . implode('', $langs) . '</ul>';

  echo $headline . rex_view::contentBlock($content);

}

// ---------------------------------- Step 2 . license

if ($step == 2) {

  rex::setProperty('lang', $lang);

  $headline = rex_view::title(rex_i18n::msg('setup_200'));

  $content = '<h2>' . rex_i18n::msg('setup_201') . '</h2>';
  $content .= rex_i18n::msg('setup_202');

  $license_file = rex_path::base('_license.txt');
  $license = '<p>' . nl2br(rex_file::get($license_file)) . '</p>';
  $content .= '<div class="rex-content-scroll">' . $license . '</div>';
  $content .= '<p><a class="rex-button" href="' . rex_path::backendController() . '?page=setup&amp;step=3&amp;lang=' . $lang . '">' . rex_i18n::msg('setup_203') . '</a></p>';

  echo $headline . rex_view::contentBlock($content);

}

// ---------------------------------- Step 3 . Perms, Evirement

$error_array = array();
$success_array = array();

$errors = rex_setup::checkEnvironment();
if (count($errors) > 0) {
  foreach ($errors as $error) {
    $error_array[] = rex_view::error($error);
  }
} else {
  $success_array[] = rex_view::success(rex_i18n::msg('setup_308'));
}

$res = rex_setup::checkFilesystem();
if (count($res) > 0) {
  foreach ($res as $key => $messages) {
    if (count($messages) > 0) {
      $li = array();
      foreach ($messages as $message) {
        $li[] = '<li>' . $message . '</li>';
      }
      $error_array[] = rex_view::error('<p>' . rex_i18n::msg($key) . '</p><ul>' . implode('', $li) . '</ul>');
    }
  }
}

if ($step > 2 && count($error_array) > 0) {
  $step = 3;
}

if ($step == 3) {

  $headline = rex_view::title(rex_i18n::msg('setup_300'));

  $content = '<h2>' . rex_i18n::msg('setup_307') . '</h2>';
  $content .= rex_view::error(rex_i18n::msg('setup_security_msg'), 'rex-hidden rex-setup-security-message');
  $content .= '<noscript>' . rex_view::warning(rex_i18n::msg('setup_no_js_security_msg')) . '</noscript>';
  $content .= '<script>

  // TODO: Javascrpt ordnerprüfung

  // /redaxo/data/_readme.txt
  // /redaxo/src
  // /redaxo/cache
  // rex_path::cache("_readme.txt");
  // rex_path::src("_readme.txt");

  jQuery(function($){

    $.ajax({
      url: "' . rex_path::backend('data/_readme.txt') . '",
      success: function(data) {
        $(".rex-setup-security-message").removeClass("rex-hidden");
      },
      error: function(data) {
        $.ajax({
          url: "' . rex_path::backend('src/_readme.txt') . '",
          success: function(data) {
            $(".rex-setup-security-message").removeClass("rex-hidden");
          },
          error: function(data) {
            $.ajax({
              url: "' . rex_path::backend('cache/_readme.txt') . '",
              success: function(data) {
                $(".rex-setup-security-message").removeClass("rex-hidden");
              },
              error: function(data) {
                $(".rex-content .rex-button").removeClass("rex-hidden");
              }
            });
          }
        });
      }
    });

  })

  </script>';


  if (count($success_array) > 0) {
    foreach ($success_array as $s) {
      $content .= $s;
    }
  }

  if (count($error_array) > 0) {
    foreach ($error_array as $error) {
      $content .= $error;
    }

    $content .= rex_view::error(rex_i18n::msg('setup_310'));
    $content .= '<p><a class="rex-button rex-hidden" href="' . rex_path::backendController() . '?page=setup&amp;step=4&amp;lang=' . $lang . '">' . rex_i18n::msg('setup_311') . '</a></p>';

  } else {
    $content .= '<p><a class="rex-button rex-hidden" href="' . rex_path::backendController() . '?page=setup&amp;step=4&amp;lang=' . $lang . '">' . rex_i18n::msg('setup_309') . '</a></p>';
  }

  echo $headline . rex_view::contentBlock($content);

}


// ---------------------------------- step 4 . Config

$error_array = array();

if ($step >= 4) {
  $configFile = rex_path::data('config.yml');
  $config = rex_file::getConfig($configFile);
}

if ($step > 4 && rex_post('serveraddress', 'string', '-1') != '-1') {
  $config['server']            = rex_post('serveraddress', 'string');
  $config['servername']        = rex_post('servername', 'string');
  $config['lang']              = $lang;
  $config['error_email']       = rex_post('error_email', 'string');
  $config['timezone']          = rex_post('timezone', 'string');
  $config['db'][1]['host']     = rex_post('mysql_host', 'string');
  $config['db'][1]['login']    = rex_post('redaxo_db_user_login', 'string');
  $config['db'][1]['password'] = rex_post('redaxo_db_user_pass', 'string');
  $config['db'][1]['name']     = rex_post('dbname', 'string');

}

if ($step > 4) {

  $redaxo_db_create            = rex_post('redaxo_db_create', 'boolean');

  if (empty($config['instname'])) {
    $config['instname'] = 'rex' . date('YmdHis');
  }

  // check if timezone is valid
  if (@date_default_timezone_set($config['timezone']) === false) {
    $error_array[] = rex_view::error(rex_i18n::msg('setup_413'));
  }

  if (empty($config['error_email'])) {
    $error_array[] = rex_view::error(rex_i18n::msg('error_email_required'));
  }

  foreach ($config as $key => $value) {
    if (in_array($key, array('fileperm', 'dirperm'))) {
      $value = octdec($value);
    }
    rex::setProperty($key, $value);
  }

  if (count($error_array) == 0) {
    if (!rex_file::putConfig($configFile, $config)) {
      $error_array[] = rex_view::error(rex_i18n::msg('setup_401'));
    }
  }

  if (count($error_array) == 0) {
    $err = rex_setup::checkDb($config, $redaxo_db_create);
    if ($err != '') {
      $error_array[] = rex_view::error($err);
    }
  }

  if (count($error_array) > 0) {
    $step = 4;
  }
}

if ($step == 4) {
  $headline = rex_view::title(rex_i18n::msg('setup_400'));

  $content = '';
  $content .= implode('', $error_array);

  $submit_message = rex_i18n::msg('setup_410');
  if (count($error_array) > 0)
    $submit_message = rex_i18n::msg('setup_414');

  $content .= '
      <div class="rex-form" id="rex-form-setup-step-4">
      <form action="' . rex_path::backendController() . '" method="post">
      <fieldset>
        <input type="hidden" name="page" value="setup" />
        <input type="hidden" name="step" value="5" />
        <input type="hidden" name="lang" value="' . $lang . '" />';

  $timezone_sel = new rex_select;
  $timezone_sel->setId('rex-form-timezone');
  $timezone_sel->setName('timezone');
  $timezone_sel->setSize(1);
  $timezone_sel->addOptions(DateTimeZone::listIdentifiers(), true);
  $timezone_sel->setSelected($config['timezone']);

  $db_create_checked = rex_post('redaxo_db_create', 'boolean') ? ' checked="checked"' : '';

  $content .= '<h2>' . rex_i18n::msg('setup_402') . '</h2>';

  $formElements = array();

    $n = array();
    $n['label'] = '<label for="rex-form-serveraddress">' . rex_i18n::msg('setup_406') . '</label>';
    $n['field'] = '<input class="rex-form-text" type="text" id="rex-form-serveraddress" name="serveraddress" value="' . $config['server'] . '" />';
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for="rex-form-servername">' . rex_i18n::msg('setup_407') . '</label>';
    $n['field'] = '<input class="rex-form-text" type="text" id="rex-form-servername" name="servername" value="' . $config['servername'] . '" />';
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for="rex-form-error-email">' . rex_i18n::msg('error_email') . '</label>';
    $n['field'] = '<input class="rex-form-text" type="text" id="rex-form-error-email" name="error_email" value="' . $config['error_email'] . '" />';
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for="rex-form-timezone">' . rex_i18n::msg('setup_412') . '</label>';
    $n['field'] = $timezone_sel->get();
    $formElements[] = $n;

  $fragment = new rex_fragment();
  $fragment->setVar('elements', $formElements, false);
  $content .= $fragment->parse('form.tpl');


   $content .= '</fieldset><fieldset><h2>' . rex_i18n::msg('setup_403') . '</h2>';



  $formElements = array();

    $n = array();
    $n['label'] = '<label for="rex-form-dbname">' . rex_i18n::msg('setup_408') . '</label>';
    $n['field'] = '<input class="rex-form-text" type="text" value="' . $config['db'][1]['name'] . '" id="rex-form-dbname" name="dbname" />';
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for=rex-form-mysql-host">MySQL Host</label>';
    $n['field'] = '<input class="rex-form-text" type="text" id="rex-form-mysql-host" name="mysql_host" value="' . $config['db'][1]['host'] . '" />';
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for="rex-form-db-user-login">Login</label>';
    $n['field'] = '<input class="rex-form-text" type="text" id="rex-form-db-user-login" name="redaxo_db_user_login" value="' . $config['db'][1]['login'] . '" />';
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for="rex-form-db-user-pass">' . rex_i18n::msg('setup_409') . '</label>';
    $n['field'] = '<input class="rex-form-text" type="text" id="rex-form-db-user-pass" name="redaxo_db_user_pass" value="' . $config['db'][1]['password'] . '" />';
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for="rex-form-db-create">' . rex_i18n::msg('setup_411') . '</label>';
    $n['field'] = '<input class="rex-form-checkbox" type="checkbox" id="rex-form-db-create" name="redaxo_db_create" value="1"' . $db_create_checked . ' />';
    $formElements[] = $n;

  $fragment = new rex_fragment();
  $fragment->setVar('elements', $formElements, false);
  $content .= $fragment->parse('form.tpl');

  $content .= '</fieldset><fieldset class="rex-form-action">';


  $formElements = array();

    $n = array();
    $n['field'] = '<input class="rex-form-submit" type="submit" value="' . rex_i18n::msg('system_update') . '" />';
    $formElements[] = $n;

  $fragment = new rex_fragment();
  $fragment->setVar('elements', $formElements, false);
  $content .= $fragment->parse('form.tpl');


   $content .= '</fieldset>
      </form>
      </div>
      <script type="text/javascript">
         <!--
        jQuery(function($) {
          $("#serveraddress").focus();
        });
         //-->
      </script>';

  echo $headline . rex_view::contentBlock($content, '', 'block');
}




// ---------------------------------- step 5 . create db / demo

$errors = array();

$createdb = rex_post('createdb', 'int', -1);

if ($step > 5 && $createdb > -1) {

  if ($createdb == 4) {
    $error = rex_setup_importer::updateFromPrevious();
    if ($error != '')
      $errors[] = rex_view::error($error);
  } elseif ($createdb == 3) {
    $import_name = rex_post('import_name', 'string');
    $error = rex_setup_importer::loadExistingImport($import_name);
    if ($error != '')
      $errors[] = rex_view::error($error);

  } elseif ($createdb == 2) {
    $error = rex_setup_importer::databaseAlreadyExists();
    if ($error != '')
      $errors[] = rex_view::error($error);

  } elseif ($createdb == 1) {
    $error = rex_setup_importer::overrideExisting();
    if ($error != '')
      $errors[] = rex_view::error($error);

  } elseif ($createdb == 0) {
    $error = rex_setup_importer::prepareEmptyDb();
    if ($error != '')
      $errors[] = rex_view::error($error);
  }

  if (count($errors) == 0 && $createdb !== '') {
    $error = rex_setup_importer::verifyDbSchema();
    if ($error != '')
      $errors[] = $error;
  }

  if (count($errors) == 0) {
    rex_clang_service::generateCache();
  } else {
    $step = 5;
  }
}

if ($step == 5) {
  $createdb = rex_post('createdb', 'int', '');

  $headline = rex_view::title(rex_i18n::msg('setup_500'));

  $content = '

      <h2>' . rex_i18n::msg('setup_501') . '</h2>

    <div class="rex-form" id="rex-form-setup-step-5">
      <form action="' . rex_path::backendController() . '" method="post">
      <fieldset>
        <input type="hidden" name="page" value="setup" />
        <input type="hidden" name="step" value="6" />
        <input type="hidden" name="lang" value="' . $lang . '" />
      ';

  $submit_message = rex_i18n::msg('setup_511');
  if (count($errors) > 0) {
    $errors[] = rex_view::error(rex_i18n::msg('setup_503'));
    $content .= implode('', $errors);
    $submit_message = rex_i18n::msg('setup_512');
  }

  $dbchecked = array_fill(0, 6, '');
  switch ($createdb) {
    case 1 :
    case 2 :
    case 3 :
    case 4 :
      $dbchecked[$createdb] = ' checked="checked"';
      break;
    default :
      $dbchecked[0] = ' checked="checked"';
  }

  $export_addon_dir = rex_path::addon('import_export');
  require_once $export_addon_dir . '/functions/function_folder.inc.php';
  require_once $export_addon_dir . '/functions/function_import_folder.inc.php';

  // Vorhandene Exporte auslesen
  $sel_export = new rex_select();
  $sel_export->setName('import_name');
  $sel_export->setId('rex-form-import-name');
  $sel_export->setStyle('class="rex-form-select"');
  $sel_export->setAttribute('onclick', 'checkInput(\'createdb_3\')');
  $export_dir = getImportDir();
  $exports_found = false;

  if (is_dir($export_dir)) {
    if ($handle = opendir($export_dir)) {
      $export_archives = array();
      $export_sqls = array();

      while (($file = readdir($handle)) !== false) {
        if ($file == '.' || $file == '..') {
          continue;
        }

        $isSql = (substr($file, strlen($file) - 4) == '.sql');
        $isArchive = (substr($file, strlen($file) - 7) == '.tar.gz');

        if ($isSql) {
          // cut .sql
          $export_sqls[] = substr($file, 0, -4);
          $exports_found = true;
        } elseif ($isArchive) {
          // cut .tar.gz
          $export_archives[] = substr($file, 0, -7);
          $exports_found = true;
        }
      }
      closedir($handle);
    }

    foreach ($export_sqls as $sql_export) {
      if (in_array($sql_export, $export_archives)) {
        $sel_export->addOption($sql_export, $sql_export);
      }
    }
  }



  $formElements = array();

    $n = array();
    $n['reverse'] = true;
    $n['label'] = '<label for="rex-form-createdb-0">' . rex_i18n::msg('setup_504') . '</label>';
    $n['field'] = '<input class="rex-form-radio" type="radio" id="rex-form-createdb-0" name="createdb" value="0"' . $dbchecked[0] . ' />';
    $formElements[] = $n;

    $n = array();
    $n['reverse'] = true;
    $n['label'] = '<label for="rex-form-createdb-1">' . rex_i18n::msg('setup_505') . '</label>';
    $n['field'] = '<input class="rex-form-radio" type="radio" id="rex-form-createdb-1" name="createdb" value="1"' . $dbchecked[1] . ' />';
    $formElements[] = $n;

    $n = array();
    $n['reverse'] = true;
    $n['label'] = '<label for="rex-form-createdb-2">' . rex_i18n::msg('setup_506') . '</label>';
    $n['field'] = '<input class="rex-form-radio" type="radio" id="rex-form-createdb-2" name="createdb" value="2"' . $dbchecked[2] . ' />';
    $formElements[] = $n;

  if ($exports_found) {
    $n = array();
    $n['reverse'] = true;
    $n['label'] = '<label for="rex-form-createdb-3">' . rex_i18n::msg('setup_507') . '</label>';
    $n['field'] = '<input class="rex-form-radio" type="radio" id="rex-form-createdb-3" name="createdb" value="3"' . $dbchecked[3] . ' />';
    $n['after'] =  $sel_export->get();
    $formElements[] = $n;
  }

  $fragment = new rex_fragment();
  $fragment->setVar('elements', $formElements, false);
  $content .= $fragment->parse('form.tpl');


  $content .= '</fieldset><fieldset class="rex-form-action">';

  $formElements = array();

    $n = array();
    $n['field'] = '<input class="rex-form-submit" type="submit" value="' . $submit_message . '" />';
    $formElements[] = $n;

  $fragment = new rex_fragment();
  $fragment->setVar('elements', $formElements, false);
  $content .= $fragment->parse('form.tpl');

  $content .= '</fieldset></form></div>
  ';

  echo $headline . rex_view::contentBlock($content, '', 'block');
}




// ---------------------------------- Step 7 . Create User

$errors = array();

if ($step == 7) {
  $noadmin           = rex_post('noadmin', 'int');
  $redaxo_user_login = rex_post('redaxo_user_login', 'string');
  $redaxo_user_pass  = rex_post('redaxo_user_pass', 'string');

  if ($noadmin != 1) {
    if ($redaxo_user_login == '') {
      $errors[] = rex_view::error(rex_i18n::msg('setup_601'));
    }

    if ($redaxo_user_pass == '') {
      $errors[] = rex_view::error(rex_i18n::msg('setup_602'));
    }

    if (count($errors) == 0) {
      $ga = rex_sql::factory();
      $ga->setQuery('select * from ' . rex::getTablePrefix() . 'user where login = ? ', array($redaxo_user_login));

      if ($ga->getRows() > 0) {
        $errors[] = rex_view::error(rex_i18n::msg('setup_603'));
      } else {
        $login = new rex_backend_login();
        $redaxo_user_pass = $login->encryptPassword($redaxo_user_pass);

        $user = rex_sql::factory();
        // $user->debugsql = true;
        $user->setTable(rex::getTablePrefix() . 'user');
        $user->setValue('name', 'Administrator');
        $user->setValue('login', $redaxo_user_login);
        $user->setValue('password', $redaxo_user_pass);
        $user->setValue('admin', 1);
        $user->addGlobalCreateFields('setup');
        $user->setValue('status', '1');
        if (!$user->insert()) {
          $errors[] = rex_view::error(rex_i18n::msg('setup_604'));
        }
      }
    }
  } else {
    $gu = rex_sql::factory();
    $gu->setQuery('select * from ' . rex::getTablePrefix() . 'user LIMIT 1');
    if ($gu->getRows() == 0)
      $errors[] = rex_view::error(rex_i18n::msg('setup_605'));
  }

  if (count($errors) == 0) {
    $step = 7;
  } else {
    $step = 6;
  }
}

if ($step == 6) {
  $user_sql = rex_sql::factory();
  $user_sql->setQuery('select * from ' . rex::getTablePrefix() . 'user LIMIT 1');

  $headline = rex_view::title(rex_i18n::msg('setup_600'));

  $content = '<h2>' . rex_i18n::msg('setup_606') . '</h2>';

  $submit_message = rex_i18n::msg('setup_610');
  if (count($errors) > 0) {
    $submit_message = rex_i18n::msg('setup_611');
    $content .= implode('', $errors);
  }

  $content .= '
  <div class="rex-form" id="rex-form-setup-step-6">
  <form action="' . rex_path::backendController() . '" method="post" autocomplete="off" id="createadminform">
    <fieldset>
      <input type="hidden" name="javascript" value="0" id="javascript" />
      <input type="hidden" name="page" value="setup" />
      <input type="hidden" name="step" value="7" />
      <input type="hidden" name="lang" value="' . $lang . '" />

      ';

  $redaxo_user_login = rex_post('redaxo_user_login', 'string');
  $redaxo_user_pass  = rex_post('redaxo_user_pass', 'string');




  $formElements = array();

    $n = array();
    $n['label'] = '<label for="rex-form-redaxo-user-login">' . rex_i18n::msg('setup_607') . '</label>';
    $n['field'] = '<input class="rex-form-text" type="text" value="' . $redaxo_user_login . '" id="rex-form-redaxo-user-login" name="redaxo_user_login" />';
    $formElements[] = $n;

    $n = array();
    $n['label'] = '<label for="rex-form-redaxo-user-pass">' . rex_i18n::msg('setup_608') . '</label>';
    $n['field'] = '<input class="rex-form-text" type="password" value="' . $redaxo_user_pass . '" id="rex-form-redaxo-user-pass" name="redaxo_user_pass" />';
    $formElements[] = $n;

  if ($user_sql->getRows() > 0) {
    $n = array();
    $n['reverse'] = true;
    $n['label'] = '<label for="rex-form-noadmin">' . rex_i18n::msg('setup_609') . '</label>';
    $n['field'] = '<input class="rex-form-checkbox" type="checkbox" id="rex-form-noadmin" name="noadmin" value="1" />';
    $formElements[] = $n;
  }

  $fragment = new rex_fragment();
  $fragment->setVar('elements', $formElements, false);
  $content .= $fragment->parse('form.tpl');


  $content .= '</fieldset><fieldset class="rex-form-action">';


  $formElements = array();

    $n = array();
    $n['field'] = '<input class="rex-form-submit" type="submit" value="' . $submit_message . '" />';
    $formElements[] = $n;

  $fragment = new rex_fragment();
  $fragment->setVar('elements', $formElements, false);
  $content .= $fragment->parse('form.tpl');

  $content .= '</fieldset></form></div>

  <script type="text/javascript">
     <!--
    jQuery(function($) {
      $("#rex-form-redaxo-user-login").focus();
      $("#createadminform")
        .submit(function(){
          var pwInp = $("#rex-form-redaxo-user-pass");
          if(pwInp.val() != "")
          {
            $("#createadminform").append(\'<input type="hidden" name="\'+pwInp.attr("name")+\'" value="\'+Sha1.hash(pwInp.val())+\'" />\');
          }
      });

      $("#javascript").val("1");
    });
   //-->
  </script>';

  echo $headline . rex_view::contentBlock($content, '', 'block');

}

// ---------------------------------- step 7 . thank you . setup false

if ($step == 7) {

  $configFile = rex_path::data('config.yml');
  $config = rex_file::getConfig($configFile);
  $config['setup'] = false;

  if (rex_file::putConfig($configFile, $config)) {
    $errmsg = '';
  } else {
    $errmsg = rex_i18n::msg('setup_701');
  }

  $headline = rex_view::title(rex_i18n::msg('setup_700'));

  $content = '<h2>' . rex_i18n::msg('setup_702') . '</h2>';
  $content .= '<h3>' . rex_i18n::msg('setup_703') . '</h3>';
  $content .= rex_i18n::msg('setup_704', '<a href="' . rex_path::backendController() . '">', '</a>');
  $content .= '<p>' . rex_i18n::msg('setup_705') . '</p>';

  $content .= '<p><a class="rex-button" href="' . rex_path::backendController() . '">' . rex_i18n::msg('setup_706') . '</a></p>';

  echo $headline . rex_view::contentBlock($content);

}
