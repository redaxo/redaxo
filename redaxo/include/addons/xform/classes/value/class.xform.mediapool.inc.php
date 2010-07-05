<?php

// TODO:
// Type checken: jpg etc.

class rex_xform_mediapool extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{		

		if ($send)
		{
			if ($_FILES["FORM"]["name"][$this->params["form_name"]]["el_".$this->id] != "")
			{
	
				$FILE["name"] = $_FILES["FORM"]["name"][$this->params["form_name"]]["el_".$this->id];
				$FILE["type"] = $_FILES["FORM"]["type"][$this->params["form_name"]]["el_".$this->id];
				$FILE["tmp_name"] = $_FILES["FORM"]["tmp_name"][$this->params["form_name"]]["el_".$this->id];
				$FILE["error"] = $_FILES["FORM"]["error"][$this->params["form_name"]]["el_".$this->id];
				$FILE["size"] = $_FILES["FORM"]["size"][$this->params["form_name"]]["el_".$this->id];

				$rex_file_category = (int) $this->elements[3];
				$NEWFILE = $this->saveMedia($FILE,$rex_file_category,array("title"=>""));
				
				if ($NEWFILE["ok"]==1)
				{
					$this->value = $NEWFILE['filename'];
				}else
				{
					$this->value = "";
				}
			}
		}

