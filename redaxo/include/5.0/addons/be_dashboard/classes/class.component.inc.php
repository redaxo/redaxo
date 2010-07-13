<?php

/**
 * Backenddashboard Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

/*abstract*/ class rex_dashboard_component extends rex_dashboard_component_base
{
  var $title;
  var $titleUrl;
  var $content;
  
  var $format;
  var $block;
  
  function rex_dashboard_component($id, $cache_options = array())
  {
    if(!isset($cache_options['lifetime']))
    {
      // default cache lifetime in seconds
      $cache_options['lifetime'] = 60;
    }
    
    $this->title = '';
    $this->titleUrl = '';
    $this->content = '';
    
    $this->format = 'half';
    $this->block = '';
    
    parent::rex_dashboard_component_base($id, $cache_options);
  }
  
  /**
   * Setzt den Titel der Komponente
   */
  /*public*/ function setTitle($title)
  {
    $this->title = $title;
  }
  
  /*public*/ function getTitle()
  {
    return $this->title;
  }
  
  /**
   * Setzt die Url auf die der Titel der Komponente zeigen soll
   */
  /*public*/ function setTitleUrl($titleUrl)
  {
    $this->titleUrl = $titleUrl;
  }
  
  /*public*/ function getTitleUrl()
  {
    return $this->titleUrl;
  }
  
  /**
   * Setzt den Inhalt der Komponente
   */
  /*public*/ function setContent($content)
  {
    $this->content = $content;
  }
  
  /*public*/ function getContent()
  {
    return $this->content;
  }

  /**
   * Setzt das Format der Komponente. Gueltige Formate sind "full" und "half"
   */
  /*public*/ function setFormat($format)
  {
    $formats = array('full', 'half');
    if(!in_array($format, $formats))
    {
      trigger_error('Unexpected format "'. $format .'"!', E_USER_ERROR);
    }
    $this->format = $format;
  }
  
  /*public*/ function getFormat()
  {
    return $this->format;
  }
  
  /**
   * Setzt den Titel des Blockes in dem die Komponente angezeigt werden soll
   */
  /*public*/ function setBlock($block)
  {
    $this->block = $block;
  }
  
  /*public*/ function getBlock()
  {
    return $this->block;
  }
  
  /*public*/ function _get()
  {
    global $I18N;
    
    $this->prepare();
    $content = $this->content;
    
    if($content)
    {
      $title = htmlspecialchars($this->title);
      
      if($this->titleUrl != '')
      {
        $title = '<a href="'. $this->titleUrl .'">'. $title .'</a>';
      }
      
    	return '<div class="rex-dashboard-component" id="'. $this->getId() .'">
                <h3 class="rex-hl2">'. $title .'</h3>
                %%actionbar%%
                %%config%%
                <div class="rex-dashboard-component-content">
                  '. $content .'
                </div>
                <div class="rex-dashboard-component-footer">
                  <p>
                    '. $I18N->msg('dashboard_component_lastupdate') .'
                    %%cachetime%%
                  </p>
                </div>
              </div>
              <script type="text/javascript">componentInit("'. $this->getId() .'")</script>';
    }
    
    return '';
  }
  
  /*
   * Static Method: Returns boolean if is notification
   */
  /*public static*/ function isValid($component)
  {
    return is_object($component) && is_a($component, 'rex_dashboard_component');
  }
}