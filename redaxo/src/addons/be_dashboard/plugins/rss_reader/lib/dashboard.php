<?php

/**
 * RSS Reader Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_rss_reader_component extends rex_dashboard_component
{
  public function __construct()
  {
    // default cache lifetime in seconds
    $cache_options['lifetime'] = 3600;

    parent::__construct('rss-reader', $cache_options);
    $this->setConfig(new rex_rss_reader_component_config());
    $this->setFormat('full');
  }

  protected function prepare()
  {
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
      $content .= rex_i18n::msg('rss_reader_component_noconfig');
      $content .= ' ';
      $content .= '<a href="#" onclick="componentToggleSettings(\''. $this->getId() .'\'); return false;">';
      $content .= rex_i18n::msg('rss_reader_component_opensettings');
      $content .= '</a>';
    }

    $this->setTitle(rex_i18n::msg('rss_reader_component_title'));
    $this->setContent($content);
  }
}

class rex_rss_reader_component_config extends rex_dashboard_component_config
{
  public function __construct()
  {
    $defaultSettings = array(
      'urls' => array('http://www.redaxo.org/de/rss/news'),
    );
    parent::__construct($defaultSettings);
  }

  function getFeedUrls()
  {
    return $this->settings['urls'];
  }

  protected function getFormValues()
  {
    $settings = array(
      'urls' => explode("\n", rex_post($this->getInputName('feedUrls'), 'string')),
    );

    return $settings;
  }

  protected function getForm()
  {
    $name = $this->getInputName('feedUrls');
    return '<textarea cols="80" rows="4" name="'. $name .'">'. implode("\n", $this->getFeedUrls()) .'</textarea>';
  }

}