<?php

class rex_form_checkbox_element extends rex_form_options_element
{
  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  public function __construct($tag = '', rex_form $table = null, array $attributes = array())
  {
    parent::__construct('', $table, $attributes);
    // Jede checkbox bekommt eingenes Label
    $this->setLabel('');
    $this->setAttribute('class', 'rex-form-checkbox rex-form-label-right');
  }

  protected function formatLabel()
  {
    // Da Jedes Feld schon ein Label hat, hier nur eine "Ueberschrift" anbringen
    $label = $this->getLabel();

    if($label != '')
    {
      $label = '<span>'. $label .'</span>';
    }

    return $label;
  }

  public function formatElement()
  {
    $s = '';
    $values = explode('|', trim($this->getValue(), '|'));
    $options = $this->getOptions();
    $name = $this->getAttribute('name');
    $id = $this->getAttribute('id');

    $attr = '';
    foreach($this->getAttributes() as $attributeName => $attributeValue)
    {
      if($attributeName == 'name' || $attributeName == 'id') continue;
      $attr .= ' '. $attributeName .'="'. $attributeValue .'"';
    }

    foreach($options as $opt_name => $opt_value)
    {
      $opt_id = $id;
      if($opt_value != '') {
       $opt_id .= '_'. $this->_normalizeId($opt_value);
      }
      $opt_attr = $attr . ' id="'. $opt_id .'"';
      $checked = in_array($opt_value, $values) ? ' checked="checked"' : '';

      $s .= '<input type="checkbox" name="'. $name .'['. $opt_value .']" value="'. htmlspecialchars($opt_value) .'"'. $opt_attr . $checked.' />
             <label for="'. $opt_id .'">'. $opt_name .'</label>';
    }
    return $s;
  }
}