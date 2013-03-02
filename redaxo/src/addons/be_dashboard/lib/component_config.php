<?php

/**
 * Backenddashboard Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo\be-dashboard
 */
abstract class rex_dashboard_component_config
{
    protected
        $settings;

    private
        $id,
        $settingsCache;

    public function __construct(array $defaultSettings)
    {
        static $counter = 0;
        $counter++;

        $dir = rex_path::addonData('be_dashboard', 'settings');
        rex_dir::create($dir);
        $options = array(
            'cache_dir' => $dir,
        );

        $this->id = $counter;
        $this->settingsCache = new rex_dashboard_file_cache($options);
        $this->settings = $this->load($defaultSettings);
    }

    /**
     * Gibt die HTML Input Elemente zurück, die das Konfigurationsformular darstellen.
     *
     * Jedes Formular-Element muss einen Namen tragen der mittels getInputName() generiert wurden,
     * damit zwischen den Komponenten keine Kkollissionen auftreten.
     *
     * @return string
     */
    abstract protected function getForm();

    /**
     * Stellt aus den Daten des POSTs die Einstellungen der Komponente her.
     *
     * @return array
     */
    abstract protected function getFormValues();

    /**
     * Laedt die Einstellungen der Komponente.
     * Falls noch keine Einstellungen hinterlegt sind, wird $defaultSettings als Einstellungen geladen.
     */
    protected function load(array $defaultSettings)
    {
        return unserialize($this->settingsCache->get($this->getCacheKey(), serialize($defaultSettings)));
    }

    private function getCacheKey()
    {
        return get_class($this) . '_uid' . rex::getUser()->getId();
    }

    /**
     * Persistiert die Einstellungen
     */
    protected function persist()
    {
        $this->settings = $this->getFormValues();

        // cache-lifetime ~ 300 jahre
        $this->settingsCache->set($this->getCacheKey(), serialize($this->settings), 10000);
    }

    /**
     * Erstellt den Namen fuer ein Input-Element zur benutzung in getForm()
     */
    protected function getInputName($key)
    {
        return 'component_' . $this->id . '_' . $key;
    }

    /**
     * Gibt zurück, ob die Einstellungen geaendert worden.
     */
    public function changed()
    {
        $btnName = $this->getInputName('save_btn');
        return rex_post($btnName, 'boolean');
    }

    /**
     * Gibt die Konfiguration in HTML-Form zurueck
     */
    public function get()
    {
        if ($this->changed()) {
            $this->persist();
        }

        $content = $this->getForm();
        if ($content != '') {
            $btnName = $this->getInputName('save_btn');

            $content = '<div class="rex-form rex-dashboard-component-config">
                                        <form action="' . rex_url::currentBackendPage() . '" method="post">
                                            ' . $content . '
                                            <p class="rex-form-col-a rex-form-submit">
                                                <input type="submit" class="rex-form-submit" name="' . $btnName . '" value="' . rex_i18n::msg('dashboard_component_save_config') . '" />
                                            </p>
                                        </form>
                                    </div>';
        }

        return $content;
    }
}
