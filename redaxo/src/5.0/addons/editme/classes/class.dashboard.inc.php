<?php

/**
 * Editme Dashboard-Komponenten
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_editme_component extends rex_dashboard_component
{
  function rex_editme_component($tableName)
  {
    global $REX;
    
    parent::rex_dashboard_component('editme-'. $tableName);
    
    $this->setTitle($tableName);
    $this->setTitleUrl('index.php?page=editme&amp;subpage='. $tableName);
    $this->setBlock($I18N->msg('editme'));
  }
  
  protected function prepare()
  {
    global $REX;
    
    $this->setContent('uio');
  }
}