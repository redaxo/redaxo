<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

echo rex_view::title('Login');


$js = '';
if ($rex_user_loginmessage != '')
{
  echo rex_view::warning($rex_user_loginmessage)."\n";
  $js = '
    var time_el = $("div.rex-message p span strong");
    if(time_el.length == 1) {
      function disableLogin() {
        time_el.html((parseInt(time_el.html(), 10)-1) + "");
        if(parseInt(time_el.html(), 10) > 0) {
          setTimeout(disableLogin, 1000);
        } else {
          $("div.rex-message p span").html("'. htmlspecialchars(rex_i18n::msg('login_welcome')) .'");
          $("#loginformular input:not(:hidden)").prop("disabled", "");
          $("#rex-form-login").focus();
        }
      };
      $("#loginformular input:not(:hidden)").prop("disabled", "disabled");
      setTimeout(disableLogin, 1000);
    }';
}

echo '

<!-- *** OUTPUT OF LOGIN-FORM - START *** -->
<div class="rex-form rex-form-login">
<form action="index.php" method="post" id="loginformular">
  <fieldset class="rex-form-col-1">
    <legend>Login</legend>
    <input type="hidden" name="javascript" value="0" id="javascript" />

    <div class="rex-form-wrapper">

    	<div class="rex-form-row">
		    <p class="rex-form-col-a rex-form-text">
    			<label for="rex-form-login">'.rex_i18n::msg('login_name').':</label>
      		<input type="text" value="'.htmlspecialchars($rex_user_login).'" id="rex-form-login" name="rex_user_login" />
    		</p>
    	</div>
    	<div class="rex-form-row">
		    <p class="rex-form-col-a rex-form-password">
      		<label for="REX_UPSW">'.rex_i18n::msg('password').':</label>
      		<input class="rex-form-password" type="password" name="rex_user_psw" id="REX_UPSW" />
	    		<input class="rex-form-submit" type="submit" value="'.rex_i18n::msg('login').'" />
	    	</p>
	    </div>
    	<div class="rex-form-row">
		    <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
      		<input class="rex-form-checkbox" type="checkbox" name="rex_user_stay_logged_in" id="1" />
      		<label for="rex_user_stay_logged_in">'.rex_i18n::msg('stay_logged_in').'</label>
	    	</p>
	    </div>
	  </div>
  </fieldset>
</form>
</div>
<script type="text/javascript">
   <!--
  jQuery(function($) {
    $("#rex-form-login").focus();

    $("#loginformular")
      .submit(function(){
      	var pwInp = $("#REX_UPSW");
      	if(pwInp.val() != "") {
      		pwInp.val(Sha1.hash(pwInp.val()));
      	}
    });

    $("#javascript").val("1");
    '. $js .'
  });
   //-->
</script>
<!-- *** OUTPUT OF LOGIN-FORM - END *** -->

';