<?php

/**
 * Cronjob Addon - Plugin article_status
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */
 
class rex_cronjob_article_status extends rex_cronjob
{
  /*public*/ function execute()
  {
    global $REX;

    $config = OOPlugin::getProperty('cronjob', 'article_status', 'config');
    $from = $config['from'];
    $to   = $config['to'];
    $from['before'] = (array) $from['before'];
    $to['before']   = (array) $to['before'];
    
    $sql = rex_sql::factory();
    // $sql->debugsql = true;
    $sql->setQuery('
      SELECT  name 
      FROM    '. $REX['TABLE_PREFIX'] .'62_params 
      WHERE   name="'. $from['field'] .'" OR name="'. $to['field'] .'"
    ');
    $rows = $sql->getRows();
    if ($rows < 2)
    {
      if ($rows == 0)
      {
        $msg = 'Metainfo fields "'. $from['field'] .'" and "'. $to['field'] .'" not found';
      }
      else
      {
        $field = $sql->getValue('name') == $from['field'] ? $to['field'] : $from['field'];
        $msg = 'Metainfo field "'. $field .'" not found';
      }
      $this->setMessage($msg);
      return false;
    }
    
    $time = time();
    $sql->setQuery('
      SELECT  id, clang, status 
      FROM    '. $REX['TABLE_PREFIX'] .'article 
      WHERE 
        (     '. $from['field'] .' > 0 
        AND   '. $from['field'] .' < '. $time .' 
        AND   status IN ('. implode(',', $from['before']) .')
        AND   ('. $to['field'] .' > '. $time .' OR '. $to['field'] .' = 0 OR '. $to['field'] .' = "")
        )
      OR 
        (     '. $to['field'] .' > 0 
        AND   '. $to['field'] .' < '. $time .' 
        AND   status IN ('. implode(',', $to['before']) .')
        )
    ');
    $rows = $sql->getRows();

    include_once $REX['INCLUDE_PATH'].'/functions/function_rex_structure.inc.php';

    for($i = 0; $i < $rows; $i++)
    {
      if (in_array($sql->getValue('status'), $from['before']))
        $status = $from['after'];
      else
        $status = $to['after'];
      
      rex_articleStatus($sql->getValue('id'), $sql->getValue('clang'), $status);
      $sql->next();
    }
    $this->setMessage('Updated articles: '. $rows);
    return true;
  }
  
  /*public*/ function getTypeName()
  {
    global $I18N;
    return $I18N->msg('cronjob_article_status');
  }
}