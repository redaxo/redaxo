<?php

use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Form\Select\Select;
use Redaxo\Core\Http\Context;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\View;

assert(isset($context) && $context instanceof Context);
assert(isset($errorArray) && is_array($errorArray));
assert(isset($config) && is_array($config));
assert(isset($cancelSetupBtn));

$configFile = Path::coreData('config.yml');
$headline = View::title(I18n::msg('setup_300', Path::relative($configFile)) . $cancelSetupBtn);

$content = '';

$submitMessage = I18n::msg('setup_310');
if (count($errorArray) > 0) {
    $submitMessage = I18n::msg('setup_314');
}

$content .= '
            <fieldset>';

$timezoneSel = new Select();
$timezoneSel->setId('rex-form-timezone');
$timezoneSel->setStyle('class="form-control selectpicker"');
$timezoneSel->setAttribute('data-live-search', 'true');
$timezoneSel->setName('timezone');
$timezoneSel->setSize(1);
$timezoneSel->addOptions(DateTimeZone::listIdentifiers(), true);
$timezoneSel->setSelected($config['timezone']);

$dbCreateChecked = Request::post('redaxo_db_create', 'boolean') ? ' checked="checked"' : '';

$httpsRedirectSel = new Select();
$httpsRedirectSel->setId('rex-form-https');
$httpsRedirectSel->setStyle('class="form-control selectpicker"');
$httpsRedirectSel->setName('use_https');
$httpsRedirectSel->setSize(1);
$httpsRedirectSel->addArrayOptions(['false' => I18n::msg('https_disable'), 'backend' => I18n::msg('https_only_backend'), 'frontend' => I18n::msg('https_only_frontend'), 'true' => I18n::msg('https_activate')]);
$httpsRedirectSel->setSelected(true === $config['use_https'] ? 'true' : $config['use_https']);

// If the setup is called over http disable https options to prevent user from being locked out
if (!Request::isHttps()) {
    $httpsRedirectSel->setAttribute('disabled', 'disabled');
}

$content .= '<legend>' . I18n::msg('setup_302') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-serveraddress" class="required">' . I18n::msg('server') . '</label>';
$n['field'] = '<input class="form-control" type="url" id="rex-form-serveraddress" name="serveraddress" value="' . rex_escape($config['server']) . '" required autofocus />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-servername" class="required">' . I18n::msg('servername') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-servername" name="servername" value="' . rex_escape($config['servername']) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-error-email" class="required">' . I18n::msg('error_email') . '</label>';
$n['field'] = '<input class="form-control" type="email" id="rex-form-error-email" name="error_email" value="' . rex_escape($config['error_email']) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-timezone" class="required">' . I18n::msg('setup_312') . '</label>';
$n['field'] = $timezoneSel->get();
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset><fieldset><legend>' . I18n::msg('setup_303') . '</legend>';

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
$n['label'] = '<label for="rex-form-db-user-pass" class="required">' . I18n::msg('setup_309') . '</label>';
$n['field'] = '<input class="form-control" type="password" id="rex-form-db-user-pass" name="redaxo_db_user_pass" value="' . rex_setup::DEFAULT_DUMMY_PASSWORD . '" />';
$formElements[] = $n;

$n = [];
$n['field'] = '<p>' . I18n::msg('setup_password_hint') . '</p>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-dbname" class="required">' . I18n::msg('setup_308') . '</label>';
$n['field'] = '<input class="form-control" type="text" value="' . rex_escape($config['db'][1]['name']) . '" id="rex-form-dbname" name="dbname" required />';
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];
$n = [];
$n['label'] = '<label>' . I18n::msg('setup_311') . '</label>';
$n['field'] = '<input type="checkbox" name="redaxo_db_create" value="1"' . $dbCreateChecked . ' />';
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$content .= '</fieldset><fieldset><legend>' . I18n::msg('setup_security') . '</legend>';

$formElements = [];

if (!Request::isHttps()) {
    $n = [];
    $n['field'] = '<label class="form-control-static"><i class="fa fa-warning"></i> ' . I18n::msg('https_only_over_https') . '</label>';
    $formElements[] = $n;
}

$n = [];
$n['label'] = '<label>' . I18n::msg('https_activate_redirect_for') . '</label>';
$n['field'] = $httpsRedirectSel->get();
$formElements[] = $n;

$n = [];
$n['field'] = '<p>' . I18n::msg('hsts_more_information') . '</p>';
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-setup" type="submit" value="' . I18n::msg('system_update') . '">' . $submitMessage . '</button>';
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

echo $headline;
echo implode('', $errorArray);

$fragment = new Fragment();
$fragment->setVar('title', I18n::msg('setup_316'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '<form action="' . $context->getUrl(['step' => 4]) . '" method="post">' . $content . '</form>';
