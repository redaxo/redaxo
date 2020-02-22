<?php

/**
 * @package redaxo\core\form
 */
class rex_form_checkbox_element extends rex_form_options_element
{
    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct('', $table, $attributes);
        // Jede checkbox bekommt eingenes Label
        $this->setLabel('');
    }

    protected function formatLabel()
    {
        // Da Jedes Feld schon ein Label hat, hier nur eine "Ueberschrift" anbringen
        $label = $this->getLabel();

        if ('' != $label) {
            $label = '<label class="control-label">' . $label . '</label>';
        }

        return $label;
    }

    /**
     * @return string
     */
    public function formatElement()
    {
        $s = '';
        $values = explode('|', trim($this->getValue(), '|'));
        $options = $this->getOptions();
        $name = $this->getAttribute('name');
        $id = $this->getAttribute('id');

        $attr = '';
        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            if ('name' == $attributeName || 'id' == $attributeName) {
                continue;
            }
            $attr .= ' ' . rex_escape($attributeName, 'html_attr') . '="' . rex_escape($attributeValue) . '"';
        }

        $formElements = [];

        foreach ($options as $opt_name => $opt_value) {
            $opt_id = $id;
            if ('' != $opt_value) {
                $opt_id .= '-' . rex_string::normalize($opt_value, '-');
            }
            $opt_attr = $attr . ' id="' . rex_escape($opt_id) . '"';
            $checked = in_array($opt_value, $values) ? ' checked="checked"' : '';

            $n = [];
            $n['label'] = '<label class="control-label" for="' . rex_escape($opt_id) . '">' . rex_escape($opt_name) . '</label>';
            $n['field'] = '<input type="checkbox" name="' . rex_escape($name) . '[' . rex_escape($opt_value) . ']" value="' . rex_escape($opt_value) . '"' . $opt_attr . $checked . ' />';
            $formElements[] = $n;
        }

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $fragment->setVar('grouped', true);
        $s = $fragment->parse('core/form/checkbox.php');

        return $s;
    }
}
