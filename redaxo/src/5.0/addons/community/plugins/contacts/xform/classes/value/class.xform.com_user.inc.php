<?php

// ************************************* XFORM USER

class rex_xform_com_user extends rex_xform_abstract
{

  // Aufruf des Objektes mit den verschiedenen Zeigern
  function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
  {   
    $this->label = $this->elements[1];

    // name="FORM[' . $this->params["form_name"] . '][el_' . $this->id . ']"

    global $REX;
  
    $this->value = -1;
    $this->user_name = "";
    if (isset($REX["COM_USER"]) && is_object($REX["COM_USER"]))
    {
      $user_field = $REX["COM_USER"]->getValue($this->elements[2]);
      $this->value = $user_field;
      $user_name = rex_com_showUser(&$REX["COM_USER"],"name","rex_com_user",FALSE);
      $this->user_name = $user_name;
      // echo '<p>Wert wurde neu gesetzt auf: '.$this->value.'</p>';
    }

    $wc = "";
    if (isset($warning["el_" . $this->getId()])) $wc = $warning["el_" . $this->getId()];

    if (!isset($this->elements[4]) || trim($this->elements[4]) != "hidden")
    {
      $form_output[] = '
        <p class="formtext">
        <label class="text ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[3] . '</label>
        <input type="text" class="text inp_disabled" disabled="disabled"  id="el_' . $this->id . '" value="'.htmlspecialchars($this->user_name) . '" />
        </p>';
    }
    
    $email_elements[$this->elements[1]] = stripslashes($this->value);
    if (!isset($this->elements[5]) || $this->elements[5] != "no_db") $sql_elements[$this->elements[1]] = $this->value;
    
  }
  
  function getDescription()
  {
    return "com_user -> Beispiel: com_user|label|dbfield|Fieldlabel|hidden|[no_db]";
  }
}

?>