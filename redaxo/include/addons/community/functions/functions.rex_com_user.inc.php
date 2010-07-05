<?php


class rex_com_user
{

	var $exist = FALSE;
	var $sql;
	var $contacts = -1;
	var $user_id = -1;

	function rex_com_user ($user_id)
	{
		global $REX;
		
		$user_id = (int) $user_id;
		
		if ($user_id == 0) return FALSE;
		
		$this->sql = new rex_sql;
		$this->sql->setQuery('select * from rex_com_user where id='.$user_id.' LIMIT 2');

		if ($this->sql->getRows() != 1) return FALSE;
		
		$this->exist = TRUE;
		$this->user_id = $this->getValue("id");
		
		return TRUE;
	}

	function getValue($val)
	{
		if (is_object($this->sql))
		{
			return $this->sql->getValue($val);
		}
	}

	function exists()
	{
		return $this->exist;
	}

	function getContactsAsArray()
	{
		if (is_array($this->contacts)) return $this->contacts;
		$this->contacts = array();
		$gc = new rex_sql;
		// $gc->debugsql = 1;
		$gc->setQuery('select * from rex_com_contact where user_id='.$this->user_id.' and accepted=1');
		$res = $gc->getArray();
		foreach($res as $con)
		{
			$this->contacts[] = $con["to_user_id"];
		}
		return $this->contacts;
	}

	// *********************************** STATIC

	// Aktion werden hier ausgeführt.
	// Bisher sind es Aktionen zur E-Mail Benachirhctung
	// * sendemail_contactrequest
	// * sendemail_newmessage
	// * sendemail_guestbook