		if ($send)
		{
			if ($this->value == "" && 
				$_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id.'_filename'] != "" && 
				(!isset($_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id.'_delete']) || $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id.'_delete'] != 1)
				)
			{
				$this->value = $_REQUEST["FORM"][$this->params["form_name"]]['el_'.$this->id.'_filename'];
			}

			$email_elements[$this->elements[1]] = stripslashes($this->value);
			$sql_elements[$this->elements[1]] = $this->value;
		}

		$pic = "";
		$check_delete = "";
		if ($this->value != "")
   		{
   			$resize = (int) $this->elements[4];
   			if ($resize<0) $resize = 100;
			$pic = '<img src="index.php?rex_resize='.$resize.'a__'.$this->value.'" />';

			$check_delete = '
   			<p class="formmcheckbox">
	   			<input id="el_'.$this->id.'_delete" type="checkbox" name="FORM['.$this->params["form_name"].'][el_'.$this->id.'_delete]" value="1" />
	   			<label for="el_' . $this->id . '_delete">Bild löschen</label>
   			</p>
   			';
   		}

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) 
		  $wc = $warning["el_" . $this->getId()];

    $out = '
			<input type="hidden" name="FORM['.$this->params["form_name"].'][el_'.$this->id.'_filename]" value="'.$this->value.'" />

			<p class="formmediapool">
				<label class="text ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[2] . $pic . '</label>
				<input class="uploadbox clickmedia '.$wc.'" id="el_'.$this->id.'" name="FORM['.$this->params["form_name"].'][el_'.$this->id.']" type="file" />
			</p>'.$check_delete;
			
		$form_output[] = $out;

	}
	
	function getDescription()
	{
		return "mediapool -> Beispiel: mediapool|label|Bezeichnung|kategorieid|100|jpg,gif,png";
	}
	
	
	function getDefinitions()
	{
		/*
		return array(
						'type' => 'value',
						'name' => 'mediapool',
						'values' => array(
             	array( 'type' => 'label',   'name' => 'Label' ),
              array( 'type' => 'text',    'name' => 'Bezeichnung'),
              array( 'type' => 'text', 'name' => 'Medienpoolkategorie ID'),
              array( 'type' => 'text',    'name' => 'Maximale Grš§e in Kb'),
              array( 'type' => 'text',    'name' => 'Welche Dateien sollen erlaubt sein, kommaseparierte Liste. ".gif,.png"'),
						),
						'description' => 'Datei kann hochgeladen werden und wird dann in den Medienpool gelegt',
						'dbtype' => 'text'
			);
		*/
	}
	
	
	
	
	
	
	
	
	
	function saveMedia($FILE,$rex_file_category,$FILEINFOS){

	  global $REX;
	
	  $FILENAME = $FILE['name'];
	  $FILESIZE = $FILE['size'];
	  $FILETYPE = $FILE['type'];
	  $NFILENAME = "";
	  $message = '';
	  
	  // ----- neuer filename und extension holen
	  $NFILENAME = strtolower(preg_replace("/[^a-zA-Z0-9.\-\$\+]/","_",$FILENAME));
	  if (strrpos($NFILENAME,".") != "")
	  {
	    $NFILE_NAME = substr($NFILENAME,0,strlen($NFILENAME)-(strlen($NFILENAME)-strrpos($NFILENAME,".")));
	    $NFILE_EXT  = substr($NFILENAME,strrpos($NFILENAME,"."),strlen($NFILENAME)-strrpos($NFILENAME,"."));
	  }else
	  {
	    $NFILE_NAME = $NFILENAME;
	    $NFILE_EXT  = "";
	  }
	
	  // ---- ext checken
	  $ERROR_EXT = array(".php",".php3",".php4",".php5",".phtml",".pl",".asp",".aspx",".cfm");
	  if (in_array($NFILE_EXT,$ERROR_EXT))
	  {
	    $NFILE_NAME .= $NFILE_EXT;
	    $NFILE_EXT = ".txt";
	  }

	  $picext = array(".jpg",".gif",".jpeg",".png");
	  if (!in_array($NFILE_EXT,$picext))
	  {
	    $RETURN = FALSE;
	    $RETURN['ok'] = FALSE;
	  	return $RETURN;

	  }
	
	
	
	  $NFILENAME = $NFILE_NAME.$NFILE_EXT;
	
	  // ----- datei schon vorhanden -> namen aendern -> _1 ..
	  if (file_exists($REX['MEDIAFOLDER']."/$NFILENAME"))
	  {
	    for ($cf=1;$cf<1000;$cf++)
	    {
	      $NFILENAME = $NFILE_NAME."_$cf"."$NFILE_EXT";
	      if (!file_exists($REX['MEDIAFOLDER']."/$NFILENAME")) break;
	    }
	  }
	
	  // ----- dateiupload
	  $upload = true;
	  if(!@move_uploaded_file($FILE['tmp_name'],$REX['MEDIAFOLDER']."/$NFILENAME") )
	  {
	    if (!@copy($FILE['tmp_name'],$REX['MEDIAFOLDER']."/$NFILENAME"))
	    {
	      $message .= "move file $FILENAME failed | ";
	      $ok = 0;
	      $upload = false;
	    }
	  }
	
	  if($upload)
	  {
	
	    @chmod($REX['MEDIAFOLDER']."/$NFILENAME", $REX['FILEPERM']);
	
	    // get widht height
	    $size = @getimagesize($REX['MEDIAFOLDER']."/$NFILENAME");
	
	    $FILESQL = rex_sql::factory();
	    // $FILESQL->debugsql=1;
	    $FILESQL->setTable($REX['TABLE_PREFIX']."file");
	    $FILESQL->setValue("filetype",$FILETYPE);
	    $FILESQL->setValue("title",$FILEINFOS['title']);
	    $FILESQL->setValue("filename",$NFILENAME);
	    $FILESQL->setValue("originalname",$FILENAME);
	    $FILESQL->setValue("filesize",$FILESIZE);
	    $FILESQL->setValue("width",$size[0]);
	    $FILESQL->setValue("height",$size[1]);
	    $FILESQL->setValue("category_id",$rex_file_category);
	    $FILESQL->setValue("createdate",time());
	    $FILESQL->setValue("createuser","system");
	    $FILESQL->setValue("updatedate",time());
	    $FILESQL->setValue("updateuser","system");
	    $FILESQL->insert();
	    $ok = 1;
	  }
	
	  $RETURN['title'] = $FILEINFOS['title'];
	  $RETURN['width'] = $size[0];
	  $RETURN['height'] = $size[1];
	  $RETURN['type'] = $FILETYPE;
	  $RETURN['msg'] = $message;
	  $RETURN['ok'] = $ok;
	  $RETURN['filename'] = $NFILENAME;
	
	  return $RETURN;
	}
	
	
}

?>