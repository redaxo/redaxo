<?php

/**
 * Backenddashboard Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo\be-dashboard
 */
abstract class rex_dashboard_component_base
{
    protected $config;

    private $id;
    private $funcCache;

    public function __construct($id, array $cache_options = [])
    {
        $this->id = $id;
        $this->funcCache = new rex_dashboard_function_cache(new rex_dashboard_file_cache($cache_options));
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
        if ($this->checkPermission()) {
            $callable = [$this, '_get'];
            $cachekey = $this->funcCache->computeCacheKey($callable, [rex::getUser()->getLogin()]);
            $cacheBackend = $this->funcCache->getCache();

            $configForm = '';
            if ($this->config) {
                $configForm = $this->config ? $this->config->get() : '';

                // config changed -> remove cache to reflect changes
                if ($this->config->changed()) {
                    $cacheBackend->remove($cachekey);
                }
            }

            // refresh clicked in actionbar
            if (rex_get('refresh', 'string') == $this->getId()) {
                $cacheBackend->remove($cachekey);
            }

            // prueft ob inhalte des callables gecacht vorliegen
            $content = $this->funcCache->call($callable, [rex::getUser()->getLogin()]);

            // wenn gecachter inhalt leer ist, vom cache entfernen und nochmals checken
            // damit leere komponenten sofort angezeigt werden, wenn neue inhalte verfuegbar sind
            if ($content == '') {
                $cacheBackend->remove($cachekey);
                $content = $this->funcCache->call($callable, [rex::getUser()->getLogin()]);
            }

            $cachestamp = $cacheBackend->getLastModified($cachekey);
            if (!$cachestamp) $cachestamp = time(); // falls kein gueltiger cache vorhanden
            $cachetime = rex_formatter::strftime($cachestamp, 'datetime');

            $content = strtr($content, ['%%actionbar%%' => $this->getActionBar()]);
            $content = strtr($content, ['%%cachetime%%' => $cachetime]);
            $content = strtr($content, ['%%config%%' => $configForm]);

            // refresh clicked in actionbar
            if (rex_get('ajax-get', 'string') == $this->getId()) {
                rex_response::sendContent($content);
                exit;
            }

            return $content;
        }
        return '';
    }

    protected function getId()
    {
        return 'rex-component-' . $this->id;
    }

    protected function getActions()
    {
        $actions = [];
        $actions[] = ['name' => 'refresh', 'class' => 'rex-i-refresh'];

        if ($this->config)
            $actions[] = ['name' => 'toggleSettings', 'class' => 'rex-i-togglesettings'];

        $actions[] = ['name' => 'toggleView', 'class' => 'rex-i-toggleview-off'];

        // ----- EXTENSION POINT
        $actions = rex_extension::registerPoint(new rex_extension_point('DASHBOARD_COMPONENT_ACTIONS', $actions));

        return $actions;
    }

    public function getActionBar()
    {
        $content = '';

        $content .= '<ul class="rex-dashboard-component-navi">';
        foreach ($this->getActions() as $action) {
            $laction = strtolower($action['name']);
            $class = $action['class'];
            $id = $this->getId() . '-' . $laction;
            $onclick = 'component' . ucfirst($action['name']) . '(\'' . $this->getId() . '\'); return false;';
            $title = rex_i18n::msg('dashboard_component_action_' . $laction);

            $content .= '<li>';
            $content .= '<a class="' . $class . '" href="#" onclick="' . $onclick . '" id="' . $id . '" title="' . $title . '">';
            $content .= '<span>' . $title . '</span>';
            $content .= '</a>';
            $content .= '</li>';
        }
        $content .= '</ul>';

        $content = '<div class="rex-dashboard-action-bar">
                                        ' . $content . '
                                </div>';

        return $content;
    }

    abstract public function _get();

    public function registerAsExtension(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();
        $subject[] = $this;
        return $subject;
    }
}
