<?php

/**
 *
 * @package redaxo5
 */


global $rex_user_loginmessage;

$rex_user_login = rex_post('rex_user_login', 'string');

echo rex_view::title(rex_i18n::msg('login'));

$js = '';
if ($rex_user_loginmessage != '') {
  echo rex_view::warning($rex_user_loginmessage) . "\n";
  $js = '
    var time_el = $("div.rex-message strong[data-time]");
    if(time_el.length == 1) {
      function disableLogin() {
        var time = time_el.attr("data-time");
        time_el.attr("data-time", time - 1);
        var hours = Math.floor(time / 3600);
        var mins  = Math.floor((time - (hours * 3600)) / 60);
        var secs  = time % 60;
        var formatted = (hours ? hours + "h " : "") + (hours || mins ? mins + "min " : "") + secs + "s";
        time_el.html(formatted);
        if(time > 0) {
          setTimeout(disableLogin, 1000);
        } else {
          $("div.rex-message div").html("' . rex_i18n::msg('login_welcome') . '");
          $("#rex-form-login input:not(:hidden)").prop("disabled", "");
          $("#rex-form-login-user").focus();
        }
      };
      $("#rex-form-login input:not(:hidden)").prop("disabled", "disabled");
      setTimeout(disableLogin, 1000);
    }';
}

$content = '';
$content .= '

<div id="rex-form-login" class="rex-form">
<form action="index.php" method="post">
  <fieldset>
    <h2>' . rex_i18n::msg('login_welcome') . '</h2>
    <input type="hidden" name="javascript" value="0" id="javascript" />';

$formElements = array();

$n = array();
$n['label'] = '<label for="rex-form-login-user">' . rex_i18n::msg('login_name') . ':</label>';
$n['field'] = '<input type="text" value="' . htmlspecialchars($rex_user_login) . '" id="rex-form-login-user" name="rex_user_login" />';
$formElements[] = $n;

$n = array();
$n['label'] = '<label for="REX_UPSW">' . rex_i18n::msg('password') . ':</label>';
$n['field'] = '<input type="password" name="rex_user_psw" id="REX_UPSW" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.tpl');

$formElements = array();
$n = array();
$n['reverse'] = true;
$n['label'] = '<label for="rex-user-stay-logged-in">' . rex_i18n::msg('stay_logged_in') . '</label>';
$n['field'] = '<input class="rex-form-checkbox" type="checkbox" name="rex_user_stay_logged_in" id="rex-user-stay-logged-in" value="1" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.tpl');

$content .= '<fieldset>';


$formElements = array();
$n = array();
$n['field'] = '<button class="rex-button" type="submit">' . rex_i18n::msg('login') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/submit.tpl');
$content .= '
</form>
</div>
<script type="text/javascript">
   <!--
  jQuery(function($) {
    $("#rex-form-login-user").focus();

    $("#rex-form-login form")
      .submit(function(){
        var pwInp = $("#REX_UPSW");
        if(pwInp.val() != "") {
          $("#rex-form-login form").append(\'<input type="hidden" name="\'+pwInp.attr("name")+\'" value="\'+Sha1.hash(pwInp.val())+\'" />\');
        }
    });

    $("#javascript").val("1");
    ' . $js . '
  });
   //-->
</script>

';


echo rex_view::contentBlock($content);
