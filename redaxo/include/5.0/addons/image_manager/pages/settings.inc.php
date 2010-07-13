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
$max_resizekb = rex_request('max_resizekb', 'int');
$max_resizepixel = rex_request('max_resizepixel', 'int');
$jpg_quality = rex_request('jpg_quality', 'int');
$old_syntax = rex_request('old_syntax', 'int');

$config_file = $REX['INCLUDE_PATH'].'/addons/image_manager/config.inc.php';

if ($func == 'update')
{
  if($jpg_quality > 100) $jpg_quality = 100;
  else if ($jpg_quality < 0) $jpg_quality = 0;

	$REX['ADDON']['image_manager']['max_resizekb'] = $max_resizekb;
	$REX['ADDON']['image_manager']['max_resizepixel'] = $max_resizepixel;
	$REX['ADDON']['image_manager']['jpg_quality'] = $jpg_quality;

	$content = '
$REX[\'ADDON\'][\'image_manager\'][\'max_resizekb\'] = '.$max_resizekb.';
$REX[\'ADDON\'][\'image_manager\'][\'max_resizepixel\'] = '.$max_resizepixel.';
$REX[\'ADDON\'][\'image_manager\'][\'jpg_quality\'] = '.$jpg_quality.';
';

  if(rex_replace_dynamic_contents($config_file, $content) !== false)
    echo rex_info($I18N->msg('imanager_config_saved'));
  else
    echo rex_warning($I18N->msg('imanager_config_not_saved'));
}

if(!is_writable($config_file))
  echo rex_warning($I18N->msg('imanager_config_not_writable', $config_file));

echo '

<div class="rex-addon-output">



  <div class="rex-form">
	
<h2 class="rex-hl2">'. $I18N->msg('imanager_subpage_config') .'</h2>
  
<form action="index.php" method="post">

		<fieldset class="rex-form-col-1">
      <div class="rex-form-wrapper">
			<input type="hidden" name="page" value="image_manager" />
			<input type="hidden" name="subpage" value="settings" />
			<input type="hidden" name="func" value="update" />
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="max_resizekb">'. $I18N->msg('imanager_max_resizekb') .'</label>
					<input class="rex-form-text" type="text" id="max_resizekb" name="max_resizekb" value="'. htmlspecialchars($REX['ADDON']['image_manager']['max_resizekb']).'" />
				</p>
			</div>
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="max_resizepixel">'. $I18N->msg('imanager_max_resizepx') .'</label>
					<input class="rex-form-text" type="text" id="max_resizepixel" name="max_resizepixel" value="'. htmlspecialchars($REX['ADDON']['image_manager']['max_resizepixel']).'" />
				</p>
			</div>
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="jpg_quality">'. $I18N->msg('imanager_jpg_quality') .' [0-100]</label>
					<input class="rex-form-text" type="text" id="jpg_quality" name="jpg_quality" value="'. htmlspecialchars($REX['ADDON']['image_manager']['jpg_quality']).'" />
				</p>
			</div>
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-submit">
					<input type="submit" class="rex-form-submit" name="sendit" value="'.$I18N->msg('update').'" />
				</p>
			</div>
		</div>
			</fieldset>
  </form>
  </div>


</div>
  ';