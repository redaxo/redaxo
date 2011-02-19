<?php

/**
 * Layout 
 *
 * @author jan[dot]kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 */

$addon = 'layout';

/* Addon Parameter */
if($REX['REDAXO'])
{
  require_once rex_path::addon($addon, 'extensions/extensions.inc.php');
  rex_register_extension('ADDONS_INCLUDED', 'rex_layout_addPage');
  
  require_once rex_path::addon($addon, 'functions/include.inc.php');
}