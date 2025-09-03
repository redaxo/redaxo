<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($errorArray) && is_array($errorArray));
assert(isset($config) && is_array($config));
assert(isset($cancelSetupBtn));

$configFile = rex_path::coreData('config.yml');
$headline = rex_view::title(rex_i18n::msg('setup_300', rex_path::relative($configFile)) . $cancelSetupBtn);

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

// SSL configuration state - prioritize POST data, fallback to config values
$sslToggleChecked = '';
$sslCaMode = '';
$sslCaFile = '';
$sslKey = '';
$sslCert = '';
$sslVerifyServerCert = true;

// First, load from existing config
$dbConfig = [];
if (isset($config['db']) && is_array($config['db']) && isset($config['db'][1]) && is_array($config['db'][1])) {
    $dbConfig = $config['db'][1];
}

if (isset($dbConfig['ssl_ca'])
    || isset($dbConfig['ssl_key'])
    || isset($dbConfig['ssl_cert'])
    || isset($dbConfig['ssl_verify_server_cert'])) {
    $sslToggleChecked = ' checked="checked"';
}

if (isset($dbConfig['ssl_ca'])) {
    if (true === $dbConfig['ssl_ca']) {
        $sslCaMode = 'system';
    } elseif (is_string($dbConfig['ssl_ca']) && !empty($dbConfig['ssl_ca'])) {
        $sslCaMode = 'file';
        $sslCaFile = $dbConfig['ssl_ca'];
    }
}

$sslKey = (string) ($dbConfig['ssl_key'] ?? '');
$sslCert = (string) ($dbConfig['ssl_cert'] ?? '');
$sslVerifyServerCert = (bool) ($dbConfig['ssl_verify_server_cert'] ?? true);

// Override with POST data if available (form submission)
if (rex_post('ssl_toggle', 'boolean')) {
    $sslToggleChecked = ' checked="checked"';
    $sslCaMode = rex_post('db_ssl_ca_mode', 'string', $sslCaMode);
    $sslCaFile = rex_post('db_ssl_ca_file', 'string', $sslCaFile);
    $sslKey = rex_post('db_ssl_key', 'string', $sslKey);
    $sslCert = rex_post('db_ssl_cert', 'string', $sslCert);
    $sslVerifyServerCert = rex_post('db_ssl_verify_server_cert', 'boolean', $sslVerifyServerCert);
} elseif (!empty($_POST)) {
    // Form was submitted but SSL toggle was unchecked - clear SSL toggle
    $sslToggleChecked = '';
}
$sslKey = rex_post('db_ssl_key', 'string', '');
$sslCert = rex_post('db_ssl_cert', 'string', '');
$sslVerifyServerCert = rex_post('db_ssl_verify_server_cert', 'boolean', true);

// Fallback to config values if no POST data
if (!rex_post('ssl_toggle')) {
    if (isset($dbConfig['ssl_ca'])
        || isset($dbConfig['ssl_key'])
        || isset($dbConfig['ssl_cert'])) {
        $sslToggleChecked = ' checked="checked"';
    }

    if (isset($dbConfig['ssl_ca'])) {
        if (true === $dbConfig['ssl_ca']) {
            $sslCaMode = 'system';
        } elseif (is_string($dbConfig['ssl_ca']) && !empty($dbConfig['ssl_ca'])) {
            $sslCaMode = 'file';
            $sslCaFile = $dbConfig['ssl_ca'];
        }
    }

    $sslKey = (string) ($dbConfig['ssl_key'] ?? '');
    $sslCert = (string) ($dbConfig['ssl_cert'] ?? '');
    $sslVerifyServerCert = (bool) ($dbConfig['ssl_verify_server_cert'] ?? true);
}

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
$n['field'] = '<input class="form-control" type="text" id="rex-form-mysql-host" name="mysql_host" value="' . rex_escape((string) ($dbConfig['host'] ?? '')) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-db-user-login" class="required">Login</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-db-user-login" name="redaxo_db_user_login" value="' . rex_escape((string) ($dbConfig['login'] ?? '')) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-db-user-pass" class="required">' . rex_i18n::msg('setup_309') . '</label>';
$n['field'] = '<input class="form-control" type="password" id="rex-form-db-user-pass" name="redaxo_db_user_pass" value="' . rex_setup::DEFAULT_DUMMY_PASSWORD . '" />';
$formElements[] = $n;

$n = [];
$n['field'] = '<p>' . rex_i18n::msg('setup_password_hint') . '</p>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-dbname" class="required">' . rex_i18n::msg('setup_308') . '</label>';
$n['field'] = '<input class="form-control" type="text" value="' . rex_escape((string) ($dbConfig['name'] ?? '')) . '" id="rex-form-dbname" name="dbname" required />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

