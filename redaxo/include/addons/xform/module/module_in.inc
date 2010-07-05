<?php

// MODUL IN - XO FORM
// v0.18
// --------------------------------------------------------------------------------

?>

<style>
ul.xform {
  width:650px;
  float:none;
}</style>

<br />DebugModus:
<?php

$dbg = new rex_select();
$dbg->setName("VALUE[7]");
$dbg->setSize(1);
$dbg->setStyle("width:100px;");
$dbg->addOption("inakiv","0");
$dbg->addOption("Aktiv","1");
$dbg->setSelected("REX_VALUE[7]");
echo $dbg->get();

?>

<br /><br />Formularbeschreibung:
<br /><textarea name="VALUE[3]" style="width:90%;height:180px;">REX_VALUE[3]</textarea>

<?php rex_xform::showHelp(); ?>

<br /><br /><b>Danksagung:</b>
<br /><textarea name="VALUE[6]" style="width:90%;height:100px;">REX_VALUE[6]</textarea>

<br /><br /><hr>

<br /><b>Was soll passieren ?</b>
<br /><br /><select name="VALUE[1]" onchange="rex_xform_toggleDb(this);" style="width:90%;">
<option value="" <?php if("REX_VALUE[1]" == "") echo " selected "; ?>>Nichts machen</option>
<option value="0" <?php if("REX_VALUE[1]" == "0") echo " selected "; ?>>Nur in Datenbank speichern</option>
<option value="1" <?php if("REX_VALUE[1]" == "1") echo " selected "; ?>>Nur E-Mail versenden</option>
<option value="2" <?php if("REX_VALUE[1]" == "2") echo " selected "; ?>>E-Mail versenden und in Datenbank speichern</option>
<option value="3" <?php if("REX_VALUE[1]" == "3") echo " selected "; ?>>E-Mail versenden und Datenbank abfragen</option>
</select>

<br /><br />
<b>Daten zu Beginn aus der Datenbank holen</b>
<input type="checkbox" value="<?php if("REX_VALUE[10]"=="") echo "Ja"; else echo "REX_VALUE[10]";?>" name="VALUE[10]" <?php if("REX_VALUE[10]"!="") echo "checked";?>>

<div id="db_data">
  <br />
  <fieldset><legend>Datenbankdaten:</legend>
  
  <br /><b>Where Klausel angeben (PHP erlaubt)</b>
  <br />$xform-&gt;setObjectparams("main_where",$where);
  
  <br /><br /><textarea name="VALUE[9]" cols="30" rows="3" style="width:90%; height:100px;">REX_VALUE[9]</textarea>
  
  <div id="db_where_info">
    <table class="warning"><tr><td>
    In der Where Klausel koennen die verschiedenen Felder eingesetzt werden.
    Dazu muss der entsprechende Feldname zwischen ### gesetzt werden. Z.B.
    <br />text|myname|Name|1
    <br />-&gt; ###myname### als Platzhalter in der Mail
    </td></tr></table>
  </div>
  
  <br />
  <br /><b>Bitte Tabelle angeben </b>
  <br />(Diese Tabelle gilt auch bei Uniqueabfragen (Pflichtfeld=2) siehe oben
  <br /><br /><?php
  $gc = rex_sql::factory();
  $gc->setQuery("SHOW TABLES");
  $tables = $gc->getArray();
  
  $ts = new rex_select;
  $ts->setName("VALUE[8]");
  $ts->setSize(1);
  $ts->addOption("Keine Tabelle ausgewählt", "");
  
  foreach ($tables as $key => $value)
  {
    $ts->addOption(current($value), current($value));
  }
  
  $ts->setSelected("REX_VALUE[8]");
  
  echo $ts->show();
  ?>
  </fieldset>
</div>



<div id="mail_data">
  <br /> <br />
  <fieldset><legend>Emailversandtdaten:</legend>
  
  <br /><b>Bitte folgende Daten eingeben</b>
  <br /><br />Absender:
  <br /><input type="text" name="VALUE[2]" value="REX_VALUE[2]"  style="width:90%;" />
  
  <br /><br />Subject:
  <br /><input type="text" name="VALUE[4]" value="REX_VALUE[4]"  style="width:90%;" />
  
  <br /><br />Mailbody:
  <br /><textarea name="VALUE[5]" style="width:90%;height:100px;">REX_VALUE[5]</textarea>
  
  <table class="warning"><tr><td>
  In der Mail koennen die verschiedenen Felder eingesetzt werden.
  Dazu muss der entsprechende Feldname zwischen ### gesetzt werden. Z.B.
  <br />text|myname|Name|1
  <br />-&gt; ###myname### als Platzhalter in der Mail
  </td></tr></table>
  
  </fieldset>
</div>

<script type="text/javascript">
<!--

function rex_xform_toggleDb(select)
{
   rex_xform_toggleDbValue(select.value);
}

function rex_xform_toggleDbValue(value)
{
   var displayDb = false;
   var displayWhereInfo = false;
   var displayMail = false;
   
   // '' => DefaultValue wenn Modul geadded wird
   // 0 => Nur in Datenbank speichern
   // 1 => Nur E-Mail versenden
   // 2 => E-Mail versenden und in Datenbank speichern
   if(value == 0 || value == '')
   {
      displayDb = true;
   }
   else if(value == 1)
   {
      displayMail = true;
   }
   else if(value == 2)
   {
      displayDb = true;
      displayMail = true;
   }
   else if(value == 3)
   {
      displayDb = true;
      displayWhereInfo = true;
      displayMail = true;
   }
   
   displayDb = displayDb == true ? '' : 'none';
   rex_xform_toggleElement('db_data', displayDb);
   
   displayWhereInfo = displayWhereInfo == true ? '' : 'none';
   rex_xform_toggleElement('db_where_info', displayWhereInfo);
   
   displayMail = displayMail == true ? '' : 'none';
   rex_xform_toggleElement('mail_data', displayMail);
}

// Wenn der 2. Parameter angegeben wird, wird die style.display Eigenschaft auf den entsprechenden wert gesetzt,
// Sonst wird der wert getoggled
function rex_xform_toggleElement(id,display)
{
   var needle = new getObj(id);
   
   if (typeof(display) == 'undefined')
   {
     needle.style.display = needle.style.display == '' ? 'none' : '';
   }
   else
   {
     needle.style.display = display;
   }
}

rex_xform_toggleDbValue("REX_VALUE[1]");

//-->
</script>