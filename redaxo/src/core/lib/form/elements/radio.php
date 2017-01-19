<?php

/**
 * @package redaxo\core
 */
class rex_form_radio_element extends rex_form_options_element
{
    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_form $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
        // Jedes radio bekommt eingenes Label
    }

    protected function formatLabel()
    {
        // Da Jedes Feld schon ein Label hat, hier nur eine "Ueberschrift" anbringen
        return '<label class="control-label">' . $this->getLabel() . '</label>';
    }

    public function formatElement()
    {
        $s = '';
        $value = $this->getValue();
        $options = $this->getOptions();
        $id = $this->getAttribute('id');

        $attr = '';
        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            if ($attributeName == 'id') {
                continue;
            }
            $attr .= ' ' . htmlspecialchars($attributeName) . '="' . htmlspecialchars($attributeValue) . '"';
        }

        $formElements = [];

        foreach ($options as $opt_name => $opt_value) {
            $checked = $opt_value == $value ? ' checked="checked"' : '';

            $n = [];
            $n['label'] = '<label class="control-label">' . htmlspecialchars($opt_name) . '</label>';
            $n['field'] = '<input type="radio" value="' . htmlspecialchars($opt_value) . '"' . $attr . $checked . ' />';
            $formElements[] = $n;
        }

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $s = $fragment->parse('core/form/radio.php');

        return $s;
    }
}
