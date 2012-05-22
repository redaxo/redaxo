<?php

/**
 * Backendstyle Addon
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

$mypage = 'be_style';

/* Addon Parameter */
if(rex::isBackend())
{
  require_once rex_path::addon($mypage, 'extensions/function_extensions.inc.php');
  rex_extension::register('ADDONS_INCLUDED', 'rex_be_add_page');
}
