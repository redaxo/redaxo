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

rex_extension::register('ADDONS_INCLUDED','rex_media_manager::init');

if(rex::isBackend())
{
  // delete thumbnails on mediapool changes
  rex_extension::register('MEDIA_UPDATED', 'rex_media_manager::mediaUpdated');

}