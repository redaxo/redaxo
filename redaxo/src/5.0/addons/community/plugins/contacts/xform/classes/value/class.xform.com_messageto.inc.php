<?php

// ************************************* XFORM COM MESSAGETO

// TODO: Was passiert wenn kein Kontakt vorhanden und kein User übergeben ?

class rex_xform_com_messageto extends rex_xform_abstract
{

  function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
  {
    global $REX;

    $SEL = new rex_select();
    $SEL->setName('FORM[' . $this->params["form_name"] . '][el_' . $this->id . ']');
    $SEL->setId("el_" . $this->id);
    $SEL->setSize(1);
    if (isset($REX["COM_USER"]) && is_object($REX["COM_USER"])) $user_id = $REX["COM_USER"]->getValue("id");
    $sql = '
        select 
          rex_com_user.* 
        from 
          rex_com_contact,rex_com_user 
        where 
          rex_com_contact.to_user_id=rex_com_user.id 
          and rex_com_contact.user_id="'.$user_id.'" 
          and rex_com_contact.accepted=1';

    $teams = new rex_sql;
    // $teams->debugsql = 1;
    $teams->setQuery($sql);
    $sqlnames = array();
    $user_id = -1;
    if (isset($_REQUEST["user_id"])) $user_id = (int) $_REQUEST["user_id"];
    $no_user = true;
    for ($t = 0; $t < $teams->getRows(); $t++)
    {
      $SEL->addOption(rex_com_showUser(&$teams,"name",'',FALSE), $teams->getValue("id"));
      $sqlnames[$teams->getValue("id")] = rex_com_showUser(&$teams,"name",'',FALSE);
      if ($teams->getValue("id") == $user_id)
      {
        $this->value = $user_id;
        $user_id = -1;
      }
      $teams->next();
      $no_user = false;
    }

    if ($user_id>0)
    {
      $sql = 'select * from rex_com_user where id="'.$user_id.'" and status=1';
      $gu = new rex_sql;
      // $gu->debugsql = 1;
      $gu->setQuery($sql);
      if ($gu->getRows()==1)
      {
        $SEL->addOption(rex_com_showUser(&$gu,"name",'',FALSE), $gu->getValue("id"));
        $sqlnames[$gu->getValue("id")] = rex_com_showUser(&$gu,"name",'',FALSE);
        $this->value = $user_id;
        $form_output[] .= '<input type="hidden" name="user_id" value="'.$user_id.'" />';
        $no_user = false;
      }
    }
    $SEL->setSelected($this->value);
    $out = $SEL->get();

    $wc = "";
    if (isset($warning["el_" . $this->getId()])) $wc = $warning["el_" . $this->getId()];
    
    if ($no_user)
    {
      $warning["el_" . $this->getId()] = $this->params["error_class"];
      $out = "Kein User ausgewählt. Kein Versand möglich.";
    }


    $SEL->setStyle(' class="select ' . $wc . '"');

    $form_output[] = '
      <p class="formselect">
      <label class="select ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[2] . '</label>
      ' . $out . '
      </p>';

    $email_elements[$this->elements[1]] = stripslashes($this->value);
    if (isset($sqlnames[$this->value])) $email_elements[$this->elements[1].'_SQLNAME'] = stripslashes($sqlnames[$this->value]);
    if (!isset($this->elements[8]) || $this->elements[8] != "no_db") $sql_elements[$this->elements[1]] = $this->value;
    
  }
  
  function getDescription()
  {
    return "com_messageto";
  }
}

?>