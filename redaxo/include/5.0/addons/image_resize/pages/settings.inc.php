<?php

/**
 * Image-Resize Addon
 *
 * @author office[at]vscope[dot]at Wolfgang Hutteger
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]yakmara[dot]de Jan Kristinus
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// rex_request();

$func = rex_request('func', 'string');
$max_cachefiles = rex_request('max_cachefiles', 'int');
$max_filters = rex_request('max_filters', 'int');
$max_resizekb = rex_request('max_resizekb', 'int');
$max_resizepixel = rex_request('max_resizepixel', 'int');
$jpg_quality = rex_request('jpg_quality', 'int');

$config_file = $REX['INCLUDE_PATH'].'/addons/image_resize/config.inc.php';

if ($func == 'update')
{
  if($jpg_quality > 100) $jpg_quality = 100;
  else if ($jpg_quality < 0) $jpg_quality = 0;

	$REX['ADDON']['image_resize']['max_cachefiles'] = $max_cachefiles;
	$REX['ADDON']['image_resize']['max_filters'] = $max_filters;
	$REX['ADDON']['image_resize']['max_resizekb'] = $max_resizekb;
	$REX['ADDON']['image_resize']['max_resizepixel'] = $max_resizepixel;
	$REX['ADDON']['image_resize']['jpg_quality'] = $jpg_quality;

	$content = '$REX[\'ADDON\'][\'image_resize\'][\'max_cachefiles\'] = '.$max_cachefiles.';
$REX[\'ADDON\'][\'image_resize\'][\'max_filters\'] = '.$max_filters.';
$REX[\'ADDON\'][\'image_resize\'][\'max_resizekb\'] = '.$max_resizekb.';
$REX[\'ADDON\'][\'image_resize\'][\'max_resizepixel\'] = '.$max_resizepixel.';
$REX[\'ADDON\'][\'image_resize\'][\'jpg_quality\'] = '.$jpg_quality.';
';

  if(rex_replace_dynamic_contents($config_file, $content) !== false)
    echo rex_info($I18N->msg('iresize_config_saved'));
  else
    echo rex_warning($I18N->msg('iresize_config_not_saved'));
}

if(!is_writable($config_file))
  echo rex_warning($I18N->msg('iresize_config_not_writable', $config_file));

echo '

<div class="rex-addon-output">

<h2 class="rex-hl2">'. $I18N->msg('iresize_subpage_config') .'</h2>

<div class="rex-area">
  <div class="rex-form">
	
  <form action="index.php" method="post">

		<fieldset class="rex-form-col-1">
      <div class="rex-form-wrapper">
			<input type="hidden" name="page" value="image_resize" />
			<input type="hidden" name="subpage" value="settings" />
			<input type="hidden" name="func" value="update" />
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="max_cachefiles">'. $I18N->msg('iresize_max_cache_files') .'</label>
					<input class="rex-form-text" type="text" id="max_cachefiles" name="max_cachefiles" value="'. htmlspecialchars($REX['ADDON']['image_resize']['max_cachefiles']).'" />
				</p>
			</div>
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="max_filters">'. $I18N->msg('iresize_max_filters') .'</label>
					<input class="rex-form-text" type="text" id="max_filters" name="max_filters" value="'. htmlspecialchars($REX['ADDON']['image_resize']['max_filters']).'" />
				</p>
			</div>
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="max_resizekb">'. $I18N->msg('iresize_max_resizekb') .'</label>
					<input class="rex-form-text" type="text" id="max_resizekb" name="max_resizekb" value="'. htmlspecialchars($REX['ADDON']['image_resize']['max_resizekb']).'" />
				</p>
			</div>
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="max_resizepixel">'. $I18N->msg('iresize_max_resizepx') .'</label>
					<input class="rex-form-text" type="text" id="max_resizepixel" name="max_resizepixel" value="'. htmlspecialchars($REX['ADDON']['image_resize']['max_resizepixel']).'" />
				</p>
			</div>
			
			<div class="rex-form-row rex-form-element-v2">
				<p class="rex-form-text">
					<label for="jpg_quality">'. $I18N->msg('iresize_jpg_quality') .' [0-100]</label>
					<input class="rex-form-text" type="text" id="jpg_quality" name="jpg_quality" value="'. htmlspecialchars($REX['ADDON']['image_resize']['jpg_quality']).'" />
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

</div>
  ';