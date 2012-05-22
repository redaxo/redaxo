<?php

class rex_form_container_element extends rex_form_element
{
  private
    $fields,
    $multiple,
    $active;

  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  public function __construct($tag = '', rex_form $table = null, array $attributes = array())
  {
    parent::__construct('', $table, $attributes);
    $this->fields = array();
    $this->multiple = true;
  }

  public function setMultiple($multiple = true)
  {
    $this->multiple = $multiple;
  }

  public function setActive($group)
  {
    $this->active = $group;
  }

  public function addField($type, $name, $value = null, array $attributes = array())
  {
    return $this->addGroupedField('elementContainer', $type, $name, $value, $attributes);
  }

  public function addGroupedField($group, $type, $name, $value = null, array $attributes = array())
  {
    $field = $this->table->createInput($type, $name, $value, $attributes);

    if(!isset($this->fields[$group]))
    {
      $this->fields[$group] = array();
    }

    $this->fields[$group][] = $field;
    return $field;
  }

  public function getFields()
  {
    return $this->fields;
  }

  protected function prepareInnerFields()
  {
    $values = json_decode($this->getValue(), true);
    if($this->multiple)
    {
      foreach($this->fields as $group => $groupFields)
      {
        foreach($groupFields as $key => $field)
        {
          if(isset($values[$group][$field->getFieldName()]))
          {
            $field->setValue($values[$group][$field->getFieldName()]);
          }
        }
      }
    }
    elseif(isset($this->active) && isset($this->fields[$this->active]))
    {
      foreach($this->fields[$this->active] as $key => $field)
      {
        if(isset($values[$field->getFieldName()]))
        {
          $field->setValue($values[$field->getFieldName()]);
        }
      }
    }
  }

  public function formatElement()
  {
    $this->prepareInnerFields();

    $attr = '';
    // Folgende attribute filtern:
    // - name: der container selbst ist kein feld, daher hat er keinen namen
    // - id:   eine id vergeben wir automatisiert pro gruppe
    $attributeFilter = array('id', 'name');
    foreach($this->getAttributes() as $attributeName => $attributeValue)
    {
      if(in_array($attributeName, $attributeFilter)) continue;

      $attr .= ' '. $attributeName .'="'. $attributeValue .'"';
    }

    $format = '';
    foreach($this->fields as $group => $groupFields)
    {
      $format .= '<div id="rex-'. $group .'"'. $attr .'>';
      foreach($groupFields as $field)
      {
          $format .= $field->get();
      }
      $format .= '</div>';
    }
    return $format;
  }

  public function get()
  {
    $s = '';
    $s .= $this->getHeader();
    $s .= $this->_get();
    $s .= $this->getFooter();

    return $s;
  }

  public function getSaveValue()
  {
//    var_dump($this->multiple);
//    var_dump($this->active);
    $value = array();
    if($this->multiple)
    {
      foreach($this->fields as $group => $groupFields)
      {
        foreach($groupFields as $field)
        {
          // read-only-fields nicht speichern
          if(strpos($field->getAttribute('class'), 'rex-form-read') === false)
          {
            $value[$group][$field->getFieldName()] = $field->getSaveValue();
          }
        }
      }
    }
    elseif(isset($this->active) && isset($this->fields[$this->active]))
    {
      foreach($this->fields[$this->active] as $field)
      {
        // read-only-fields nicht speichern
        if(strpos($field->getAttribute('class'), 'rex-form-read') === false)
        {
          $value[$field->getFieldName()] = $field->getSaveValue();
        }
      }
    }
    return json_encode($value);
  }
}
