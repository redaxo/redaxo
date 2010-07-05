<?php

class rex_xform_action_com_user_action extends rex_xform_action_abstract
{

  function execute()
  {
    foreach($this->elements_sql as $key => $value)
    {
      if ($this->action["elements"][2]==$key)
      {
        $id = $value;
        break;
      }
    }
    return rex_com_user::exeAction($id,$this->action["elements"][3], $this->elements_email);
  }

  function getDescription()
  {
    return "action|com_user_action|to_user_id|sendemail_newmessage";
  }

}

?>