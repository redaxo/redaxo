<?php

/**
 * XForm
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

class rex_xform
{

	var $objparams;

	function rex_xform()
	{
		global $REX;


		// $REX['ADDON']['xform']['classpaths']['value'][0]

		require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/'.'class.xform.value.abstract.inc.php');
		require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/'.'class.xform.action.abstract.inc.php');
		require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/'.'class.xform.validate.abstract.inc.php');

		$this->objparams = array();
		$this->objparams['object_path'] = $REX["INCLUDE_PATH"]."/addons/xform/classes/";
		$this->objparams['debug'] = FALSE;

		$this->objparams['form_data'] = "";

		$this->objparams["actions"] = array();

		$this->objparams["answertext"] = ""; // Antworttext
		$this->objparams["submit_btn_label"] = "Abschicken";
		$this->objparams["submit_btn_show"] = TRUE;
		$this->objparams["output"] = ""; // das was am ende ausgegeben wird

		$this->objparams["main_where"] = ""; // z.B. id=12
		$this->objparams["main_id"] = -1; // unique ID des Datensatzen: z.B. 12
		$this->objparams["main_table"] = ""; // für db speicherungen und unique abfragen

		$this->objparams["error_class"] = 'form_warning'; // CSS Klasse fuer die Fehlermedlungen.
		$this->objparams["WRAP_BLOCK_START"] = '<div class="element">';
		$this->objparams["WRAP_BLOCK_END"] = '</div>';
		$this->objparams["unique_error"] = "";
		$this->objparams["unique_field_warning"] = "not unique"; // Fehlermeldung die erscheint, wenn ein Unique-Feld von einem anderen User benutzt wird.
		$this->objparams["article_id"] = 0;
		$this->objparams["clang"] = 0;

		$this->objparams["form_method"] = "post";
		$this->objparams["form_action"] = "index.php";
		$this->objparams["form_anchor"] = "";
		$this->objparams["form_showformafterupdate"] = 0;
		$this->objparams["form_show"] = TRUE;
		$this->objparams["form_name"] = "formular";
		$this->objparams["form_id"] = "form_formular";
		$this->objparams["form_wrap"] = array('<div id="rex-xform" class="xform">','</div>');
		$this->objparams["form_hiddenfields"] = array();

		$this->objparams["Error-occured"] = "";
		$this->objparams["Error-Code-EntryNotFound"] = "ErrorCode - EntryNotFound";
		$this->objparams["Error-Code-InsertQueryError"] = "ErrorCode - InsertQueryError";
		$this->objparams["warning"] = array ();
		$this->objparams["warning_messages"] = array ();
			
		$this->objparams["first_fieldset"] = true; //
		$this->objparams["getdata"] = FALSE; // Daten vorab aus der DB holen

		$this->objparams["form_elements"] = array(); // Alle einzelnen Elemente

	}

	function setDebug($s = TRUE)
	{
		$this->objparams['debug'] = $s;
	}

	function setFormData($form_definitions,$refresh = TRUE)
	{
		$this->setObjectparams("form_data",$form_definitions,$refresh);

		$this->objparams["form_data"] = str_replace("\n\r", "\n" ,$this->objparams["form_data"]); // Die Definitionen
		$this->objparams["form_data"] = str_replace("\r", "\n" ,$this->objparams["form_data"]); // Die Definitionen

		if(!is_array($this->objparams["form_elements"]))
		{
			$this->objparams["form_elements"] = array();
		}

		$form_elements_tmp = array ();
		$form_elements_tmp = explode("\n", $this->objparams['form_data']); // Die Definitionen

		// leere Zeilen aus $this->objparams["form_elements"] entfernen
		foreach($form_elements_tmp as $form_element)
		{
			if(trim($form_element)!="")
			{
				$this->objparams["form_elements"][] = explode("|", trim($form_element));
			}
		}
	}

	function setValueField($type = "",$values = array())
	{
		$values = array_merge(array($type),$values);
		$this->objparams["form_elements"][] = $values;
	}

	function setValidateField($type = "",$values = array())
	{
		$values = array_merge(array("validate",$type),$values);
    $this->objparams["form_elements"][] = $values;
	}

	function setActionField($type = "",$values = array())
	{
    $values = array_merge(array("action",$type),$values);
    $this->objparams["form_elements"][] = $values;
	}

	function setRedaxoVars($aid = "",$clang = "",$params = array())
	{
		global $REX;

		if ($clang == "")
		{
			$clang = $REX["CUR_CLANG"];
		}
		if ($aid == "")
		{
			$aid = $REX["ARTICLE_ID"];
		}

		// deprecated
		$this->setObjectparams("article_id",$aid);
		$this->setObjectparams("clang",$clang);

		$this->setHiddenField("article_id",$aid);
		$this->setHiddenField("clang",$clang);

		$this->setObjectparams("form_action", rex_getUrl($aid, $clang, $params));
	}

	function setHiddenField($k,$v)
	{
		$this->objparams["form_hiddenfields"][$k] = $v;
	}

	function setGetdata($s = true)
	{
		$this->setObjectparams("getdata",$s);
	}

	function setObjectparams($k,$v,$refresh = TRUE)
	{
		if (!$refresh && isset($this->objparams[$k]))
		{
			$this->objparams[$k] .= $v;
		}else
		{
			$this->objparams[$k] = $v;
		}

		if($k != "form_name")
		{
			return;
		}

		if (isset($_REQUEST["FORM"][$this->objparams["form_name"]][$this->objparams["form_name"] . "send"]))
		{
			$this->objparams["send"] = $_REQUEST["FORM"][$this->objparams["form_name"]][$this->objparams["form_name"] . "send"];
		}		else
		{
			$this->objparams["send"] = 0;
		}

	}

	function getObjectparams($k)
	{
		echo $this->objparams[$k];
	}



	function getForm()
	{

		global $REX;

		$preg_user_vorhanden = "~\*|:|\(.*\)~Usim"; // Preg der Bestimmte Zeichen/Zeichenketten aus der Bezeichnung entfernt
		$form_output = array ();

		$sql_elements = array(); // diese Werte werden beim DB Satz verwendet /update oder insert
		$email_elements = array(); // hier werden Werte gesetzt die beim Mailversand ersetzt werden. z.B. passwort etc.

		$obj = array();


		// *************************************************** ABGESCHICKT PARAMENTER
		if (isset($_REQUEST["FORM"][$this->objparams["form_name"]][$this->objparams["form_name"] . "send"]))
		{
			$this->objparams["send"] = $_REQUEST["FORM"][$this->objparams["form_name"]][$this->objparams["form_name"] . "send"];
		}else
		{
			$this->objparams["send"] = 0;
		}





		// *************************************************** VALUE OBJEKTE
		$rows = count($this->objparams["form_elements"]);
		for ($i = 0; $i < $rows; $i++)
		{
			$element = $this->objparams["form_elements"][$i];
			if($element[0] != "validate" && $element[0] != "action")
			{
				foreach($REX['ADDON']['xform']['classpaths']['value'] as $value_path)
				{
					$classname = "rex_xform_".trim($element[0]);
					if (@include_once ($value_path.'class.xform.'.trim($element[0]).'.inc.php'))
					{
						$obj[$i] = new $classname;
						$obj[$i]->loadParams($this->objparams,$element,$obj,$email_elements,$sql_elements);
						$obj[$i]->setId($i);
						$obj[$i]->init();

						if (isset($_REQUEST["FORM"][$this->objparams["form_name"]]["el_" . $i]))
						{
							$obj[$i]->setValue($_REQUEST["FORM"][$this->objparams["form_name"]]["el_" . $i]);
						}else
						{
							$obj[$i]->setValue("");
						}

						$obj[$i]->setObjects($obj);

						// muss hier gesetzt sein, damit ein value objekt die elemente erweitern kann
						$rows = count($this->objparams["form_elements"]);

						break;

					}
				}
			}
		}



		// ----- PRE VALUES
		// Felder aus Datenbank auslesen - Sofern Aktualisierung
		$SQLOBJ = rex_sql::factory();
		if ((!isset($this->objparams['form_type']) || $this->objparams['form_type'] != "3") && $this->objparams['getdata'])
		{
			$xsSelect = "SELECT * from ".$this->objparams["main_table"]. " WHERE ".$this->objparams["main_where"];
			$SQLOBJ->debugsql = $this->objparams['debug'];
			$SQLOBJ->setQuery($xsSelect);
			if ($SQLOBJ->getRows() > 1 || $SQLOBJ->getRows() == 0)
			{
				$this->objparams["warning_messages"][] = $this->objparams["Error-Code-EntryNotFound"];
				$this->objparams["form_show"] = TRUE;
			}
		}

		// ----- Felder mit Werten fuellen, fuer wiederanzeige
		// Die Value Objekte werden mit den Werten befuellt die
		// aus dem Formular nach dem Abschicken kommen
		if (!($this->objparams["send"] == 1) && $this->objparams["main_where"] != "" && (!isset($this->objparams['form_type']) || $this->objparams['form_type'] != "3"))
		{
			for ($i = 0; $i < count($this->objparams["form_elements"]); $i++)
			{
				$element = $this->objparams["form_elements"][$i];
				if (($element[0]!="validate" && $element[0]!="action") and $element[1] != "")
				{
					$_REQUEST["FORM"][$this->objparams["form_name"]]["el_" . $i] = @addslashes($SQLOBJ->getValue($element[1]));
				}
				if($element[0]!="validate" && $element[0]!="action")
				{
					$obj[$i]->setValue($_REQUEST["FORM"][$this->objparams["form_name"]]["el_" . $i]);
				}
			}
		}




		// *************************************************** VALIDATE OBJEKTE

		// PreValidateActions
		foreach($obj as $value_object)
		{
			$value_object->preValidateAction();
		}

		for ($i = 0; $i < count($this->objparams["form_elements"]); $i++)
		{
			$element = $this->objparams["form_elements"][$i];
			if($element[0] == "validate")
			{
				foreach($REX['ADDON']['xform']['classpaths']['validate'] as $validate_path)
				{
					$classname = "rex_xform_validate_".trim($element[1]);
					if (@include_once ($validate_path.'class.xform.validate_'.trim($element[1]).'.inc.php'))
					{
						$count = 0;
						if (isset($valObj[$element[1]])) $count = count($valObj[$element[1]]);
						$valObj[$element[1]][$count] = new $classname;
						$valObj[$element[1]][$count]->loadParams($this->objparams, $element);
						$valObj[$element[1]][$count]->setObjects($obj);
						break;
					}
				}
			}
		}

		if ($this->objparams["send"] == 1)
		{
			if (isset($valObj) && count($valObj)>0)
			{
				foreach($valObj as $vObj)
				{
					foreach($vObj as $xoObject)
					$xoObject->enterObject($this->objparams["warning"], 1, $this->objparams["warning_messages"]);
				}
			}
		}

		// PostValidateActions
		foreach($obj as $value_object)
		{
			$value_object->postValidateAction();
		}





		// *************************************************** FORMULAR ERSTELLEN

		for ($i = 0; $i < count($this->objparams["form_elements"]); $i++)
		{
			$element = $this->objparams["form_elements"][$i];
			for($t = 0; $t < count($element); $t++)
			{
				$element[$t] = trim($element[$t]);
			}

			if($element[0]!="validate" && $element[0]!="action")
			{
				$obj[$i]->enterObject( $email_elements, $sql_elements, $this->objparams["warning"], $form_output, $this->objparams["send"], $SQLOBJ, $this->objparams );
			}
		}

		// PostFormActions
		foreach($obj as $value_object)
		{
			$value_object->postFormAction();
		}





		// *************************************************** ACTION OBJEKTE

		// ID setzen, falls vorhanden
		if($this->objparams["main_id"]>0)
		$email_elements["ID"] = $this->objparams["main_id"];

		// Action Felder auslesen und Validate Objekte erzeugen
		if (isset($this->objparams['form_type']))
		{
			if ($this->objparams['form_type'] == "0" || $this->objparams['form_type'] == "2")
			{
				$this->objparams["actions"][] = array(
					"type" => "db",
					"elements" => array(
						"action", 
						"db", 
				$this->objparams["main_table"], // Db Name
				$this->objparams["main_where"], // Where
				),
				);
			}
		}

		for ($i = 0; $i < count($this->objparams["form_elements"]); $i++)
		{
			$element = $this->objparams["form_elements"][$i];
			if($element[0]=="action")
			{
				$this->objparams["actions"][] = array(
					"type" => trim($element[1]),
					"elements" => $element,
				);
			}
		}

		if (isset($this->objparams['form_type']))
		{
			if ($this->objparams['form_type'] == "1" || $this->objparams['form_type'] == "2")
			{
				$this->objparams["actions"][] = array(
					"type" => "email",
					"elements" => array(
						"action",
						"email",
				$this->objparams["mail_from"],
				$this->objparams["mail_to"],
				$this->objparams["mail_subject"],
				$this->objparams["mail_body"],
				),
				);
			}
			if ($this->objparams["answertext"]!="")
			{
				$this->objparams["actions"][] = array(
					"type" => "showtext",
					"elements" => array(
						"action",
						"email",
				$this->objparams["answertext"],
						'<p class="answertext">',
						"</p>",
				),
				);
			}
		}

		// echo count($this->objparams["warning"]);

		$hasWarnings = count($this->objparams["warning"]) != 0;
		$hasWarningMessages = count($this->objparams["warning_messages"]) != 0;

		// ----- Actionen ausführen
		if ($this->objparams["send"] == 1 && !$hasWarnings && !$hasWarningMessages)
		{
			$this->objparams["form_show"] = FALSE;
			$i=-1;
			if (count($this->objparams["actions"]))
			{
				foreach($this->objparams["actions"] as $action)
				{
					$i++;
					foreach($REX['ADDON']['xform']['classpaths']['action'] as $action_path)
					{
						$type = 'action_'.$action["type"];
						if (@include_once ($action_path.'class.xform.'.$type.'.inc.php'))
						{
							$classname = 'rex_xform_'.$type;
							$actions[$i] = new $classname;
							$actions[$i]->loadParams($this->objparams,$action,$email_elements,$sql_elements,$this->objparams["warning"],$this->objparams["warning_messages"]);
							$actions[$i]->setObjects($obj);
						}
					}
				}
				foreach($actions as $action)
				{
					$action->execute();
				}
			}

			// PostActions
			foreach($obj as $value_object)
			{
				$value_object->postAction($email_elements, $sql_elements);
			}


		}






		$hasWarnings = count($this->objparams["warning"]) != 0;
		$hasWarningMessages = count($this->objparams["warning_messages"]) != 0;







		// ----- FORMULAR AUSGEBEN
		//
		if($this->objparams["form_show"] || $this->objparams["form_showformafterupdate"])
		{

			// ----- Formular wieder anzeigen

			$this->objparams["output"] .= $this->objparams["form_wrap"][0].'<form action="'.$this->objparams["form_action"];
			if($this->objparams["form_anchor"] != "")
			$this->objparams["output"] .= '#'.$this->objparams["form_anchor"];
			$this->objparams["output"] .= '" method="'.$this->objparams["form_method"].'" id="' . $this->objparams["form_id"] . '" enctype="multipart/form-data">';

			$this->objparams["output"] .= '<p style="display:none;">';

			// deprecated
			if($this->objparams["article_id"]>0)
			$this->objparams["output"] .= '<input type="hidden" name="article_id" value="'.htmlspecialchars($this->objparams["article_id"]).'" />';
			if($this->objparams["clang"]>0)
			$this->objparams["output"] .= '<input type="hidden" name="clang" value="'.htmlspecialchars($this->objparams["clang"]).'" />';

			$this->objparams["output"] .= '<input type="hidden" name="FORM[' . $this->objparams["form_name"] . '][' . $this->objparams["form_name"] . 'send]" value="1" />';
			foreach($this->objparams["form_hiddenfields"] as $k => $v)
			$this->objparams["output"] .= '<input type="hidden" name="'.$k.'" value="'.htmlspecialchars($v).'" />';
			$this->objparams["output"] .= '</p>';

			$hasWarningMessages = count($this->objparams["warning_messages"]) != 0;
			if ($this->objparams["unique_error"] != '' || $hasWarnings || $hasWarningMessages)
			{
				$warningListOut = '';
				if($hasWarningMessages)
				{					
					foreach($this->objparams["warning_messages"] as $k => $v)
					{
						$warningListOut .= '<li>'. $v .'</li>';
					}
				}
				if($this->objparams["unique_error"] != '')
				{
					$warningListOut .= '<li>'. preg_replace($preg_user_vorhanden, "", $this->objparams["unique_error"]) .'</li>';
				}
				
				if ($warningListOut != '')
				{
					if ($this->objparams["Error-occured"] != "")
					{
						$this->objparams["output"] .= '<dl class="' . $this->objparams["error_class"] . '">';
						$this->objparams["output"] .= '<dt>'. $this->objparams["Error-occured"] .'</dt>';
						$this->objparams["output"] .= '<dd><ul>'. $warningListOut .'</ul></dd>';
						$this->objparams["output"] .= '</dl>';
					}
					else
					{
						$this->objparams["output"] .= '<ul class="' . $this->objparams["error_class"] . '">'. $warningListOut .'</ul>';
					}
				}
			}

			foreach ($form_output as $v)
			$this->objparams["output"] .= $v;

			if ($this->objparams["submit_btn_show"])
			{
				$this->objparams["output"] .= '
					<p class="formsubmit">
						<input type="submit" name="FORM['.$this->objparams["form_name"].']['.$this->objparams["form_name"].'submit]" value="'.$this->objparams["submit_btn_label"].'" class="submit" />
					</p>';
			}

			if(!$this->objparams["first_fieldset"])
			$this->objparams["output"] .= '</fieldset>';

			$this->objparams["output"] .= '</form>
			'.$this->objparams["form_wrap"][1];

		}

		return $this->objparams["output"];
	}



























	// ----- Hilfsfunktionen -----

	function unhtmlentities($text)
	{
		if (!function_exists('unhtmlentities'))
		{
			function unhtmlentities($string)
			{
				// Ersetzen numerischer Darstellungen
				$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
				$string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
				// Ersetzen benannter Zeichen
				$trans_tbl = get_html_translation_table(HTML_ENTITIES);
				$trans_tbl = array_flip($trans_tbl);
				return strtr($string, $trans_tbl);
			}
		}
		return unhtmlentities($text);
	}


	function showHelp()
	{

		global $REX;

		?>

<ul class="xform">
  <li>Value - Typen
  <ul class="xform">
  <?php

  if (!class_exists('rex_xform_abstract'))
  require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/class.xform.value.abstract.inc.php');
   
  foreach($REX['ADDON']['xform']['classpaths']['value'] as $pos => $value_path)
  {
  	if ($pos==1) echo '<li><b>Extras</b><ul>';
  	if($Verzeichniszeiger = opendir($value_path))
  	{
  		while($Datei = readdir($Verzeichniszeiger))
  		{
  			if (preg_match("/^(class.xform)/", $Datei) && !preg_match("/^(class.xform.validate|class.xform.abstract)/", $Datei))
  			{
  				if(!is_dir($Datei))
  				{
  					$classname = (explode(".", substr($Datei, 12)));
  					$classname = "rex_xform_".$classname[0];
  					if (file_exists($value_path.$Datei))
  					{
  						include_once($value_path.$Datei);
  						$class = new $classname;
  						$desc = $class->getDescription();
  						if($desc != "")
  						echo '<li>'.$desc.'</li>';
  					}
  				}
  			}
  		}
  		closedir($Verzeichniszeiger);
  	}
  }
  if ($pos>0) echo '</ul></li>';
  ?></ul>
  </li>
  <li>Validate - Typen
  <ul class="xform">
  <?php

  if (!class_exists('rex_xform_validate_abstract'))
  require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/class.xform.validate.abstract.inc.php');

  foreach($REX['ADDON']['xform']['classpaths']['validate'] as $pos => $validate_path)
  {
  	if ($pos==1) echo '<li><b>Extras</b><ul>';
  	if($Verzeichniszeiger = opendir($validate_path))
  	{
  		while($Datei = readdir($Verzeichniszeiger))
  		{
  			if (preg_match("/^(class.xform.validate)/", $Datei) && !preg_match("/^(class.xform.validate.abstract)/", $Datei))
  			{
  				if(!is_dir($Datei))
  				{
  					$classname = (explode(".", substr($Datei, 12)));
  					$classname = "rex_xform_".$classname[0];
  					if (file_exists($validate_path.$Datei))
  					{
  						include_once($validate_path.$Datei);
  						$class = new $classname;
  						$desc = $class->getDescription();
  						if($desc != "")
  						echo '<li>'.$desc.'</li>';
  					}
  				}
  			}
  		}
  		closedir($Verzeichniszeiger);
  	}
  }
  if ($pos>0) echo '</ul></li>';
   
  ?></ul>
  </li>

  <li>Action - Typen
  <ul class="xform">
  <?php
   
  if (!class_exists('rex_xform_action_abstract'))
  require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/class.xform.action.abstract.inc.php');

  foreach($REX['ADDON']['xform']['classpaths']['action'] as $pos => $action_path)
  {
  	if ($pos==1) echo '<li><b>Extras</b><ul>';
  	if($Verzeichniszeiger = opendir($action_path))
  	{
  		while($Datei = readdir($Verzeichniszeiger))
  		{
  			if (preg_match("/^(class.xform.action)/", $Datei) && !preg_match("/^(class.xform.action.abstract)/", $Datei))
  			{
  				if(!is_dir($Datei))
  				{
  					$classname = (explode(".", substr($Datei, 12)));
  					$classname = "rex_xform_".$classname[0];
  					if (file_exists($action_path.$Datei))
  					{
  						include_once($action_path.$Datei);
  						$class = new $classname;
  						$desc = $class->getDescription();
  						if($desc != "")
  						echo '<li>'.$desc.'</li>';
  					}
  				}
  			}
  		}
  		closedir($Verzeichniszeiger);
  	}
  }
  if ($pos>0) echo '</ul></li>';
   
  ?></ul>
  </li>
</ul>
  <?php

	}


	function getTypeArray()
	{

		global $REX;

		$return = array();

		// Value

		if (!class_exists('rex_xform_abstract'))
		require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/class.xform.value.abstract.inc.php');
			
		foreach($REX['ADDON']['xform']['classpaths']['value'] as $pos => $value_path)
		{
			if($Verzeichniszeiger = opendir($value_path))
			{
				while($Datei = readdir($Verzeichniszeiger))
				{
					if (preg_match("/^(class.xform)/", $Datei) && !preg_match("/^(class.xform.validate|class.xform.abstract)/", $Datei))
					{
						if(!is_dir($Datei))
						{
							$classname = (explode(".", substr($Datei, 12)));
							$name = $classname[0];
							$classname = "rex_xform_".$name;
							if (file_exists($value_path.$Datei))
							{
								include_once($value_path.$Datei);
								$class = new $classname;
								$d = $class->getDefinitions();
								if(count($d)>0)
								$return['value'][$d['name']] = $d;
							}
						}
					}
				}
				closedir($Verzeichniszeiger);
			}
		}


		// Validate

		if (!class_exists('rex_xform_validate_abstract'))
		require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/class.xform.validate.abstract.inc.php');

		foreach($REX['ADDON']['xform']['classpaths']['validate'] as $pos => $validate_path)
		{
			if ($pos==1) echo '<li><b>Extras</b><ul>';
			if($Verzeichniszeiger = opendir($validate_path))
			{
				while($Datei = readdir($Verzeichniszeiger))
				{
					if (preg_match("/^(class.xform.validate)/", $Datei) && !preg_match("/^(class.xform.validate.abstract)/", $Datei))
					{
						if(!is_dir($Datei))
						{
							$classname = (explode(".", substr($Datei, 12)));
							$name = $classname[0];
							$classname = "rex_xform_".$name;
							if (file_exists($validate_path.$Datei))
							{
								include_once($validate_path.$Datei);
								$class = new $classname;
								$d = $class->getDefinitions();
								if(count($d)>0)
								$return['validate'][$d['name']] = $d;
							}
						}
					}
				}
				closedir($Verzeichniszeiger);
			}
		}


		// Action

		if (!class_exists('rex_xform_action_abstract'))
		require_once($REX['INCLUDE_PATH'].'/addons/xform/classes/basic/class.xform.action.abstract.inc.php');

		foreach($REX['ADDON']['xform']['classpaths']['action'] as $pos => $action_path)
		{
			if ($pos==1) echo '<li><b>Extras</b><ul>';
			if($Verzeichniszeiger = opendir($action_path))
			{
				while($Datei = readdir($Verzeichniszeiger))
				{
					if (preg_match("/^(class.xform.action)/", $Datei) && !preg_match("/^(class.xform.action.abstract)/", $Datei))
					{
						if(!is_dir($Datei))
						{
							$classname = (explode(".", substr($Datei, 12)));
							$name = $classname[0];
							$classname = "rex_xform_".$name;
							if (file_exists($action_path.$Datei))
							{
								include_once($action_path.$Datei);
								$class = new $classname;
								$d = $class->getDefinitions();
								if(count($d)>0)
								$return['action'][$d['name']] = $d;
							}
						}
					}
				}
				closedir($Verzeichniszeiger);
			}
		}

		return $return;

	}



}