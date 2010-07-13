<?php

/**
 * Cronjob Addon - Plugin article_status
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
  
  $REX['ADDON']['rxid']['article_status'] = '630';
  
  // Credits
  $REX['ADDON']['version']['article_status'] = '1.0';
  $REX['ADDON']['author']['article_status'] = 'Gregor Harlan';
  $REX['ADDON']['supportpage']['article_status'] = 'forum.redaxo.de';
  
}

// Config
$REX['ADDON']['config']['article_status']['from'] = array(
  'field' => 'art_online_from',
  'before' => 0,
  'after' => 1
);
$REX['ADDON']['config']['article_status']['to'] = array(
  'field' => 'art_online_to',
  'before' => 1,
  'after' => 0
);

rex_register_extension(
  'CRONJOB_TYPES',
  array('rex_cronjob_manager', 'registerExtension'),
  array('class' => 'rex_cronjob_article_status')
);

require_once dirname(__FILE__).'/classes/class.cronjob.inc.php';