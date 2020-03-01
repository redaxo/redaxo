<?php

assert(isset($context) && $context instanceof rex_context);
assert(isset($errors) && is_array($errors));

$user_sql = rex_sql::factory();
$user_sql->setQuery('select * from ' . rex::getTablePrefix() . 'user LIMIT 1');

$headline = rex_view::title(rex_i18n::msg('setup_600'));

$submit_message = rex_i18n::msg('setup_610');
if (count($errors) > 0) {
    $submit_message = rex_i18n::msg('setup_611');
    $headline .= implode('', $errors);
}

$content = '';

$content .= '
        <fieldset>
            <input class="rex-js-javascript" type="hidden" name="javascript" value="0" />
            ';

$redaxo_user_login = rex_post('redaxo_user_login', 'string');
$redaxo_user_pass = rex_post('redaxo_user_pass', 'string');

if ($user_sql->getRows() > 0) {
    $formElements = [];
    $n = [];

    $checked = '';
    if (!isset($_REQUEST['redaxo_user_login'])) {
        $checked = 'checked="checked"';
    }

    $n['label'] = '<label>' . rex_i18n::msg('setup_609') . '</label>';
    $n['field'] = '<input class="rex-js-noadmin" type="checkbox" name="noadmin" value="1" ' . $checked . ' />';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/checkbox.php');
}

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-form-redaxo-user-login">' . rex_i18n::msg('setup_607') . '</label>';
$n['field'] = '<input class="form-control" type="text" value="' . rex_escape($redaxo_user_login) . '" id="rex-form-redaxo-user-login" name="redaxo_user_login" autofocus />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-form-redaxo-user-pass">' . rex_i18n::msg('setup_608') . '</label>';
$n['field'] = '<input class="form-control rex-js-redaxo-user-pass" type="password" value="' . rex_escape($redaxo_user_pass) . '" id="rex-form-redaxo-user-pass" name="redaxo_user_pass" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= '<div class="rex-js-login-data">' . $fragment->parse('core/form/form.php') . '</div>';

$content .= '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-setup" type="submit" value="' . $submit_message . '">' . $submit_message . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$content .= '

    <script type="text/javascript">
         <!--
        jQuery(function($) {
            $(".rex-js-createadminform")
                .submit(function(){
                    var pwInp = $(".rex-js-redaxo-user-pass");
                    if(pwInp.val() != "") {
                        $(".rex-js-createadminform").append(\'<input type="hidden" name="\'+pwInp.attr("name")+\'" value="\'+Sha1.hash(pwInp.val())+\'" />\');
                        pwInp.removeAttr("name");
                    }
            });

            $(".rex-js-javascript").val("1");

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
$fragment->setVar('title', rex_i18n::msg('setup_606'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '<form class="rex-js-createadminform" action="' . $context->getUrl(['step' => 7]) . '" method="post" autocomplete="off">' . $content . '</form>';
