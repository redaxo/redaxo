<?php

/**
 * Userinfo Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */


function rex_a659_statistics()
{
  $stats = array();
  $stats['last_update'] = 0;

  $sql = rex_sql::factory();
//  $sql->debugsql = true;
  $result = $sql->getArray('SELECT COUNT(*) as count, updatedate FROM ' . rex::getTablePrefix() . 'article WHERE clang=0 AND startpage=1 GROUP BY clang ORDER BY updatedate DESC');
  if (count($result) > 0) {
    $stats['total_categories'] = $result[0]['count'];
    $stats['last_update'] = $result[0]['updatedate'] > $stats['last_update'] ? $result[0]['updatedate'] : $stats['last_update'];
  } else {
    $stats['total_categories'] = 0;
  }

  $result = $sql->getArray('SELECT COUNT(*) as count, updatedate FROM ' . rex::getTablePrefix() . 'article WHERE clang=0 AND startpage=0 GROUP BY clang ORDER BY updatedate DESC');
  if (count($result) > 0) {
    $stats['total_articles'] = $result[0]['count'];
    $stats['last_update'] = $result[0]['updatedate'] > $stats['last_update'] ? $result[0]['updatedate'] : $stats['last_update'];
  } else {
    $stats['total_articles'] = 0;
  }

  $result = $sql->getArray('SELECT COUNT(*) as count, updatedate FROM ' . rex::getTablePrefix() . 'article_slice GROUP BY revision ORDER BY updatedate DESC LIMIT 1');
  if (count($result) > 0) {
    $stats['total_slices'] = $result[0]['count'];
    $stats['last_update'] = $result[0]['updatedate'] > $stats['last_update'] ? $result[0]['updatedate'] : $stats['last_update'];
  } else {
    $stats['total_slices'] = 0;
  }

  $result = $sql->getArray('SELECT COUNT(*) as count FROM ' . rex::getTablePrefix() . 'clang');
  if (count($result) > 0) {
    $stats['total_clangs'] = $result[0]['count'];
  } else {
    $stats['total_clangs'] = 0;
  }

  $result = $sql->getArray('SELECT COUNT(*) as count, updatedate FROM ' . rex::getTablePrefix() . 'template GROUP BY revision ORDER BY updatedate DESC LIMIT 1');
  if (count($result) > 0) {
    $stats['total_templates'] = $result[0]['count'];
    $stats['last_update'] = $result[0]['updatedate'] > $stats['last_update'] ? $result[0]['updatedate'] : $stats['last_update'];
  } else {
    $stats['total_templates'] = 0;
  }

  $result = $sql->getArray('SELECT COUNT(*) as count, updatedate FROM ' . rex::getTablePrefix() . 'module GROUP BY revision ORDER BY updatedate DESC LIMIT 1');
  if (count($result) > 0) {
    $stats['total_modules'] = $result[0]['count'];
    $stats['last_update'] = $result[0]['updatedate'] > $stats['last_update'] ? $result[0]['updatedate'] : $stats['last_update'];
  } else {
    $stats['total_modules'] = 0;
  }

  $result = $sql->getArray('SELECT COUNT(*) as count, updatedate FROM ' . rex::getTablePrefix() . 'action GROUP BY revision ORDER BY updatedate DESC LIMIT 1');
  if (count($result) > 0) {
    $stats['total_actions'] = $result[0]['count'];
    $stats['last_update'] = $result[0]['updatedate'] > $stats['last_update'] ? $result[0]['updatedate'] : $stats['last_update'];
  } else {
    $stats['total_actions'] = 0;
  }

  $result = $sql->getArray('SELECT COUNT(*) as count, updatedate FROM ' . rex::getTablePrefix() . 'user GROUP BY revision ORDER BY updatedate DESC LIMIT 1');
  if (count($result) > 0) {
    $stats['total_users'] = $result[0]['count'];
    $stats['last_update'] = $result[0]['updatedate'] > $stats['last_update'] ? $result[0]['updatedate'] : $stats['last_update'];
  } else {
    $stats['total_users'] = 0;
  }

  return $stats;
}
