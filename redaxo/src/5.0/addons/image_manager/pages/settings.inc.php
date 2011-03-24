<?php

/**
 * image_manager Addon
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]yakmara[dot]de Jan Kristinus
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// rex_request();

$func = rex_request('func', 'string');
$jpg_quality = rex_request('jpg_quality', 'int');

if ($func == 'update')
{
  if($jpg_quality > 100) $jpg_quality = 100;
  else if ($jpg_quality < 0) $jpg_quality = 0;

  rex_config::set('image_manager', 'jpg_quality', $jpg_quality);

  echo rex_info(rex_i18n::msg('imanager_config_saved'));
}

echo '

<div class="rex-addon-output">



  <div class="rex-form">

<h2 class="rex-hl2">'. rex_i18n::msg('imanager_subpage_config') .'</h2>

<form action="index.php" method="post">

		<fieldset class="rex-form-col-1">
      <div class="rex-form-wrapper">
			<input type="hidden" name="page" value="image_manager" />
			<input type="hidden" name="subpage" value="settings" />
			<input type="hidden" name="func" value="update" />

			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="jpg_quality">'. rex_i18n::msg('imanager_jpg_quality') .' [0-100]</label>
					<input class="rex-form-text" type="text" id="jpg_quality" name="jpg_quality" value="'. htmlspecialchars(rex_config::get('image_manager', 'jpg_quality')).'" />
				</p>
			</div>

			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-submit">
					<input type="submit" class="rex-form-submit" name="sendit" value="'.rex_i18n::msg('update').'" />
				</p>
			</div>
		</div>
			</fieldset>
  </form>
  </div>


</div>
  ';