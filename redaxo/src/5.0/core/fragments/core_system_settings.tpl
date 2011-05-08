  <div class="rex-form" id="rex-form-system-setup">
  	<form action="index.php" method="post">
    	<input type="hidden" name="page" value="specials" />
    	<input type="hidden" name="func" value="updateinfos" />

			<div class="rex-area-col-2">
				<div class="rex-area-col-a">

					<h3 class="rex-hl2"><?php echo rex_i18n::msg("specials_features"); ?></h3>

					<div class="rex-area-content">
						<h4 class="rex-hl3"><?php echo rex_i18n::msg("delete_cache"); ?></h4>
						<p class="rex-tx1"><?php echo rex_i18n::msg("delete_cache_description"); ?></p>
						<p class="rex-button"><a class="rex-button" href="index.php?page=specials&amp;func=generate"><span><span><?php echo rex_i18n::msg("delete_cache"); ?></span></span></a></p>

						<h4 class="rex-hl3"><?php echo rex_i18n::msg("setup"); ?></h4>
						<p class="rex-tx1"><?php echo rex_i18n::msg("setup_text"); ?></p>
						<p class="rex-button"><a class="rex-button" href="index.php?page=specials&amp;func=setup" onclick="return confirm('<?php echo rex_i18n::msg("setup"); ?>?');"><span><span><?php echo rex_i18n::msg("setup"); ?></span></span></a></p>

            <h4 class="rex-hl3"><?php echo rex_i18n::msg("version"); ?></h4>
            <p class="rex-tx1">
            REDAXO: <?php echo $REX['VERSION'].'.'.$REX['SUBVERSION'].'.'.$REX['MINORVERSION']; ?><br />
            PHP: <?php echo phpversion(); ?></p>

            <h4 class="rex-hl3"><?php echo rex_i18n::msg("database"); ?></h4>
            <p class="rex-tx1">MySQL: <?php echo rex_sql::getServerVersion(); ?><br /><?php echo rex_i18n::msg("name"); ?>: <?php echo $REX['DB'][1]['name']; ?><br /><?php echo rex_i18n::msg("host"); ?>: <?php echo $REX['DB'][1]['host']; ?></p>

					</div>
				</div>

				<div class="rex-area-col-b">

					<h3 class="rex-hl2"><?php echo rex_i18n::msg("specials_settings"); ?></h3>

					<div class="rex-area-content">

						<fieldset class="rex-form-col-1">
							<legend><?php echo rex_i18n::msg("general_info_header"); ?></legend>

							<div class="rex-form-wrapper">

            <!--
							<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex-form-version">Version</label>
										<span class="rex-form-read" id="rex-form-version"><?php echo $REX['VERSION'].'.'.$REX['SUBVERSION'].'.'.$REX['MINORVERSION']; ?></span>
									</p>
								</div>
						-->

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-server">$REX[\'SERVER\']</label>
										<input class="rex-form-text" type="text" id="rex-form-server" name="neu_SERVER" value="<?php echo htmlspecialchars($REX['SERVER']); ?>" />
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-servername">$REX[\'SERVERNAME\']</label>
										<input class="rex-form-text" type="text" id="rex-form-servername" name="neu_SERVERNAME" value="<?php echo htmlspecialchars($REX['SERVERNAME']); ?>" />
									</p>
								</div>
							</div>
            <!--
						</fieldset>
						-->

						<!--
						<fieldset class="rex-form-col-1">
							<legend><?php echo rex_i18n::msg("db1_can_only_be_changed_by_setup"); ?></legend>

							<div class="rex-form-wrapper">

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex-form-db-host">$REX[\'DB\'][\'1\'][\'HOST\']</label>
										<span class="rex-form-read" id="rex-form-db-host">&quot;<?php echo $REX['DB'][1]['host']; ?>&quot;</span>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-db-login">$REX[\'DB\'][\'1\'][\'LOGIN\']</label>
										<span id="rex-form-db-login">&quot;<?php echo $REX['DB'][1]['login']; ?>&quot;</span>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex-form-db-psw">$REX[\'DB\'][\'1\'][\'PSW\']</label>
										<span class="rex-form-read" id="rex-form-db-psw">&quot;****&quot;</span>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex-form-db-name">$REX[\'DB\'][\'1\'][\'NAME\']</label>
										<span class="rex-form-read" id="rex-form-db-name">&quot;<?php echo htmlspecialchars($REX['DB'][1]['name']); ?>&quot;</span>
									</p>
								</div>
							</div>
						</fieldset>
						-->

						<!--
						<fieldset class="rex-form-col-1">
							<legend>'.rex_i18n::msg("specials_others").'</legend>

							<div class="rex-form-wrapper">
						-->

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-read">
										<label for="rex_src_path">rex_path::src()</label>
										<span class="rex-form-read" id="rex_src_path" title="'. rex_path::src() .'">&quot;
                  <?php
										$tmp = rex_path::src();
										if (strlen($tmp)>21)
											$tmp = substr($tmp,0,8)."..".substr($tmp,strlen($tmp)-13);

										echo $tmp;
								  ?>

					         &quot;</span>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-error-email">$REX[\'ERROR_EMAIL\']</label>
										<input class="rex-form-text" type="text" id="rex-form-error-email" name="neu_error_emailaddress" value="<?php echo htmlspecialchars($REX['ERROR_EMAIL']); ?>" />
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-widget">
										<label for="rex-form-startarticle-id">$REX[\'START_ARTICLE_ID\']</label>
										<?php echo rex_var_link::_getLinkButton('neu_startartikel', 1, $REX['START_ARTICLE_ID']); ?>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-widget">
										<label for="rex-form-notfound-article-id">$REX[\'NOTFOUND_ARTICLE_ID\']</label>
                    <?php echo rex_var_link::_getLinkButton('neu_notfoundartikel', 2, $REX['NOTFOUND_ARTICLE_ID']); ?>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-select">
										<label for="rex-form-default-template-id">$REX[\'DEFAULT_TEMPLATE_ID\']</label>
										<?php echo $this->template; ?>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-select">
										<label for="rex-form-lang">$REX[\'LANG\']</label>
										<?php echo $this->language; ?>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-select">
										<label for="rex-form-mod-rewrite">$REX[\'MOD_REWRITE\']</label>
										<?php echo $this->mod_rewrite; ?>
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-submit">
										<input type="submit" class="rex-form-submit" name="sendit" value="<?php echo rex_i18n::msg("specials_update"); ?>" <?php echo rex_accesskey(rex_i18n::msg('specials_update'), $REX['ACKEY']['SAVE']); ?> />
									</p>
								</div>

            <!--
								</div>
						-->
						</fieldset>
					</div> <!-- Ende rex-area-content //-->

				</div> <!-- Ende rex-area-col-b //-->
			</div> <!-- Ende rex-area-col-2 //-->

		</form>
	</div>