	function exeAction($user_id = 0,$action = "", $searchandreplace = array())
	{
		$gt = new rex_sql;
		// $gt->debugsql = true;
		$gt->setQuery('select * from rex_xform_email_template where name="'.$action.'"');
		if ($gt->getRows()==1)
		{
			$gu = new rex_sql;
			// $gu->debugsql = true;
			$gu->setQuery('select * from rex_com_user where id="'.$user_id.'" and '.$action.'=1');
			if ($gu->getRows()==1)
			{
				// echo "<p>Aktion ausgeführt: ID:$user_id Aktion:$action</p>";
				$mail_from = $gt->getValue("mail_from");
				$mail_from_name = $gt->getValue("mail_from_name");
				$mail_to = $gu->getValue("email");
				$mail_subject = $gt->getValue("subject");
				$mail_body = $gt->getValue("body");
				foreach ($searchandreplace as $search => $replace)
				{
					$mail_from = str_replace('###'. $search .'###', $replace, $mail_from);
					$mail_from_name = str_replace('###'. $search .'###', $replace, $mail_from_name);
					$mail_to = str_replace('###'. $search .'###', $replace, $mail_to);
					$mail_subject = str_replace('###'. $search .'###', $replace, $mail_subject);
					$mail_body = str_replace('###'. $search .'###', $replace, $mail_body);
				}

				$mail = new PHPMailer();
				$mail->AddAddress($mail_to, $mail_to);
				$mail->WordWrap = 80;
				$mail->FromName = $mail_from_name;
				$mail->From = $mail_from;
				$mail->Subject = $mail_subject;
				$mail->Body = nl2br($mail_body);
				$mail->AltBody = strip_tags($mail_body);
				// $mail->IsHTML(true);
				if ($mail->Send()) echo ""; // ok
				else echo ""; // nicht ok
			}
			return FALSE;
		}
		return FALSE;
	}

	
	function createObject($user_id)
	{
		global $REX;

		if (!isset($REX["COM_CACHE"]["USER"][$user_id]) || !is_object($REX["COM_CACHE"]["USER"][$user_id]))
		{
			$REX["COM_CACHE"]["USER"][$user_id] = new rex_com_user($user_id);
			if (!$REX["COM_CACHE"]["USER"][$user_id]->exists()) return FALSE;
		}
		return TRUE;
	}
	
	
	function getGuestbook($user_id, $aid, $params = array())
	{
	
		global $REX;
	
		$MY = FALSE;
		if (is_object($REX['COM_USER']) && $REX['COM_USER']->getValue("rex_com_user.id") == $user_id) $MY = TRUE;

		$u = new rex_sql;
		$u->setQuery("select * from rex_com_user where id=".$user_id);
		if ($u->getRows()!=1) return "";

	
		// ***** ADD MESSAGE
		if(is_object($REX['COM_USER']) && $_REQUEST["add_message"] != "")
		{
			$text = $_REQUEST["text"];
			if($text == ""){
				$errormessage = '<p class="warning" colspan=2>Es wurde keine Nachricht eingetragen !</p>';
			}else
			{ 
				$addmsgsql = new rex_sql();
				$addmsgsql->setTable("rex_com_guestbook");
				$addmsgsql->setValue("user_id", $user_id);
				$addmsgsql->setValue("from_user_id", $REX['COM_USER']->getValue("id"));
				$addmsgsql->setValue("text", $text);
				$addmsgsql->setValue("create_datetime", time());
				$addmsgsql->insert();
				
				if ($user_id != $REX['COM_USER']->getValue('rex_com_user.id'))
				{
					rex_com_user::exeAction($user_id,"sendemail_guestbook", 
						array(
							"user_id" => $REX['COM_USER']->getValue('rex_com_user.id'),
							"firstname" => $REX['COM_USER']->getValue('rex_com_user.firstname'),
							"name" => $REX['COM_USER']->getValue('rex_com_user.name'),
							"login" => $REX['COM_USER']->getValue('rex_com_user.login'),
							"to_user_id" => $u->getValue('rex_com_user.id'),
							"to_firstname" => $u->getValue('rex_com_user.firstname'),
							"to_name" => $u->getValue('rex_com_user.name'),
							"to_login" => $u->getValue('rex_com_user.login'),
						)
					);
				}
				
			}
		}elseif($MY && $_REQUEST["delete_message"] != "")
		{
			$msg_id = (int) $_REQUEST["msg_id"];
			if($msg_id == 0){
				$errormessage = '<p class="warning">Es wurde keine Nachricht ausgewählt!</p>';
			}else
			{ 
				$addmsgsql = new rex_sql();
				// $addmsgsql->debugsql = 1;
				$addmsgsql->setQuery('delete from rex_com_guestbook where id='.$msg_id.' and user_id="'.$REX['COM_USER']->getValue("id").'"');
			}
		}
		
		
		
		// ***** SHOW MESSAGES
		$guestsql = new rex_sql();
		$guestsql->debugsql = 0;
		$guestsql->setQuery("SELECT * 
			FROM  rex_com_guestbook 
			LEFT JOIN rex_com_user ON rex_com_guestbook.from_user_id=rex_com_user.id 
			WHERE rex_com_guestbook.user_id='".$user_id."' 
			ORDER BY rex_com_guestbook.create_datetime desc");

		if($guestsql->getRows()<=0)
		{
			$echo .= '<p class="com-whitebox">Kein Gästebucheintrag vorhanden !</p>';
		}else
		{
			$cl = "";
			for($i=0;$i<$guestsql->getRows();$i++)
			{
			
				// $cl
				$echo .= '
				<div class="com-guestbook">
					<div class="com-image">
						<p class="image">'.rex_com_showUser($guestsql,"image").'</p>
					</div>

					<div class="com-content">
					<div class="com-content-2">
					
						<div class="com-content-name">
							<p><span class="color-1">'.rex_com_showUser($guestsql,"name").', '.rex_com_showUser($guestsql,"city","",FALSE).'</span>
								<br />'.rex_com_formatter($guestsql->getValue("rex_com_guestbook.create_datetime"),'datetime').'
							</p>
						</div>
						<p><b>'.nl2br(htmlspecialchars($guestsql->getValue("rex_com_guestbook.text"))).'</b></p>';

				if ($guestsql->getValue("rex_com_user.motto") != '')
					$echo .= '<p>Motto: '.$guestsql->getValue("rex_com_user.motto").'</p>';
			
				if ($MY)
				{
					$link_params = array_merge($params,array("user_id"=>$user_id,"delete_message"=>1,"msg_id"=>$guestsql->getValue("rex_com_guestbook.id")));
					$echo .= '<br /><p class="link-button"><a href="'.rex_getUrl($aid,'',$link_params).'"><span>Löschen</span></a></p>';
				}

				$echo .= '</div></div>
					<div class="clearer"> </div>
				</div>';
				
				if ($cl == "") $cl = ' class="alternative"';
				else $cl = "";
				$guestsql->next();
			}
		}
//		$echo .= '</tr></table>';
		
		if(is_object($REX['COM_USER']))
		{
		
			$echo .= '<div id="rex-form" class="com-guestbook-form spcl-bgcolor">
			
			<form action="'.$REX["FRONTEND_FILE"].'" method="post" id="guestbookform">
			
			<h2>Einen neuen Eintrag schreiben</h2>

			'.$errormessage.'
			
			<input type="hidden" name="add_message" value="1" />
			<input type="hidden" name="user_id" value="'.$user_id.'" />
			<input type="hidden" name="article_id" value="'.$aid.'" />
			';
			
			foreach($params as $k => $v)
			{
				$echo .= '<input type="hidden" name="'.$k.'" value="'.htmlspecialchars($v).'" />';
			}
			
			$echo .= '
				<p class="formtextarea">
					<label for="f-message">Nachricht:</label>
					<textarea id="f-message" name="text" cols="40" rows="4" /></textarea>
				</p>
				<p class="link-save">
					<a href="javascript:void(0);"  onclick="document.getElementById(\'guestbookform\').submit()"><span>Speichern</span></a></p>
				</p>
			<div class="clearer"> </div>
			
			</form>
			</div>';

		}
	
		return $echo;
	
	
	}
	
	

}

















function rex_com_showUserProfil($user_id,$aid = 0,$params = array())
{
	global $REX,$FORM;

	$u = new rex_sql;
	$u->setQuery("select * from rex_com_user where id=".$user_id);

	if ($u->getRows()==1)
	{
	
		$datetime = time();
	
		$show_basis = (int) $u->getValue("show_basisinfo");
		$show_kontakt = (int) $u->getValue("show_contactinfo");
		$show_personal = (int) $u->getValue("show_personalinfo");

		$ST = FALSE;
		$SK = FALSE;
		$SP = FALSE;
		
		// ***** Kontakt ?
		$KT = FALSE; // Kontakt nicht gestartet
		$KTA = FALSE; // Kontakt nicht bestaetigt
		$KTR = FALSE; // Kontakt nicht angefragt
		if (is_object($REX['COM_USER']))
		{
		
			$k = new rex_sql;
			$k->setQuery('select * from rex_com_contact where user_id="'.$user_id.'" and to_user_id="'.$REX['COM_USER']->getValue('rex_com_user.id').'"');
			if ($k->getRows()==1)
			{
			
				if ($_REQUEST["delete_contact"]==1)
				{
					$msg = "Kontakt wurde gelöscht.";
					$k->setQuery('delete from rex_com_contact where user_id="'.$user_id.'" and to_user_id="'.$REX['COM_USER']->getValue('rex_com_user.id').'"');
					$k->setQuery('delete from rex_com_contact where user_id="'.$REX['COM_USER']->getValue('rex_com_user.id').'" and to_user_id="'.$user_id.'"');
				}elseif ($_REQUEST["contact_accepted"]==1 && $k->getValue("accepted")==0)
				{
					$msg = "Kontakt wurde bestätigt.";
					$k->setQuery('update rex_com_contact set accepted=1 where user_id="'.$user_id.'" and to_user_id="'.$REX['COM_USER']->getValue('rex_com_user.id').'"');
					$k->setQuery('update rex_com_contact set accepted=1 where user_id="'.$REX['COM_USER']->getValue('rex_com_user.id').'" and to_user_id="'.$user_id.'"');
					$KT = TRUE;
					// $KTR = TRUE;
					// $KTA = TRUE;
					if ($show_basis == 1) $ST = TRUE;
					if ($show_kontakt == 1) $SK = TRUE;
					if ($show_personal == 1) $SP = TRUE;

				}else
				{
					$KT = TRUE;
					if ($k->getValue("requested")==1) $KTR = TRUE; // d.h. schwesterndatensatz ist request -> daten sind freigegeben
					if ($k->getValue("accepted")==1) $KTA = TRUE;
					if ($show_basis == 1 && ($KTR||$KTA)) $ST = TRUE;
					if ($show_kontakt == 1 && ($KTR||$KTA)) $SK = TRUE;
					if ($show_personal == 1 && ($KTR||$KTA)) $SP = TRUE;
				}
			}else
			{
				// Kontaktanfrage
				if ($_REQUEST["add_contact"]==1)
				{
					$msg = "Kontakt wurde angefragt.";
					$k = new rex_sql;
					$k->setTable("rex_com_contact");
					$k->setValue("user_id",$user_id);
					$k->setValue("to_user_id",$REX['COM_USER']->getValue('rex_com_user.id'));
					$k->setValue("create_datetime",$datetime);
					$k->insert();
					$k = new rex_sql;
					$k->setTable("rex_com_contact");
					$k->setValue("user_id",$REX['COM_USER']->getValue('rex_com_user.id'));
					$k->setValue("to_user_id",$user_id);
					$k->setValue("requested",1);
					$k->setValue("create_datetime",$datetime);
					$k->insert();
					
					if ($user_id != $REX['COM_USER']->getValue('rex_com_user.id'))
					{
						rex_com_user::exeAction($user_id,"sendemail_contactrequest", 
							array(
							"user_id" => $REX['COM_USER']->getValue('rex_com_user.id'),
							"firstname" => $REX['COM_USER']->getValue('rex_com_user.firstname'),
							"name" => $REX['COM_USER']->getValue('rex_com_user.name'),
							"login" => $REX['COM_USER']->getValue('rex_com_user.login'),
							"to_user_id" => $u->getValue('rex_com_user.id'),
							"to_firstname" => $u->getValue('rex_com_user.firstname'),
							"to_name" => $u->getValue('rex_com_user.name'),
							"to_login" => $u->getValue('rex_com_user.login'),
							)
						);
					}
					
					$KT = TRUE;
				}
			}
		}

		// ***** für alle sichtbar
		if ($show_basis == 0) $ST = FALSE;
		if ($show_kontakt == 0) $SK = FALSE;
		if ($show_personal == 0) $SP = FALSE;
		if ($show_basis == 2) $ST = TRUE;
		if ($show_kontakt == 2) $SK = TRUE;
		if ($show_personal == 2) $SP = TRUE;

		// ***** user hat eigenes profil aufgerufen
		$MY = FALSE;
		if (is_object($REX['COM_USER']) && $REX['COM_USER']->getValue("rex_com_user.id") == $user_id)
		{
			$ST = TRUE;
			$SK = TRUE;
			$SP = TRUE;
			$MY = TRUE;
		}
		





		// ***** MSG AUSAGBE
		if ($msg != "") echo '<p class="warning">'.$msg.'</p>';




		// ***** AUSGABE USERPROFIL BASIS

		echo '
		<div class="rex-com-profile">
			<div class="image">'.rex_com_showUser($u,"image_big","",FALSE).'</div>
			<div class="text">
			<h2 class="noclear">';
		
		if ($ST) echo rex_com_showUser($u,"login","",FALSE)." [ ".rex_com_showUser($u,"name","",FALSE)."]";
		else echo rex_com_showUser($u,"login","",FALSE);
		
		echo '</h2>
			<ul class="navi com-navi-myprofile">
			';
		
		
		$buttons = array();
		
		
		rex_register_extension_point('ADDON_COM_USER_BUTTONS_PRE', "", array("userobj" => &$u, "buttons" => &$buttons, "user_id" => $user_id));
		
		

		// Kontakt
		if (!$KT && !$MY)
		{
			$buttons[] = '<a href="'.rex_getUrl($aid,0,array("user_id"=>$user_id, "add_contact"=>1)).'"><span>Als Kontakt hinzufügen</span></a>';
		}

		if ($KT)
		{
			$buttons[] = '<a href="'.rex_getUrl($aid,0,array("user_id"=>$user_id,"delete_contact"=>1)).'"><span>Kontakt löschen</span></a>';
		}
		if (!$KTA && $KTR && $KT)
		{
			$buttons[] = '<a href="'.rex_getUrl($aid,0,array("user_id"=>$user_id,"contact_accepted"=>1)).'"><span>Kontakt bestätigen</span></a>';
		}

		// Eigene Daten bearbeiten
		if ($MY)
		{
			$buttons[] = '<a href="'.rex_getUrl(REX_COM_PAGE_MYPROFIL_ID,0,array("tab"=>0)).'"><span>Basisdaten bearbeiten</span></a>';
			$buttons[] = '<a href="'.rex_getUrl(REX_COM_PAGE_MYPROFIL_ID,0,array("tab"=>1)).'"><span>Kontaktdaten bearbeiten</span></a>';
			$buttons[] = '<a href="'.rex_getUrl(REX_COM_PAGE_MYPROFIL_ID,0,array("tab"=>2)).'"><span>Persönliches bearbeiten</span></a>';
			$buttons[] = '<a href="'.rex_getUrl(REX_COM_PAGE_MYPROFIL_ID,0,array("tab"=>3)).'"><span>Einstellungen</span></a>';
			$buttons[] = '<a href="'.rex_getUrl(REX_COM_PAGE_MYPROFIL_ID,0,array("tab"=>4)).'"><span>Passwort ändern</span></a>';
		}

		rex_register_extension_point('ADDON_COM_USER_BUTTONS_POST', "", array("userobj" => &$u, "buttons" => &$buttons, "user_id" => $user_id));

		foreach($buttons as $k => $button)
		{
			echo '<li class="no-'.$k.'">'.$button.'</li>';
		}




		echo '
			</ul>
			</div>
			<div class="clearer"> </div>
		</div>';



		// ***** AUSGABE USERPROFIL STAMMDATEN,PERSÖNLICHES; ... GÄSTEBUCH

		$tab_arr_in = array(
			'Basisdaten' => 'REX_LINK_ID[1]', 
			'Kontaktdaten' => 'REX_LINK_ID[2]', 
			'Persönliches' => 'REX_LINK_ID[3]', 
			'Gästebuch' => 'REX_LINK_ID[4]',
			);

		$tab_arr = array();
		foreach($tab_arr_in as $k => $v) if ($k != "" && $v != "") $tab_arr[$k] = $v;
	
		$tab_g = 0;
		if (isset($_REQUEST['tab']) AND $_REQUEST['tab'] != '') $tab_g = (int) $_REQUEST['tab'];	
		if ($tab_g < 0 || $tab_g >= count($tab_arr)) $tab_g = 0;
			
		$tab_cnt = '';
		$tab_list = '';
		$tab_c = 0; // Counter
		// $tab_g -> active tab
		foreach ($tab_arr as $key => $val)
		{
			$link = rex_getUrl('', '', array('tab' => $tab_c));
			$tab_class = '';
			if ($tab_g == $tab_c) {
				$tab_cnt = $val;
				$tab_class = 'active ';
			}
			if ($tab_c == 0) $tab_class = 'tab-frst ';
			if ($tab_c == 0 AND $tab_g == $tab_c) $tab_class = 'tab-frst-active ';
			if (($tab_c+1) == count($tab_arr)) $tab_class = 'tab-lst ';
			if (($tab_c+1) == count($tab_arr) AND $tab_g == $tab_c) $tab_class = 'tab-lst-active ';
			$tab_c_active_nxt = $tab_g - 1;
			if ($tab_c == $tab_c_active_nxt) $tab_class .= 'active-nxt ';
			trim($tab_class);
			if ($tab_class != '') $tab_class = ' class="'.$tab_class.'"';
			$tab_list .= '<li'.$tab_class.'><a href="'.rex_getUrl($aid, '', array('user_id'=>$user_id,'tab' => $tab_c)).'"><span>'.$key.'</span></a></li>';
			$tab_c++;
		}
			
		print '
			<div class="com-tab">
			<div class="com-tab2">
				<div class="com-tab-navi">
					<ul class="navi">
						'.$tab_list.'
					</ul>
				</div>
				
				<div class="com-tab-cntnt">
				<div class="com-tab-cntnt-2">
				<div class="com-tab-cntnt-3">';

		$current = "";
		function wechsel(&$current)
		{
			if ($current != 'class="alternative"') $current = 'class="alternative"';
			else $current = "";
			return $current;
		}


		switch($tab_g)
		{
			case("0"):
			
			
				// Stammdaten
				$G = array();
				$G["m"] = "Herr";$G["1"] = "Herr";
				$G["f"] = "Frau";$G["2"] = "Frau";
				$gender = $G[$u->getValue("gender")];

				if (!$ST)
				{
					echo "Daten sind nicht freigeschaltet";
				}else
				{
					echo '<table class="profiledata">';
					if ($u->getValue("gender")!="") echo '<tr '.wechsel($current).'><td class="label">Anrede: </td><td>'.$gender.'</td></tr>';
					if ($u->getValue("firstname")!="") echo '<tr '.wechsel($current).'><td class="label">Vorname: </td><td>'.htmlspecialchars($u->getValue("firstname")).'</td></tr>';
					if ($u->getValue("name")!="") echo '<tr '.wechsel($current).'><td class="label">Name: </td><td>'.htmlspecialchars($u->getValue("name")).'</td></tr>';
					if ($u->getValue("motto")!="") echo '<tr '.wechsel($current).'><td class="label">Motto: </td><td>'.htmlspecialchars($u->getValue("motto")).'</td></tr>';
					echo '</table>';				
				}
				
				break;


			case("1"):
				// Kontaktdaten
				
				if (!$SK)
				{
					echo "Daten sind nicht freigeschaltet";
				}else
				{
					$current = "";
					echo '<table class="profiledata">';

					if ($u->getValue("email")!="") echo '<tr '.wechsel($current).'><td class="label">E-Mail: </td><td>'.htmlspecialchars($u->getValue("email")).'</td></tr>';
					if ($u->getValue("street")!="") echo '<tr '.wechsel($current).'><td class="label">Straße: </td><td>'.htmlspecialchars($u->getValue("street")).'</td></tr>';
					if ($u->getValue("zip")!="") echo '<tr '.wechsel($current).'><td class="label">PLZ: </td><td>'.htmlspecialchars($u->getValue("zip")).'</td></tr>';
					if ($u->getValue("city")!="") echo '<tr '.wechsel($current).'><td class="label">Ort: </td><td>'.htmlspecialchars($u->getValue("city")).'</td></tr>';
					if ($u->getValue("phone")!="") echo '<tr '.wechsel($current).'><td class="label">Telefon: </td><td>'.htmlspecialchars($u->getValue("phone")).'</td></tr>';
					if ($u->getValue("fax")!="") echo '<tr '.wechsel($current).'><td class="label">Fax: </td><td>'.htmlspecialchars($u->getValue("fax")).'</td></tr>';
					if ($u->getValue("icq")!="") echo '<tr '.wechsel($current).'><td class="label">ICQ: </td><td>'.htmlspecialchars($u->getValue("icq")).'</td></tr>';
					
					echo '</table>';				
				}				
				break;


			case("2"):
				// Persönliches

				if (!$SP)
				{
					echo "Daten sind nicht freigeschaltet";
				}else
				{
					$FS = array();
				
					$current = "";
					echo '<table class="profiledata">';

					if ($u->getValue("birthday")!="0000-00-00") echo '<tr '.wechsel($current).'><td class="label">Geburtstag: </td><td>'.$u->getValue("birthday").'</td></tr>';

					if ($u->getValue("hobby") != "") echo '<tr '.wechsel($current).'><td class="label">Hobbies: </td><td>'.nl2br(htmlspecialchars($u->getValue("hobby"))).'</td></tr>';
					
					if ($u->getValue("interests") != "") echo '<tr '.wechsel($current).'><td class="label">Interessen: </td><td>'.nl2br(htmlspecialchars($u->getValue("interests"))).'</td></tr>';

					if ($u->getValue("more") != "") echo '<tr '.wechsel($current).'><td class="label">Mehr über mich: </td><td>'.nl2br(htmlspecialchars($u->getValue("more"))).'</td></tr>';
						
					// echo '<tr><td>Über mich</td><td>'.$u->getValue("description").'</td></tr>';
					echo '</table>';				
				}
				break;



			case("3"):
				// Gästebuch
				
				echo rex_com_user::getGuestbook($user_id,$aid,array("tab"=>$tab_g));
				
				break;		
		}
		
		print '
				<div class="clearer"> </div>
				</div>
				</div>
				</div>
			</div>
			</div>';

	}

}









function rex_com_showUser(&$sql, $style="mini", $table="", $linked = TRUE)
{

	global $REX;

	// $sql is rex_sql

	// Styles
	// Text - nur name
	// Mini - für Tabellen - mit Bild und Name
	// Maxi - Vollansicht mit allen Infos
	
	// Alle abhaengig von freischaltungen
	// * Allgemeines
	// * Adresse/Kontakt
	// * Persönliches/Interessen

	$img = "nobody.gif";
	if ($sql->getValue("$table.gender")=="2") $img = "nobody_w.gif";
	if ($sql->getValue("$table.gender")=="1") $img = "nobody_m.gif";
	if (@$sql->getValue("$table.image") != "") $img = $sql->getValue("$table.image");
	if (@$sql->getValue("$table.photo") != "") $img = $sql->getValue("$table.photo");

	$admin_class = "";
	if ($sql->getValue("$table.admin") == 1) $admin_class = " admin";

	// ***** MINI ist immer für alle sichtbar .. Bild und Login
	if ($style == "mini")
	{
		$return = '<div class="profile-mini '.$admin_class.'">';
		$return .= '<img src="'.$REX["FRONTEND_FILE"].'?rex_resize=50a__'.$img.'" class="flLeft" />';
		$return .= '<p>'.htmlspecialchars($sql->getValue("$table.firstname"))."<br />".htmlspecialchars($sql->getValue("$table.name")).'</p>';
		$return .= '</div>';
		$premium_class = "";
	}elseif($style == "image")
	{
		$return .= '<img src="'.$REX["FRONTEND_FILE"].'?rex_resize=50a__'.$img.'" class="flLeft" />';
		$premium_class = "";
	}elseif($style == "image_big")
	{
		$return .= '<img src="'.$REX["FRONTEND_FILE"].'?rex_resize=150a__'.$img.'" />';
	}elseif($style == "name")
	{
		$return = nl2br(htmlspecialchars($sql->getValue("$table.firstname")." ".$sql->getValue("$table.name")));
	}elseif($style == "city")
	{
		$return = nl2br(htmlspecialchars($sql->getValue("$table.city")));
	}elseif($style == "url")
	{
		$return = ''.$REX["FRONTEND_FILE"].'?article_id='.REX_COM_PAGE_PROFIL_ID.'&user_id='.$sql->getValue("$table.id");
		$linked = FALSE;
	}else
	{
		$return = nl2br(htmlspecialchars($sql->getValue("$table.$style")));
	}
	
	if ($linked)
	{
		if (isset($REX['COM_USER']) && is_object($REX['COM_USER'])) $return = '<a href="'.rex_getUrl(REX_COM_PAGE_PROFIL_ID,'',array("user_id"=>$sql->getValue("$table.id"))).'" class="'.$admin_class.'">'.$return.'</a>';
	}
	
	return $return;
	
}

?>