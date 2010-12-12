<?php

/**
 * Cronjob Addon - Plugin article_status
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

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

rex_cronjob_manager::registerType('rex_cronjob_article_status');