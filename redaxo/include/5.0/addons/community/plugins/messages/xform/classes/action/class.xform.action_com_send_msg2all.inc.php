<?php

class rex_xform_action_com_send_msg2all extends rex_xform_action_abstract
{

  function execute()
  {

    $subject_key = $this->action["elements"][2];
    $body_key = $this->action["elements"][3];
    $user_id_key = $this->action["elements"][4];
    
    foreach($this->elements_sql as $key => $value)
    {
      if ($subject_key == $key) $subject = $value;
      if ($body_key == $key) $body = $value;
      if ($user_id_key == $key) $from_user_id = $value;

      // echo "<br /> $key => $value";

    }

    if ($subject == "" or $body == "" or $from_user_id == "") return FALSE;

    // User auslesen
    $gu = new rex_sql;
    // $gu->debugsql = 1;
    // $gu->setQuery('select * from rex_com_user where id<>"'.$from_user_id.'" order by id');
    $gu->setQuery('select * from rex_com_user order by id');
    
    foreach($gu->getArray() as $user)
    {
      
      $user_body = $body;
      $user_subject = $subject;
      $to_user_id = $user["id"];
    
      // Empfaenger einbauen
    
      $in = new rex_sql;
      // $in->debugsql = 1;
      $in->setTable("rex_com_message");
      $in->setValue("user_id",$to_user_id);
      $in->setValue("from_user_id",$from_user_id);
      $in->setValue("to_user_id",$to_user_id);
      $in->setValue("subject",$user_subject);
      $in->setValue("body",$user_body);
      $in->setValue("create_datetime",time());
      $in->insert();
    
      /*
      $in = new rex_sql;
      // $in->debugsql = 1;
      $in->setTable("rex_com_message");
      $in->setValue("user_id",$from_user_id);
      $in->setValue("from_user_id",$from_user_id);
      $in->setValue("to_user_id",$to_user_id);
      $in->setValue("subject",$user_subject);
      $in->setValue("body",$user_body);
      $in->setValue("create_datetime",time());
      $in->insert();
      */
    
      rex_com_user::exeAction($to_user_id,"sendemail_newmessage", $user);

    }
    
  }

  function getDescription()
  {
    return "action|com_send_msg2all|subject|body|user_id";
  }

}

?>