<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_cronjob_component extends rex_dashboard_component
{
  public function __construct()
  {
    parent::__construct('cronjob');
    $this->setTitle(rex_i18n::msg('cronjob_dashboard_component_title'));
    $this->setTitleUrl('index.php?page=cronjob');
    $this->setFormat('full');
    $this->setBlock(rex_i18n::msg('cronjob_dashboard_block'));
  }

  public function checkPermission()
  {
    return rex_core::getUser()->isAdmin();
  }

  protected function prepare()
  {
    $this->setContent(rex_cronjob_log :: getListOfNewestMessages(10));
  }
}