// SSL Configuration Section
$formElements = [];

// SSL Toggle Checkbox  
$n = [];
$n['label'] = '<label>' . rex_i18n::msg('setup_database_ssl') . '</label>';
$n['field'] = '<input type="checkbox" data-ssl-toggle="true" name="ssl_toggle" value="1"' . $sslToggleChecked . ' />';
$n['note'] = rex_i18n::msg('setup_database_ssl_info');
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

// SSL Configuration Fields (collapsible)
$content .= '<div id="ssl-config-panel" style="display: none;">';

$sslFormElements = [];

// SSL CA Configuration
$sslCaSel = new rex_select();
$sslCaSel->setId('rex-form-ssl-ca-mode');
$sslCaSel->setStyle('class="form-control selectpicker"');
$sslCaSel->setName('db_ssl_ca_mode');
$sslCaSel->setSize(1);
$sslCaSel->addOption(rex_i18n::msg('setup_ssl_ca_none'), '');
$sslCaSel->addOption(rex_i18n::msg('setup_ssl_ca_system'), 'system');
$sslCaSel->addOption(rex_i18n::msg('setup_ssl_ca_file'), 'file');
$sslCaSel->setSelected($sslCaMode);

$n = [];
$n['label'] = '<label for="rex-form-ssl-ca-mode">' . rex_i18n::msg('setup_ssl_ca') . '</label>';
$n['field'] = $sslCaSel->get();
$sslFormElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-ssl-ca-file">' . rex_i18n::msg('setup_ssl_ca_file_path') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-ssl-ca-file" name="db_ssl_ca_file" value="' . rex_escape($sslCaFile) . '" placeholder="/path/to/ca.pem" data-ssl-field="file" />';
$sslFormElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-ssl-key">' . rex_i18n::msg('setup_ssl_key') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-ssl-key" name="db_ssl_key" value="' . rex_escape($sslKey) . '" placeholder="/path/to/key.pem" />';
$sslFormElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-ssl-cert">' . rex_i18n::msg('setup_ssl_cert') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-form-ssl-cert" name="db_ssl_cert" value="' . rex_escape($sslCert) . '" placeholder="/path/to/cert.pem" />';
$sslFormElements[] = $n;

// SSL verify server cert checkbox (separate for proper styling)
$sslVerifyFormElements = [];
$n = [];
$n['label'] = '<label>' . rex_i18n::msg('setup_ssl_verify') . '</label>';
$sslVerifyChecked = $sslVerifyServerCert ? ' checked="checked"' : '';
$n['field'] = '<input type="checkbox" id="rex-form-ssl-verify" name="db_ssl_verify_server_cert" value="1"' . $sslVerifyChecked . ' />';
$sslVerifyFormElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $sslFormElements, false);
$content .= $fragment->parse('core/form/form.php');

$fragment = new rex_fragment();
$fragment->setVar('elements', $sslVerifyFormElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$content .= '</div>'; // End SSL config panel

// Database Create Checkbox
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
    $n['field'] = '<label class="form-control-static"><i class="fa fa-warning"></i> ' . rex_i18n::msg('https_only_over_https') . '</label>';
    $formElements[] = $n;
}

$n = [];
$n['label'] = '<label>' . rex_i18n::msg('https_activate_redirect_for') . '</label>';
$n['field'] = $httpsRedirectSel->get();
$formElements[] = $n;

$n = [];
$n['field'] = '<p>' . rex_i18n::rawMsg('hsts_more_information') . '</p>';
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

?>
<script type="text/javascript" nonce="<?= rex_response::getNonce() ?>">
jQuery(document).ready(function($) {
    // SSL Panel Toggle
    $('input[data-ssl-toggle="true"]').on('change', function() {
        var panel = $('#ssl-config-panel');
        if ($(this).is(':checked')) {
            panel.slideDown(300);
        } else {
            panel.slideUp(300);
        }
    });
    
    // Show SSL panel if checkbox is checked (from POST data or config)
    if ($('input[data-ssl-toggle="true"]').is(':checked')) {
        $('#ssl-config-panel').show();
    }
    
    // SSL CA file field visibility
    function toggleSslCaFileField() {
        var mode = $('#rex-form-ssl-ca-mode').val();
        $('[data-ssl-field="file"]').closest('.rex-form-group').toggle(mode === 'file');
    }
    
    $('#rex-form-ssl-ca-mode').on('change', toggleSslCaFileField);
    toggleSslCaFileField(); // Initial call
});
</script>
