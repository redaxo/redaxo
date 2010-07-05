<?php

class rex_xform_datetime extends rex_xform_abstract
{

  function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
  {

    $day = date("d");
    $month = date("m");
    $year = date("Y");
    $hour = date("H");
    $min = date("i");
    
    if (!is_array($this->value) && strlen($this->value) == 12)
    {
      $min = (int) substr($this->value,13,2);
      $hour = (int) substr($this->value,11,2);
      $day = (int) substr($this->value,8,2);
      $month = (int) substr($this->value,5,2);
      $year = (int) substr($this->value,0,4);

    }else
    {
      if (isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["min"]))
        $min = (int) $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["min"];
      if (isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["hour"]))
        $hour = (int) $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["hour"];
      if (isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["day"]))
        $day = (int) $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["day"];
      if (isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["month"]))
        $month = (int) $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["month"];
      if (isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["year"]))
        $year = (int) $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id]["year"];

    }
    
    $formname = 'FORM['.$this->params["form_name"].'][el_'.$this->id.']';

    $twarning = "";
    if (!checkdate($month,$day,$year) && $send == 1)
    {
      $twarning = 'border:1px solid #f99;background-color:#f9f3f3;';
      $warning[] = "Datum ist falsch";
    }
    
    $isodatum = sprintf ("%04d-%02d-%02d %02d:%02d:%02d", $year, $month, $day, $hour, $min, 0);

    $email_elements[$this->getName()] = "$day.$month.$year $hour.$min";
    $sql_elements[$this->getName()] = $isodatum;
    
    $out = "";
    $out .= '
    <p class="formdate">
          <label class="select" for="el_'.$this->getId().'" >'.$this->elements[2].'</label>';
        
    $dsel = new rex_select;
    $dsel->setName($formname.'[day]');
    $dsel->setStyle("width:55px;".$twarning);
    $dsel->setId('el_'.$this->id.'_day');
    $dsel->setSize(1);
    $dsel->addOption("TT","0");
    for($i=1;$i<32;$i++)
    {
      $dsel->addOption($i,$i);
    }
    $dsel->setSelected($day);
    $out .= $dsel->get();

    $msel = new rex_select;
    $msel->setName($formname.'[month]');
    $msel->setStyle("width:55px;".$twarning);
    $msel->setId('el_'.$this->id.'_month');
    $msel->setSize(1);
    $msel->addOption("MM","0");
    for($i=1;$i<13;$i++)
    {
      $msel->addOption($i,$i);
    }
    $msel->setSelected($month);
    $out .= $msel->get();

    $year_start = (int) $this->elements[3];
    $year_end = (int) $this->elements[4];
    
    if ($year_start == 0) 
      $year_start = 1980;
    if ($year_end == 0) 
      $year_end = 2020;

    if ($year_end<$year_start) $year_end = $year_start;

    $ysel = new rex_select;
    $ysel->setName($formname.'[year]');
    $ysel->setStyle("width:88px;".$twarning);
    $ysel->setId('el_'.$this->id.'_year');
    $ysel->setSize(1);
    $ysel->addOption("YYYY","0");
    for($i=$year_start;$i<=$year_end;$i++)
    {
      $ysel->addOption($i,$i);
    }
    $ysel->setSelected($year);
    $out .= $ysel->get();

    $hsel = new rex_select;
    $hsel->setName($formname.'[hour]');
    $hsel->setStyle("width:55px;".$twarning);
    $hsel->setId('el_'.$this->id.'_hour');
    $hsel->setSize(1);
    $hsel->addOption("HH","00");
    for($i=0;$i<24;$i++)
    {
      $hsel->addOption(str_pad($i,2,'0',STR_PAD_LEFT),str_pad($i,2,'0',STR_PAD_LEFT));
    }
    $hsel->setSelected($hour);
    $out .= $hsel->get()."h";

    $msel = new rex_select;
    $msel->setName($formname.'[min]');
    $msel->setStyle("width:55px;".$twarning);
    $msel->setId('el_'.$this->id.'_min');
    $msel->setSize(1);
    $msel->addOption("MM","0");
    
    $mmm = array();
    if(isset($this->elements[5]) && $this->elements[5] != "")
      $mmm = explode(",",trim($this->elements[5]));
    
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
    $out .= $msel->get()."m";

    $out .= '</p>';

    $form_output[] = $out;

  }
  function getDescription()
  {
    return "datetime -> Beispiel: datetime|feldname|Text *|jahrstart|jahrsende|minutenformate 00,15,30,45";
  }
}

?>