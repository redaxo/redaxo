<?php

/**
 * RSS Reader Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_rss_reader_component extends rex_dashboard_component
{
  function rex_rss_reader_component()
  {
    // default cache lifetime in seconds
    $cache_options['lifetime'] = 3600;
    
    parent::rex_dashboard_component('rss-reader', $cache_options);
    $this->setConfig(new rex_rss_reader_component_config());
    $this->setFormat('full');
  }
  
  /*protected*/ function prepare()
  {
    global $I18N;
    
    $content = '';
    foreach($this->config->getFeedUrls() as $feedUrl)
    {
      if($feedUrl != '')
      {
        $content .= rex_a656_rss_teaser($feedUrl);
      }
    }
    
    if($content == '')
    {
      $content .= $I18N->msg('rss_reader_component_noconfig');
      $content .= ' ';
      $content .= '<a href="#" onclick="componentToggleSettings(\''. $this->getId() .'\'); return false;">';
      $content .= $I18N->msg('rss_reader_component_opensettings');
      $content .= '</a>';
    }
    
    $this->setTitle($I18N->msg('rss_reader_component_title'));
    $this->setContent($content);
  }
}

class rex_rss_reader_component_config extends rex_dashboard_component_config
{
  function rex_rss_reader_component_config()
  {
    $defaultSettings = array(
      'urls' => array('http://www.redaxo.de/de/rss/news'),
    );
    parent::rex_dashboard_component_config($defaultSettings);
  }
  
  function getFeedUrls()
  {
    return $this->settings['urls'];
  }
  
  /*protected*/ function getFormValues()
  {
    $settings = array(
      'urls' => explode("\n", rex_post($this->getInputName('feedUrls'), 'string')),
    );
    
    return $settings;
  }
  
  /*protected*/ function getForm()
  {
    $name = $this->getInputName('feedUrls');
    return '<textarea cols="80" rows="4" name="'. $name .'">'. implode("\n", $this->getFeedUrls()) .'</textarea>';
  }
  
}