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
  $REX['ADDON']['rxid']['optimize_tables'] = '630';
  
  // Credits
  $REX['ADDON']['version']['optimize_tables'] = '1.0';
  $REX['ADDON']['author']['optimize_tables'] = 'Gregor Harlan';
  $REX['ADDON']['supportpage']['optimize_tables'] = 'forum.redaxo.de';
  
}

rex_cronjob_manager::registerType('rex_cronjob_optimize_tables');