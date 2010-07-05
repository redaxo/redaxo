<?php

/**
 * Cronjob Addon - Plugin optimize_tables
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

if($REX['REDAXO'])
{

  // Sprachdateien anhaengen
  $I18N->appendFile(dirname(__FILE__) .'/lang/');
  
  $REX['ADDON']['rxid']['optimize_tables'] = '630';
  
  // Credits
  $REX['ADDON']['version']['optimize_tables'] = '1.0';
  $REX['ADDON']['author']['optimize_tables'] = 'Gregor Harlan';
  $REX['ADDON']['supportpage']['optimize_tables'] = 'forum.redaxo.de';
  
}

rex_register_extension(
  'CRONJOB_TYPES',
  array('rex_cronjob_manager', 'registerExtension'),
  array('class' => 'rex_cronjob_optimize_tables')
);

require_once dirname(__FILE__).'/classes/class.cronjob.inc.php';