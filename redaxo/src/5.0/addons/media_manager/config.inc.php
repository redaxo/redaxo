<?php

/**
 * media_manager Addon
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$mypage = 'media_manager';

$REX['PERM'][] = 'media_manager[]';

rex_extension::register('ADDONS_INCLUDED','rex_media_manager_init');

function rex_media_manager_init()
{
	global $REX;

	//--- handle image request
	$rex_media_manager_file = rex_get('rex_media_file', 'string');
	$rex_media_manager_type = rex_get('rex_media_type', 'string');

	if($rex_media_manager_file != '' && $rex_media_manager_type != '')
	{

		$media_path    = rex_path::media($rex_media_manager_file, rex_path::RELATIVE);
		$cache_path    = rex_path::cache('media/');

		$media         = new rex_media($media_path);
		// $media_manager_cacher  = new rex_media_manager_cacher($cache_path);

		$media_manager = new rex_media_manager($media); // $media_manager_cacher
		$media_manager->setCachePath($cache_path);
		$media_manager->applyEffects($rex_media_manager_type);
		$media_manager->sendMedia();

		exit();

	}
}

if($REX['REDAXO'])
{
	// delete thumbnails on mediapool changes
	if(!function_exists('rex_media_manager_ep_mediaupdated'))
	{
		rex_extension::register('MEDIA_UPDATED', 'rex_media_manager_ep_mediaupdated');
		function rex_media_manager_ep_mediaupdated($params){
			rex_image_cacher::deleteCache($params["filename"]);
		}
	}

}