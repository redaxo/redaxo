<?php

// ************************************* XFORM USER

class rex_xform_com_user_email extends rex_xform_abstract
{

  // Aufruf des Objektes mit den verschiedenen Zeigern
  function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
  {   
  
  
    global $REX;
  
    $this->label = $this->elements[1];
    $this->request_field = $this->elements[3];
    $field = rex_request($this->request_field,"int","0");
    $this->user_name = "ddd";

    if ($field != "0" && rex_com_user::createObject($field))
    {
      $this->value = $REX["COM_CACHE"]["USER"][$field]->getValue("email");
      $this->user_name = rex_com_showUser(&$REX["COM_CACHE"]["USER"][$field],"name","",FALSE);
    }else
    {
      $warning = $this->elements[5];
      return;
    }

    $form_output[] = '
      <p class="formtext">
        <input type="hidden" name="'.$this->request_field.'" value="'.htmlspecialchars($field).'" />
        <label class="text" for="el_' . $this->id . '" >' . $this->elements[2] . '</label>
        <input type="text" class="text inp_disabled" disabled="disabled"  id="el_' . $this->id . '" value="'.htmlspecialchars($this->user_name) . '" />
      </p>';

    $email_elements[$this->elements[1]] = stripslashes($this->value);
    if (!isset($this->elements[4]) || $this->elements[4] != "no_db") $sql_elements[$this->elements[1]] = $this->value;

    return;
    
  }
  
  function getDescription()
  {
    return "com_user_email|emaillabel|name|REQUEST_FIELD|[no_db]|User wurde nicht gefunden";
  }
}

?>