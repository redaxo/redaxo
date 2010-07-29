<?php

class rex_form_container_element extends rex_form_element
{
  var $fields;
  var $multiple;
  var $active;
  
  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  function rex_form_container_element($tag = '', &$table, $attributes = array())
  {
    parent::rex_form_element('', $table, $attributes);
    $this->fields = array();
    $this->multiple = true;
  }
  
  function setMultiple($multiple = true)
  {
    $this->multiple = $multiple;
  }
  
  function setActive($group)
  {
    $this->active = $group;
  }
  
  function &addField($type, $name, $value = null, $attributes = array())
  {
    return $this->addGroupedField('elementContainer', $type, $name, $value, $attributes);
  }
  
  function &addGroupedField($group, $type, $name, $value = null, $attributes = array())
  {
    $field =& $this->table->createInput($type, $name, $value, $attributes);
    
    if(!isset($this->fields[$group]))
    {
      $this->fields[$group] = array();
    }
    
    $this->fields[$group][] =& $field;
    return $field;
  }
  
  function getFields()
  {
    return $this->fields;
  }
  
  function prepareInnerFields()
  {
    $values = unserialize($this->getValue());
    if($this->multiple)
    {
      foreach($this->fields as $group => $groupFields)
      {
        foreach($groupFields as $key => $field)
        {
          if(isset($values[$group][$field->getFieldName()]))
          {
            // PHP4 compat notation
            $this->fields[$group][$key]->setValue($values[$group][$field->getFieldName()]);   
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
          // PHP4 compat notation
          $this->fields[$this->active][$key]->setValue($values[$field->getFieldName()]);  
        }
      }
    }
  }
  
  function formatElement()
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
    
  function get()
  {
    $s = '';
    $s .= $this->getHeader();
    $s .= $this->_get();
    $s .= $this->getFooter();
    
    return $s;
  }

  function getSaveValue()
  {
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
    return serialize($value);
  }
}