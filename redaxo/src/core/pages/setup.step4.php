<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($error_array) && is_array($error_array));
assert(isset($config) && is_array($config));

$headline = rex_view::title(rex_i18n::msg('setup_400'));

$content = '';

$submit_message = rex_i18n::msg('setup_410');
if (count($error_array) > 0) {
    $submit_message = rex_i18n::msg('setup_414');
}

$content .= '
            <fieldset>';

$timezone_sel = new rex_select();
$timezone_sel->setId('rex-form-timezone');
$timezone_sel->setStyle('class="form-control selectpicker"');
$timezone_sel->setAttribute('data-live-search', 'true');
$timezone_sel->setName('timezone');
$timezone_sel->setSize(1);
$timezone_sel->addOptions(DateTimeZone::listIdentifiers(), true);
$timezone_sel->setSelected($config['timezone']);

$db_create_checked = rex_post('redaxo_db_create', 'boolean') ? ' checked="checked"' : '';

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

$content .= '<legend>' . rex_i18n::msg('setup_402') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-serveraddress">' . rex_i18n::msg('server') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-serveraddress" name="serveraddress" value="' . rex_escape($config['server']) . '" autofocus />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-servername">' . rex_i18n::msg('servername') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-servername" name="servername" value="' . rex_escape($config['servername']) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-error-email">' . rex_i18n::msg('error_email') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-error-email" name="error_email" value="' . rex_escape($config['error_email']) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-timezone">' . rex_i18n::msg('setup_412') . '</label>';
$n['field'] = $timezone_sel->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset><fieldset><legend>' . rex_i18n::msg('setup_403') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for=rex-form-mysql-host">MySQL Host</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-mysql-host" name="mysql_host" value="' . rex_escape($config['db'][1]['host']) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-db-user-login">Login</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-db-user-login" name="redaxo_db_user_login" value="' . rex_escape($config['db'][1]['login']) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-db-user-pass">' . rex_i18n::msg('setup_409') . '</label>';
$n['field'] = '<input class="form-control" type="password" id="rex-form-db-user-pass" name="redaxo_db_user_pass" value="'. rex_setup::DEFAULT_DUMMY_PASSWORD .'" />';
$formElements[] = $n;

$n = [];
$n['field'] = '<p>'.rex_i18n::msg('setup_password_hint').'</p>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-dbname">' . rex_i18n::msg('setup_408') . '</label>';
$n['field'] = '<input class="form-control" type="text" value="' . rex_escape($config['db'][1]['name']) . '" id="rex-form-dbname" name="dbname" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];
$n = [];
$n['label'] = '<label>' . rex_i18n::msg('setup_411') . '</label>';
$n['field'] = '<input type="checkbox" name="redaxo_db_create" value="1"' . $db_create_checked . ' />';
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
$n['field'] = '<button class="btn btn-setup" type="submit" value="' . rex_i18n::msg('system_update') . '">' . $submit_message . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

echo $headline;
echo implode('', $error_array);

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('setup_416'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '<form action="' . $context->getUrl(['step' => 5]) . '" method="post">' . $content . '</form>';
