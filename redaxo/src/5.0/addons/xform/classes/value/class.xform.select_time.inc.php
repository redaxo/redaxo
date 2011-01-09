<?php

class rex_xform_select_time extends rex_xform_abstract
{

  function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
  {

    $hour = date("H");
    $min = date("i");
    $sec = 0;
    
    if (!is_array($this->getValue()) && strlen($this->getValue()) == 8)
    {
      if($d = explode(":",$this->getValue()))
      {
        $hour = (int) $d[0];
        $min = (int) $d[1];
        $sec = (int) $d[2];
      }
    }
    
    $formname = 'FORM['.$this->params["form_name"].'][el_'.$this->id.']';

    $isotime = sprintf ("%02d:%02d:%02d", $hour, $min, $sec);

    $email_elements[$this->getName()] = $isotime;
    $sql_elements[$this->getName()] = $isotime;
    
    $out = "";
    $out .= '
    <p class="form_select_time">
          <label class="select" for="el_'.$this->getId().'" >'.$this->elements[2].'</label>';
        
    $hsel = new rex_select;
    $hsel->setName($formname.'[hour]');
    $hsel->setAttribute('class', 'formdate-hour');
    $hsel->setId('el_'.$this->id.'_hour');
    $hsel->setSize(1);
    // $hsel->addOption("HH","00");

    $von_h = 0;
    $bis_h = 24;
    
    if(isset($this->elements[4]) && trim($this->elements[4]) != "")
    {
      if($a = explode(",",$this->elements[4]))
      {
        $von_h = (int) $a[0]; 
        $bis_h = (int) $a[1]; 
      }
    }

    if($von_h<0 || $von_h>23)
      $von_h = 0;
    if($bis_h<1 || $bis_h>23)
      $bis_h = 23;
    
    for($i=0;$i<24;$i++)
    {
      $hsel->addOption(str_pad($i,2,'0',STR_PAD_LEFT),str_pad($i,2,'0',STR_PAD_LEFT));
    }
    $hsel->setSelected($hour);
    $out .= '<div class="hour"><span>Stunde:</span>'.$hsel->get().'h</div>';

    $msel = new rex_select;
    $msel->setName($formname.'[min]');
    $msel->setAttribute('class', 'formdate-minute');
    $msel->setId('el_'.$this->id.'_min');
    $msel->setSize(1);
    // $msel->addOption("MM","0");
    
    $mmm = array();
    if(isset($this->elements[4]) && trim($this->elements[4]) != "")
      $mmm = explode(",",trim($this->elements[4]));
    
    if(count($mmm)>0)
    {
      foreach($mmm as $m)
      {
        $msel->addOption($m,$m);
      }
    }else
    {
      for($i=0;$i<61;$i++)
      {
        $msel->addOption(str_pad($i,2,'0',STR_PAD_LEFT),str_pad($i,2,'0',STR_PAD_LEFT));
      }
    }
    $msel->setSelected($min);
    $out .= '<div class="minute"><span>Minute:</span>'.$msel->get().'m</div>';

    $out .= '</p>';

    $form_output[] = $out;

  }
  function getDescription()
  {
    return "select_time -> Beispiel: select_time|feldname|Text *|von_stunde,bis_stunde|minutenformate 00,15,30,45";
  }
  
  function preValidateAction()
  {
    if(is_array($this->getValue()))
    {
      $a = $this->getValue();
      $hour = (int) @$a["hour"];
      $min = (int) @$a["min"];
      
      $r = 
        str_pad($hour, 2, "0", STR_PAD_LEFT).":".
        str_pad($min, 2, "0", STR_PAD_LEFT).":00";
      
      $this->setValue($r);
    }
  }
  
  
  
  
}

?>