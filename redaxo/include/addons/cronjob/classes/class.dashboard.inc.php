<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_cronjob_component extends rex_dashboard_component
{
  function rex_cronjob_component()
  {
    global $I18N;
    
    parent::rex_dashboard_component('cronjob');
    $this->setTitle($I18N->msg('cronjob_dashboard_component_title'));
    $this->setTitleUrl('index.php?page=cronjob');
    $this->setFormat('full');
    $this->setBlock($I18N->msg('cronjob_dashboard_block'));
  }
  
  /*public*/ function checkPermission()
  {
    global $REX;
    
    return $REX['USER']->isAdmin();
  }
  
  /*protected*/ function prepare()
  {
    $this->setContent(rex_cronjob_log :: getListOfNewestMessages(10));
  }
}