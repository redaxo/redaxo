<?php

/**
 * Backenddashboard Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

abstract class rex_dashboard_component_base
{
  protected
    $config;

  private
    $id,
    $funcCache;

  public function __construct($id, array $cache_options = array())
  {
    $this->id = $id;
    $this->funcCache = new rex_function_cache(new rex_file_cache($cache_options));
  }

  protected function prepare()
  {
    // override in subclasses to prepare component
  }


  public function checkPermission()
  {
    // no permission required by default
    return true;
  }

  public function setConfig(rex_dashboard_component_config $config)
  {
    $this->config = $config;
  }

  public function get()
  {
    if($this->checkPermission())
    {
      $callable = array($this, '_get');
      $cachekey = $this->funcCache->computeCacheKey($callable, array(rex::getUser()->getUserLogin()));
      $cacheBackend = $this->funcCache->getCache();

      $configForm = '';
      if($this->config)
      {
        $configForm = $this->config ? $this->config->get() : '';

        // config changed -> remove cache to reflect changes
        if($this->config->changed())
        {
          $cacheBackend->remove($cachekey);
        }
      }

      // refresh clicked in actionbar
      if(rex_get('refresh', 'string') == $this->getId())
      {
        $cacheBackend->remove($cachekey);
      }

      // prueft ob inhalte des callables gecacht vorliegen
      $content = $this->funcCache->call($callable, array(rex::getUser()->getUserLogin()));

      // wenn gecachter inhalt leer ist, vom cache entfernen und nochmals checken
      // damit leere komponenten sofort angezeigt werden, wenn neue inhalte verfuegbar sind
      if($content == '')
      {
        $cacheBackend->remove($cachekey);
        $content = $this->funcCache->call($callable, array(rex::getUser()->getUserLogin()));
      }

      $cachestamp = $cacheBackend->getLastModified($cachekey);
      if(!$cachestamp) $cachestamp = time(); // falls kein gueltiger cache vorhanden
      $cachetime = rex_formatter::format($cachestamp, 'strftime', 'datetime');

      $content = strtr($content, array('%%actionbar%%' => $this->getActionBar()));
      $content = strtr($content, array('%%cachetime%%' => $cachetime));
      $content = strtr($content, array('%%config%%' => $configForm));

      // refresh clicked in actionbar
      if(rex_get('ajax-get', 'string') == $this->getId())
      {
        // clear output-buffer
        while(@ob_end_clean());

        rex_response::sendResource($content);
        exit();
      }

      return $content;
    }
    return '';
  }

  protected function getId()
  {
    return 'rex-component-'. $this->id;
  }

  protected function getActions()
  {
    $actions = array();
    $actions[] = array('name' => 'refresh', 'class' => 'rex-i-refresh');

    if($this->config)
      $actions[] = array('name' => 'toggleSettings', 'class' => 'rex-i-togglesettings');

    $actions[] = array('name' => 'toggleView', 'class' => 'rex-i-toggleview-off');

    // ----- EXTENSION POINT
    $actions = rex_extension::registerPoint('DASHBOARD_COMPONENT_ACTIONS', $actions);

    return $actions;
  }

  public function getActionBar()
  {
    $content = '';

    $content .= '<ul class="rex-dashboard-component-navi">';
    foreach($this->getActions() as $action)
    {
      $laction = strtolower($action['name']);
      $class = $action['class'];
      $id = $this->getId(). '-'. $laction;
      $onclick = 'component'. ucfirst($action['name']) .'(\''. $this->getId() .'\'); return false;';
      $title = rex_i18n::msg('dashboard_component_action_'. $laction);

      $content .= '<li>';
      $content .= '<a class="'.$class.'" href="#" onclick="'.$onclick.'" id="'.$id.'" title="'.$title.'">';
      $content .= '<span>'.$title.'</span>';
      $content .= '</a>';
      $content .= '</li>';
    }
    $content .= '</ul>';

    $content = '<div class="rex-dashboard-action-bar">
                    '. $content .'
                </div>';

    return $content;
  }

  public function _get()
  {
    trigger_error('The _get method has to be overridden by a subclass!', E_USER_ERROR);
  }

  public function registerAsExtension($params)
  {
    $params['subject'][] = $this;
    return $params['subject'];
  }
}
