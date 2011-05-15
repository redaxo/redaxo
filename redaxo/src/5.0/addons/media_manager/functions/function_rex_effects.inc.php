<?php

function rex_media_manager_supportedEffects()
{
  $dirs = array(
    dirname(__FILE__). '/../lib/effects/'
  );

  $effects = array();
  foreach($dirs as $dir)
  {
    $files = glob($dir . 'class.rex_effect_*.inc.php');
    if($files)
    {
      foreach($files as $file)
      {
        $effects[rex_media_manager_effectClass($file)] = $file;
      }
    }
  }
  return $effects;
}

function rex_media_manager_supportedEffectNames()
{
  $effectNames = array();
  foreach(rex_media_manager_supportedEffects() as $effectClass => $effectFile)
  {
    $effectNames[] = rex_media_manager_effectName($effectFile);
  }
  return $effectNames;
}

function rex_media_manager_effectName($effectFile)
{
  return str_replace(
      array('class.rex_effect_', '.inc.php'),
      '',
      basename($effectFile)
    );
}

function rex_media_manager_effectClass($effectFile)
{
  return str_replace(
      array('class.', '.inc.php'),
      '',
      basename($effectFile)
    );
}

function rex_media_manager_deleteCacheByType($type_id)
{
  $qry = 'SELECT * FROM '. rex_core::getTablePrefix().'media_manager_types' . ' WHERE id='. $type_id;
  $sql = rex_sql::factory();
//  $sql->debugsql = true;
  $sql->setQuery($qry);

  $counter = 0;
  foreach($sql as $row)
  {
    $counter += rex_media_manager_cacher::deleteCache(null, $row->getValue('name'));
  }

  return $counter;
}