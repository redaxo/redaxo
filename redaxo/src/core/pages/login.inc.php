<?php

/**
 *
 * @package redaxo5
 */

echo rex_view::title('Login');


$js = '';
if ($rex_user_loginmessage != '')
{
  echo rex_view::warning($rex_user_loginmessage)."\n";
  $js = '
    var time_el = $("div.rex-message p strong");
    if(time_el.length == 1) {
      function disableLogin() {
        time_el.html((parseInt(time_el.html(), 10)-1) + "");
        if(parseInt(time_el.html(), 10) > 0) {
          setTimeout(disableLogin, 1000);
        } else {
          $("div.rex-message p").html("'. htmlspecialchars(rex_i18n::msg('login_welcome')) .'");
          $("#loginformular input:not(:hidden)").prop("disabled", "");
          $("#rex-form-login-user").focus();
        }
      };
      $("#loginformular input:not(:hidden)").prop("disabled", "disabled");
      setTimeout(disableLogin, 1000);
    }';
}

$content = '';
$content .= '

<div id="rex-form-login" class="rex-form">
<form action="index.php" method="post" id="loginformular">
  <fieldset>
    <h2>'.rex_i18n::msg('login_welcome').'</h2>
    <input type="hidden" name="javascript" value="0" id="javascript" />';

          $formElements = array();

            $n = array();
            $n['label'] = '<label for="rex-form-login-user">'.rex_i18n::msg('login_name').':</label>';
            $n['field'] = '<input type="text" value="'.htmlspecialchars($rex_user_login).'" id="rex-form-login-user" name="rex_user_login" />';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="REX_UPSW">'.rex_i18n::msg('password').':</label>';
            $n['field'] = '<input type="password" name="rex_user_psw" id="REX_UPSW" />';
            $formElements[] = $n;

            $n = array();
            $n['reverse'] = true;
            $n['label'] = '<label for="rex_user_stay_logged_in">'.rex_i18n::msg('stay_logged_in').'</label>';
            $n['field'] = '<input class="rex-form-checkbox" type="checkbox" name="rex_user_stay_logged_in" id="1" />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          $content .= $fragment->parse('form.tpl');

$content .= '<fieldset><fieldset class="rex-form-action">';

          $formElements = array();

            $n = array();
            $n['field'] = '<input class="rex-form-submit" type="submit" value="'.rex_i18n::msg('login').'" />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          $content .= $fragment->parse('form.tpl');
$content .= '
  </fieldset>
</form>
</div>
<script type="text/javascript">
   <!--
  jQuery(function($) {
    $("#rex-form-login-user").focus();

    $("#loginformular")
      .submit(function(){
        var pwInp = $("#REX_UPSW");
        if(pwInp.val() != "") {
          $("#loginformular").append(\'<input type="hidden" name="\'+pwInp.attr("name")+\'" value="\'+Sha1.hash(pwInp.val())+\'" />\');
        }
    });

    $("#javascript").val("1");
    '. $js .'
  });
   //-->
</script>

';

echo rex_view::contentBlock($content,'','block');
