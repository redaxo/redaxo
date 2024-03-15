<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Str;

assert(isset($context) && $context instanceof rex_context);
assert(isset($errors) && is_array($errors));
assert(isset($cancelSetupBtn));

$userSql = Sql::factory();
$userSql->setQuery('select * from ' . Core::getTablePrefix() . 'user LIMIT 1');

$headline = rex_view::title(I18n::msg('setup_500') . $cancelSetupBtn);

$submitMessage = I18n::msg('setup_510');
if (count($errors) > 0) {
    $submitMessage = I18n::msg('setup_511');
    $headline .= implode('', $errors);
}

$content = '';

$content .= '
        <fieldset>
            ';

$redaxoUserLogin = rex_post('redaxo_user_login', 'string');
$redaxoUserPass = rex_post('redaxo_user_pass', 'string');

if ($userSql->getRows() > 0) {
    $formElements = [];
    $n = [];

    $checked = '';
    if (!isset($_REQUEST['redaxo_user_login'])) {
        $checked = 'checked="checked"';
    }

    $n['label'] = '<label>' . I18n::msg('setup_509') . '</label>';
    $n['field'] = '<input class="rex-js-noadmin" type="checkbox" name="noadmin" value="1" ' . $checked . ' />';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/checkbox.php');
}

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-redaxo-user-login" class="required">' . I18n::msg('setup_507') . '</label>';
$n['field'] = '<input class="form-control" type="text" value="' . rex_escape($redaxoUserLogin) . '" id="rex-form-redaxo-user-login" name="redaxo_user_login" maxlength="255" inputmode="email" autocorrect="off" autocapitalize="off" autofocus />';
$formElements[] = $n;

$passwordPolicy = rex_backend_password_policy::factory();
$n = [];
$n['label'] = '<label for="rex-form-redaxo-user-pass" class="required">' . I18n::msg('setup_508') . '</label>';
$n['field'] = '<input class="form-control" type="password" value="' . rex_escape($redaxoUserPass) . '" id="rex-form-redaxo-user-pass" name="redaxo_user_pass" autocomplete="new-password" autocorrect="off" autocapitalize="off" ' . Str::buildAttributes($passwordPolicy->getHtmlAttributes()) . ' />';
$n['note'] = $passwordPolicy->getDescription();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= '<div class="rex-js-login-data">' . $fragment->parse('core/form/form.php') . '</div>';

$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-setup" type="submit" value="' . $submitMessage . '">' . $submitMessage . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$content .= '

    <script type="text/javascript" nonce="' . rex_response::getNonce() . '">
         <!--
        jQuery(function($) {
            $(".rex-js-createadminform .rex-js-noadmin").on("change",function (){

                if($(this).is(":checked")) {
                    $(".rex-js-login-data").each(function() {
                        $(this).css("display","none");
                    })
                } else {
                    $(".rex-js-login-data").each(function() {
                        $(this).css("display","block");
                    })
                }

            }).trigger("change");

        });
     //-->
    </script>';

echo $headline;

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('setup_506'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '<form class="rex-js-createadminform" action="' . $context->getUrl(['step' => 6]) . '" method="post" autocomplete="off">' . $content . '</form>';
