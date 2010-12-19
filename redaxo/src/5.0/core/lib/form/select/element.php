<?php

class rex_form_select_element extends rex_form_element
{
  var $select;
  var $separator;

  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  function rex_form_select_element($tag = '', &$table, $attributes = array())
  {
    parent::rex_form_element('', $table, $attributes);

    $this->select = new rex_select();
    $this->separator = '|';
  }

  function formatElement()
  {
    $multipleSelect = false;

    // Hier die Attribute des Elements an den Select weitergeben, damit diese angezeigt werden
    foreach($this->getAttributes() as $attributeName => $attributeValue)
    {
      $this->select->setAttribute($attributeName, $attributeValue);
    }

    if ($this->select->hasAttribute('multiple'))
      $multipleSelect = true;

    if ($multipleSelect)
    {
        $this->setAttribute('name', $this->getAttribute('name').'[]');

        $selectedOptions = explode($this->separator, trim($this->getValue(), $this->separator));
        if (is_array($selectedOptions) && $selectedOptions[0] != '')
        {
          foreach($selectedOptions as $selectedOption)
          {
           $this->select->setSelected($selectedOption);
          }
        }
    }
    else
      $this->select->setSelected($this->getValue());

    $this->select->setName($this->getAttribute('name'));
    return $this->select->get();
  }

  function setSeparator($separator)
  {
    $this->separator = $separator;
  }

  function getSelect()
  {
    return $this->select;
  }

  function setSelect($selectObj)
  {
    $this->select = $selectObj;
    if($selectObj->hasAttribute('multiple'))
    {
      $this->setAttribute('multiple', $selectObj->getAttribute('multiple'));
    }
  }
}