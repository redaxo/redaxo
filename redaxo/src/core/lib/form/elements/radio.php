<?php

/**
 * @package redaxo\core\form
 */
class rex_form_radio_element extends rex_form_options_element
{
    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
        // Jedes radio bekommt eingenes Label
    }

    /**
     * @return string
     */
    protected function formatLabel()
    {
        // Da Jedes Feld schon ein Label hat, hier nur eine "Ueberschrift" anbringen
        return '<label class="control-label">' . $this->getLabel() . '</label>';
    }

    /**
     * @return string
     */
    public function formatElement()
    {
        $s = '';
        $value = $this->getValue();
        $options = $this->getOptions();
        $id = $this->getAttribute('id');

        $attr = '';
        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            if ('id' == $attributeName) {
                continue;
            }
            $attr .= ' ' . rex_escape($attributeName, 'html_attr') . '="' . rex_escape($attributeValue) . '"';
        }

        $formElements = [];

        foreach ($options as $opt_name => $opt_value) {
            $checked = $opt_value == $value ? ' checked="checked"' : '';
            $opt_id = $id . '-' . rex_string::normalize($opt_value, '-');
            $opt_attr = $attr . ' id="' . $opt_id . '"';

            $n = [];
            $n['label'] = '<label class="control-label" for="' . $opt_id . '">' . rex_escape($opt_name) . '</label>';
            $n['field'] = '<input type="radio" value="' . rex_escape($opt_value) . '"' . $opt_attr . $checked . ' />';
            $formElements[] = $n;
        }

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $s = $fragment->parse('core/form/radio.php');

        return $s;
    }
}
