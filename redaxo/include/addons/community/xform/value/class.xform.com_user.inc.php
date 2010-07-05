<?php

// ************************************* XFORM USER

class rex_xform_com_user extends rex_xform_abstract
{

  function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
  {   
    global $REX;

    $show_label = "email";
    $show_value = "";
    if(isset($this->elements[6]))
    {
      $show_label = $this->elements[6];
    }
    
    $this->value = -1;
    if (isset($REX["COM_USER"]) && is_object($REX["COM_USER"]))
    {
      $this->value = $REX["COM_USER"]->getValue($this->elements[2]);
      $show_value = $REX["COM_USER"]->getValue($show_label);
    }

    $wc = "";
    if (isset($warning["el_" . $this->getId()]))
    {
      $wc = $warning["el_" . $this->getId()];
    }

    if (!isset($this->elements[4]) || trim($this->elements[4]) != "hidden")
    {
      $form_output[] = '
        <p class="formtext">
          <label class="text ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[3] . '</label>
          <input type="text" class="text inp_disabled" disabled="disabled"  id="el_' . $this->id . '" value="'.htmlspecialchars($show_value) . '" />
        </p>';
    }
    
    $email_elements[$this->getLabel()] = stripslashes($this->value);
    if (!isset($this->elements[5]) || $this->elements[5] != "no_db")
    {
      $sql_elements[$this->getLabel()] = $this->value;
    }
    
  }
  
  function getDescription()
  {
    return "com_user -> Beispiel: com_user|label|dbfield|Fieldlabel|hidden|[no_db]|showlabel";
  }
  
}

?>