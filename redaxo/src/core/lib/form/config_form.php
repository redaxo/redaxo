<?php

/**
 * @package redaxo\core
 */
class rex_config_form extends rex_form_base
{
    use rex_factory_trait;

    private $namespace;

    protected function __construct($namespace, $fieldset = null, $debug = false)
    {
        parent::__construct($fieldset, md5($this->namespace.$fieldset), 'post', $debug);

        $this->namespace = $namespace;
    }

    public static function factory($namespace, $fieldset = null, $debug = false)
    {
        $class = static::getFactoryClass();
        return new $class($namespace, $fieldset, $debug);
    }

    protected function loadBackendConfig()
    {
        parent::loadBackendConfig();

        $attr = ['type' => 'submit', 'internal::useArraySyntax' => false, 'internal::fieldSeparateEnding' => true];
        $this->addControlField(
            null,
            $this->addField('button', 'save', rex_i18n::msg('form_save'), $attr, false)
        );
    }

    protected function getValue($name)
    {
        return rex_config::get($this->namespace, $name);
    }

    protected function save()
    {
        foreach ($this->getSaveElements() as $fieldsetName => $fieldsetElements) {
            foreach ($fieldsetElements as $element) {
                // read-only-fields nicht speichern
                if (strpos($element->getAttribute('class'), 'form-control-static') !== false) {
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
