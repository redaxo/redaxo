<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($errorArray) && is_array($errorArray));
assert(isset($config) && is_array($config));
assert(isset($cancelSetupBtn));

$configFile = rex_path::coreData('config.yml');
$headline = rex_view::title(rex_i18n::msg('setup_300', rex_path::relative($configFile)).$cancelSetupBtn);

$content = '';

$submitMessage = rex_i18n::msg('setup_310');
if (count($errorArray) > 0) {
    $submitMessage = rex_i18n::msg('setup_314');
}

$content .= '
            <fieldset>';

$timezoneSel = new rex_select();
$timezoneSel->setId('rex-form-timezone');
$timezoneSel->setStyle('class="form-control selectpicker"');
$timezoneSel->setAttribute('data-live-search', 'true');
$timezoneSel->setName('timezone');
$timezoneSel->setSize(1);
$timezoneSel->addOptions(DateTimeZone::listIdentifiers(), true);
$timezoneSel->setSelected($config['timezone']);

$dbCreateChecked = rex_post('redaxo_db_create', 'boolean') ? ' checked="checked"' : '';

$httpsRedirectSel = new rex_select();
$httpsRedirectSel->setId('rex-form-https');
$httpsRedirectSel->setStyle('class="form-control selectpicker"');
$httpsRedirectSel->setName('use_https');
$httpsRedirectSel->setSize(1);
$httpsRedirectSel->addArrayOptions(['false' => rex_i18n::msg('https_disable'), 'backend' => rex_i18n::msg('https_only_backend'), 'frontend' => rex_i18n::msg('https_only_frontend'), 'true' => rex_i18n::msg('https_activate')]);
$httpsRedirectSel->setSelected(true === $config['use_https'] ? 'true' : $config['use_https']);

// If the setup is called over http disable https options to prevent user from being locked out
if (!rex_request::isHttps()) {
    $httpsRedirectSel->setAttribute('disabled', 'disabled');
}

$content .= '<legend>' . rex_i18n::msg('setup_302') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-serveraddress" class="required">' . rex_i18n::msg('server') . '</label>';
$n['field'] = '<input class="form-control" type="url" id="rex-form-serveraddress" name="serveraddress" value="' . rex_escape($config['server']) . '" required autofocus />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-servername" class="required">' . rex_i18n::msg('servername') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-servername" name="servername" value="' . rex_escape($config['servername']) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-error-email" class="required">' . rex_i18n::msg('error_email') . '</label>';
$n['field'] = '<input class="form-control" type="email" id="rex-form-error-email" name="error_email" value="' . rex_escape($config['error_email']) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-timezone" class="required">' . rex_i18n::msg('setup_312') . '</label>';
$n['field'] = $timezoneSel->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset><fieldset><legend>' . rex_i18n::msg('setup_303') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for=rex-form-mysql-host" class="required">MySQL Host</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-mysql-host" name="mysql_host" value="' . rex_escape($config['db'][1]['host']) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-db-user-login" class="required">Login</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-db-user-login" name="redaxo_db_user_login" value="' . rex_escape($config['db'][1]['login']) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-db-user-pass" class="required">' . rex_i18n::msg('setup_309') . '</label>';
$n['field'] = '<input class="form-control" type="password" id="rex-form-db-user-pass" name="redaxo_db_user_pass" value="'. rex_setup::DEFAULT_DUMMY_PASSWORD .'" />';
$formElements[] = $n;

$n = [];
$n['field'] = '<p>'.rex_i18n::msg('setup_password_hint').'</p>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-dbname" class="required">' . rex_i18n::msg('setup_308') . '</label>';
$n['field'] = '<input class="form-control" type="text" value="' . rex_escape($config['db'][1]['name']) . '" id="rex-form-dbname" name="dbname" required />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];
$n = [];
$n['label'] = '<label>' . rex_i18n::msg('setup_311') . '</label>';
$n['field'] = '<input type="checkbox" name="redaxo_db_create" value="1"' . $dbCreateChecked . ' />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$content .= '</fieldset><fieldset><legend>' . rex_i18n::msg('setup_security') . '</legend>';

$formElements = [];

if (!rex_request::isHttps()) {
    $n = [];
    $n['field'] = '<label class="form-control-static"><i class="fa fa-warning"></i> '.rex_i18n::msg('https_only_over_https').'</label>';
    $formElements[] = $n;
}

$n = [];
$n['label'] = '<label>'.rex_i18n::msg('https_activate_redirect_for').'</label>';
$n['field'] = $httpsRedirectSel->get();
$formElements[] = $n;

$n = [];
$n['field'] = '<p>'.rex_i18n::msg('hsts_more_information').'</p>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-setup" type="submit" value="' . rex_i18n::msg('system_update') . '">' . $submitMessage . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

echo $headline;
echo implode('', $errorArray);

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('setup_316'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '<form action="' . $context->getUrl(['step' => 4]) . '" method="post">' . $content . '</form>';
