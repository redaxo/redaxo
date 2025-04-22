<?php

/**
 * Create forms for rex_config data.
 *
 * @package redaxo\core\form
 */
class rex_config_form extends rex_form_base
{
    use rex_factory_trait;

    /** @var string */
    private $namespace;
    
    /** @var array */
    private $additionalButtons = [];

    /**
     * @param string $namespace `rex_config` namespace, usually the package key
     * @param string|null $fieldset
     * @param bool $debug
     */
    protected function __construct($namespace, $fieldset = null, $debug = false)
    {
        parent::__construct($fieldset, md5($namespace . $fieldset), 'post', $debug);

        $this->namespace = $namespace;

        // --------- Load Env
        if (rex::isBackend()) {
            $this->loadBackendConfig();
        }
    }

    /**
     * @param string $namespace `rex_config` namespace, usually the package key
     * @param string|null $fieldset
     * @param bool $debug
     *
     * @return static
     */
    public static function factory($namespace, $fieldset = null, $debug = false)
    {
        $class = static::getFactoryClass();
        return new $class($namespace, $fieldset, $debug);
    }

    protected function loadBackendConfig()
    {
        parent::loadBackendConfig();

        // Wir erzeugen die Buttons erst beim Anzeigen des Formulars,
        // damit alle zusätzlichen Buttons erfasst werden können
    }
    
    /**
     * Adds a button to the form.
     *
     * @param string $name Name of the button (will be used as name attribute)
     * @param string $label Label text of the button
     * @param string $type Type of the button (submit, reset, button)
     * @param array $attributes Additional HTML attributes for the button
     * @return rex_form_element The created button element
     */
    public function addButton($name, $label, $type = 'submit', array $attributes = [])
    {
        $attr = array_merge(['type' => $type, 'internal::useArraySyntax' => false, 'internal::fieldSeparateEnding' => true], $attributes);
        $button = $this->addField('button', $name, $label, $attr, false);
        $this->additionalButtons[] = $button;
        return $button;
    }
    
    /**
     * @return string
     */
    public function get()
    {
        // Wir erstellen das Kontrollfeld mit allen Buttons kurz vor der Ausgabe des Formulars
        $this->createControlField();
        
        return parent::get();
    }
    
    /**
     * Creates the control field with the save button and any additional buttons
     */
    protected function createControlField()
    {
        // Standard-Speichern-Button
        $attr = ['type' => 'submit', 'internal::useArraySyntax' => false, 'internal::fieldSeparateEnding' => true];
        $saveButton = $this->addField('button', 'save', rex_i18n::msg('form_save'), $attr, false);
        
        // Control-Element mit allen Buttons erstellen
        if (empty($this->additionalButtons)) {
            // Standardverhalten, wenn keine zusätzlichen Buttons vorhanden sind
            $this->addControlField(null, $saveButton);
        } else {
            // Bei mindestens einem Button erstellen wir ein erweitertes Control-Element
            $buttons = array_merge([$saveButton], $this->additionalButtons);
            
            // Die ersten fünf Buttons als Parameter übergeben
            $saveElement = isset($buttons[0]) ? $buttons[0] : null;
            $applyElement = isset($buttons[1]) ? $buttons[1] : null;
            $deleteElement = isset($buttons[2]) ? $buttons[2] : null;
            $resetElement = isset($buttons[3]) ? $buttons[3] : null;
            $abortElement = isset($buttons[4]) ? $buttons[4] : null;
            
            $this->addControlField($saveElement, $applyElement, $deleteElement, $resetElement, $abortElement);
        }
    }

    protected function getValue($name)
    {
        return rex_config::get($this->namespace, $name);
    }

    protected function save()
    {
        foreach ($this->getSaveElements() as $fieldsetElements) {
            foreach ($fieldsetElements as $element) {
                // read-only-fields nicht speichern
                if ($element->isReadOnly()) {
                    continue;
                }

                $fieldName = $element->getFieldName();
                $fieldValue = $element->getSaveValue();

                if (is_string($fieldValue)) {
                    $fieldValue = trim($fieldValue);
                }

                rex_config::set($this->namespace, $fieldName, $fieldValue);
            }
        }

        return true;
    }
}
