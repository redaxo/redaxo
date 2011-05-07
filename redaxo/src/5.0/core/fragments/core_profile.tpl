  <div class="rex-form" id="rex-form-profile">
    <form action="index.php" method="post">
      <fieldset class="rex-form-col-2">
        <legend><?php echo rex_i18n::msg('profile_myprofile'); ?></legend>

        <div class="rex-form-wrapper">
          <input type="hidden" name="page" value="profile" />

					<div class="rex-form-row">
						<p class="rex-form-col-a rex-form-read">
              <label for="userlogin"><?php echo htmlspecialchars(rex_i18n::msg('login_name')); ?></label>
              <span class="rex-form-read" id="userlogin"><?php echo $this->user_login; ?></span>
						</p>

	          <p class="rex-form-col-b rex-form-select">
	            <label for="userperm-mylang"><?php echo rex_i18n::msg('backend_language'); ?></label>
	            <?php echo $this->backend_language; ?>
	          </p>
					</div>

					<div class="rex-form-row">
						<p class="rex-form-col-a rex-form-text">
              <label for="username"><?php echo rex_i18n::msg('name'); ?></label>
              <input class="rex-form-text" type="text" id="username" name="username" value="<?php echo $this->user_name; ?>" />
            </p>
						<p class="rex-form-col-b rex-form-text">
              <label for="userdesc"><?php echo rex_i18n::msg('description'); ?></label>
              <input class="rex-form-text" type="text" id="userdesc" name="userdesc" value="<?php echo $this->user_desc; ?>" />
            </p>
      		</div>

      	</div>
      </fieldset>

      <fieldset class="rex-form-col-1">
        <div class="rex-form-wrapper">
          <div class="rex-form-row">
						<p class="rex-form-col-a rex-form-submit">
            	<input class="rex-form-submit" type="submit" name="upd_profile_button" value="<?php echo rex_i18n::msg('profile_save'); ?>" <?php echo rex_accesskey(rex_i18n::msg('profile_save'), $REX['ACKEY']['SAVE']); ?> />
            </p>
          </div>
        </div>
      </fieldset>
    </form>
    </div>
  
  <p>&nbsp;</p>
  
    <div class="rex-form" id="rex-form-profile-psw">
    <form action="index.php" method="post" id="pwformular">
      <input type="hidden" name="javascript" value="0" id="javascript" />
      <fieldset class="rex-form-col-2">
        <legend><?php echo rex_i18n::msg('profile_changepsw'); ?></legend>

        <div class="rex-form-wrapper">
          <input type="hidden" name="page" value="profile" />

					<div class="rex-form-row">
			    	<p class="rex-form-col-a rex-form-text">
              			<label for="userpsw"><?php echo rex_i18n::msg('old_password'); ?></label>
							<input class="rex-form-text" type="password" id="userpsw" name="userpsw" autocomplete="off" />
						</p>
					</div>


					<div class="rex-form-row">
			    	<p class="rex-form-col-a rex-form-text">
             				 <label for="userpsw"><?php echo rex_i18n::msg('new_password'); ?></label>
							<input class="rex-form-text" type="password" id="userpsw_new_1" name="userpsw_new_1" autocomplete="off" />
						</p>
			    	<p class="rex-form-col-b rex-form-text">
              				<label for="userpsw"><?php echo rex_i18n::msg('new_password_repeat'); ?></label>
							<input class="rex-form-text" type="password" id="userpsw_new_2" name="userpsw_new_2" autocomplete="off" />
						</p>
					</div>

      	</div>
      </fieldset>

      <fieldset class="rex-form-col-1">
        <div class="rex-form-wrapper">
          <div class="rex-form-row">
						<p class="rex-form-col-a rex-form-submit">
            	<input class="rex-form-submit" type="submit" name="upd_psw_button" value="<?php echo rex_i18n::msg('profile_save_psw'); ?>" <?php echo rex_accesskey(rex_i18n::msg('profile_save_psw'), $REX['ACKEY']['SAVE']); ?> />
            </p>
          </div>
        </div>
      </fieldset>
    </form>
    </div>

    <script type="text/javascript">
       <!--
      jQuery(function($) {
        $("#username").focus();

        $("#pwformular")
          .submit(function(){
          	var pwInp0 = $("#userpsw");
          	if(pwInp0.val() != "")
          	{
            	pwInp0.val(Sha1.hash(pwInp0.val()));
          	}

          	var pwInp1 = $("#userpsw_new_1");
          	if(pwInp1.val() != "")
          	{
            	pwInp1.val(Sha1.hash(pwInp1.val()));
          	}

          	var pwInp2 = $("#userpsw_new_2");
          	if(pwInp2.val() != "")
          	{
          		pwInp2.val(Sha1.hash(pwInp2.val()));
          	}
        });

        $("#javascript").val("1");
      });
       //-->
    </script>