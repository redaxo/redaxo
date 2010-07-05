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

/*abstract*/ class rex_dashboard_component_config
{
  var $id;
  var $settings;
  var $settingsCache;
  
  /*public*/ function rex_dashboard_component_config($defaultSettings)
  {
    static $counter = 0;
    $counter++;
    
    $options = array(
      'cache_dir' => dirname(__FILE__). '/../settings',
    );
    
    $this->id = $counter;
    $this->settingsCache = new rex_file_cache($options);
    $this->settings = $this->load($defaultSettings);
  }
  
  /**
   * Gibt die HTML Input Elemente zurück, die das Konfigurationsformular darstellen.
   * 
   * Jedes Formular-Element muss einen Namen tragen der mittels getInputName() generiert wurden,
   * damit zwischen den Komponenten keine Kkollissionen auftreten.
   */
  /*protected*/ function getForm()
  {
    trigger_error('The getForm method has to be overridden by a subclass!', E_USER_ERROR);
  }
  
  /**
   * Stellt aus den Daten des POSTs die Einstellungen der Komponente her. 
   */
  /*protected*/ function getFormValues()
  {
      trigger_error('The getFormValues method has to be overridden by a subclass!', E_USER_ERROR);
  }
  
  /**
   * Laedt die Einstellungen der Komponente.
   * Falls noch keine Einstellungen hinterlegt sind, wird $defaultSettings als Einstellungen geladen.  
   */
  /*protected*/ function load($defaultSettings)
  {
    return unserialize($this->settingsCache->get($this->getCacheKey(), serialize($defaultSettings)));
  }
  
  /*private*/ function getCacheKey()
  {
    global $REX;
    
    return get_class($this) .'_uid'. $REX['USER']->getValue('user_id');
  }
  
  /**
   * Persistiert die Einstellungen
   */
  /*protected*/ function persist()
  {
    $this->settings = $this->getFormValues();
    
    // cache-lifetime ~ 300 jahre
    $this->settingsCache->set($this->getCacheKey(), serialize($this->settings), 10000);
  }
  
  /**
   * Erstellt den Namen fuer ein Input-Element zur benutzung in getForm()
   */
  /*protected*/ function getInputName($key)
  {
    return 'component_'. $this->id .'_'. $key;
  }
  
  /**
   * Gibt zurück, ob die Einstellungen geaendert worden.
   */
  /*public*/ function changed()
  {
    $btnName = $this->getInputName('save_btn');
    return rex_post($btnName, 'boolean');
  }
  
  /**
   * Gibt die Konfiguration in HTML-Form zurueck
   */
  /*public*/ function get()
  {
    global $REX, $I18N;
    
    if($this->changed())
    {
      $this->persist();
    }
    
    $content = $this->getForm();
    if($content != '')
    {
      $btnName = $this->getInputName('save_btn');
      
      $content = '<div class="rex-form rex-dashboard-component-config">
                    <form action="index.php" method="post">
                      <input type="hidden" name="page" value="'. $REX['PAGE'] .'" />
                      '. $content .'
                      <p class="rex-form-col-a rex-form-submit">
                        <input type="submit" class="rex-form-submit" name="'. $btnName .'" value="'. $I18N->msg('dashboard_component_save_config') .'" />
                      </p>
                    </form>
                  </div>';
    }
    
    return $content;
  }
}