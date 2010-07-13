<?php

class rex_com_board
{

	var $boardname;
	var $realboardname;

	var $anonymous = false;
	var $userjoin_query;
	var $userjoin_field;
	var $user_id;
	var $username;

	var $admin;

	var $addlink;
	var $linkuser;

	var $errmsg;
	var $text;

	var $msg;

	var $form_post_action;

	var $debug;

	// ----- konstruktor
	function rex_com_board()
	{
		global $REX;
		$this->addlink = array();
		$this->admin = false;
		$this->setBoardname("standard");
		$this->setRealBoardname("standard");
		$this->setLang("de");
		$this->setArticleId(1);
		$this->msg = array();
		$this->setFormPostAction($REX["FRONTEND_FILE"]);
		$this->debug = FALSE;
		$this->setUserjoin();
		$this->setLinkUser("",0);

	}

	// ----- REX Article Id
	function setArticleId($id)
	{
		$this->article_id = $id;
		$this->addlink("article_id",$id);
	}

	// ----- Link Adds
	function addLink($name,$value)
	{
		$this->addlink[$name] = $value;
	}

	// ----- Set Form Post Action
	function setFormPostAction($name)
	{
		$this->form_post_action = $name;
	}

	// ----- Link erstellen
	function getLink($extra_params = array())
	{
		$params = array_merge($this->addlink,$extra_params);
		return rex_getUrl($this->article_id,'',$params);
	}
	
	// ----- FormArray zurückgeben
	function getFormDetails($extra_params)
	{
		$params = array_merge($this->addlink,$extra_params);
		$return["form"] = '<form name="bb_form" id="bb_form" action="'.$this->form_post_action.'" method="post">';
		for($i=1;$i<=count($params);$i++)
		{
			$return["hidden"] .= '<input type="hidden" name="'.key($params).'" value="'.htmlspecialchars(current($params)).'" />';
			next($params);
		}
		$return["submit"] = '<p class="formsubmit"><input type="submit" value="'.$this->text[160].'" /></p>';
		return $return;
	}

	// ----- Admin ? -> Delete Funktionen möglich
	function setAdmin()
	{
		$this->admin = true;
	}

	// ----- Boardname - Allgemeine Bezeichnung
	function setBoardname($boardname)
	{
		$this->boardname = $boardname;
		$this->addlink("bb_boardname",$boardname);
	}

	// ----- Tabellenname
	function setRealBoardname($realboardname)
	{
		$this->realboardname = $realboardname;
	}

	// ----- Anonymous Board ?
	function setAnonymous($status)
	{
		if ($status !== false) $status = true;
		$this->anonymous = $status;
		$this->userjoin_query = "";
		$this->userjoin_field = "";
	}

	// ----- wenn userjoin -> hier link to userpage
	function setLinkUser($link,$id)
	{
		$this->linkuser = $link;
	}

	// ----- Eingeloggter User
	function setUser($user_id)
	{
		$this->user_id = $user_id;
	}

	// ----- Userjoin .. Wie ist die Tabelle verknüpft -> rex_2_user
	function setUserjoin($userjoin = "",$userfield = "")
	{
		if ($userjoin == "") $userjoin = "rex_com_user on rex_com_board.user_id=rex_com_user.login";
		if ($userfield == "") $userfield = "rex_com_user.login";
		
		$this->userjoin_query = "left join $userjoin";
		$this->userjoin_field = $userfield;
	}

	// ----- Darstellung des Users
	function showUser(&$sql,$table = '')
	{
		if ($this->anonymous)
		{
			$link = $sql->getValue("user_id");	
		}else
		{
			$link = rex_com_showUser(&$sql, "mini", $table);
			// $link = htmlspecialchars($sql->getValue($this->userjoin_field));
			// if ($this->linkuser != "") $link = '<a href="'.$this->linkuser.'">'.$link.'</a>';
		}
		return $link;
	}

	// ----- Userzugriff erlaubt ?
	function checkVars()
	{
		if ($this->anonymous) return true;
		elseif ($this->user_id == "") return false;
		else return true;
	}


	// ----- Board anzeigen - Zuweisung der entsprechenden Funktion
	function showBoard()
	{
		$return = "";
		$bb_func = $_REQUEST["bb_func"];
		$msg["bb_msg_id"] = (int) $_REQUEST["bb_msg_id"];
		$msg["bb_msg_subject"] = $_REQUEST["bb_msg_subject"];
		$msg["bb_msg_message"] = $_REQUEST["bb_msg_message"];
		$msg["bb_msg_anouser"] = $_REQUEST["bb_msg_anouser"];
		$this->msg = $msg;
		if ($bb_func == "deleteMessage" && $this->admin) $return .= $this->deleteMessage($msg["bb_msg_id"]);
		else if ($bb_func == "reply" && $this->checkVars() ) $return .= $this->saveMessage();
		else if ($bb_func == "addtopic" && $this->checkVars() ) $return .= $this->saveMessage();
		else if ($bb_func == "showMessage") $return .= $this->showMessage();
		else if ($bb_func == "showAddMessage" && $this->checkVars()) $return .= $this->showAddMessage();
	    else if ($bb_func == "showAddTopic" && $this->checkVars()) $return .= $this->showAddTopic();
		else $return .= $this->showMessages();

		return $return;
	}

	// ----- Nachrichtenübersicht anzeigen
	function showMessages()
	{
		$msql = new rex_sql();
		if($this->debug) $msql->debugsql = 1;
		$msql->setQuery("
			select 
				* 
			from 
				rex_com_board 
			$this->userjoin_query 
			where 
				rex_com_board.re_message_id='0' 
				and rex_com_board.board_id='".$this->boardname."' 
				and rex_com_board.status='1' 
			order by 
				last_entry desc
			");

		$mout .= '	<div class="com-tab com-tab-no-navi com-board com-board-topics">
					<div class="com-tab-cntnt">
					<div class="com-tab-cntnt-2">
					<div class="com-tab-cntnt-3">';
		
		
		$mout .= '		<div class="com-board-info">
							<h2>'.$this->text[10].' <a href="'.$this->getLink().'">'.$this->realboardname.'</a></h2>
						</div>
						
						<div class="com-board-info com-board-topics-found">
							<p>';
		
		if ($msql->getRows()==0) $mout .= $this->text[23];
		elseif ($msql->getRows()==1) $mout .= $this->text[22];
		else $mout .= $msql->getRows()." ".$this->text[20];				
		
		if ( $this->checkVars() ) $mout .= ' - <a href="'.$this->getLink(array("bb_func"=>"showAddTopic")).'">'.$this->text[30].'</a>';
						
		$mout .= '		</p></div>';
		
		if ($this->errmsg != "") $mout .= '<p class="com-warning">'.$this->errmsg.'</p>';
		
		
		for ($i=0;$i<$msql->getRows();$i++)
		{
		
			$mout .= '	<div class="com-topic">
							<div class="com-image">
								<p class="image">'.rex_com_showUser(&$msql, "image", "", TRUE, FALSE).'</p>
							</div>
						
							<div class="com-content">
							<div class="com-content-2">
								<p class="user-name"><span>'.rex_com_showUser(&$msql, "name", "", TRUE).'</span> '.$this->text[501].' '.date($this->text[150],$msql->getValue("rex_com_board.stamp")).':</p>
								<p class="topic">
									<a href="'.$this->getLink(array("bb_func" => "showMessage", "bb_msg_id" => $msql->getValue("rex_com_board.message_id"))).'">';

			if ($msql->getValue("subject")== "") $mout .= $this->text[90];
			else $mout .= $msql->getValue("subject");					

			$mout .= '				</a>
								</p>
								
								<p class="replies">'.$this->text[60].': <span>'.$msql->getValue("rex_com_board.replies").'</span> - '.$this->text[80].': <span>'.date($this->text[150],$msql->getValue("rex_com_board.last_entry")).'</span></p>';
			
			$mout .= '		</div></div>
							<div class="clearer"> </div>
						</div>';


			$msql->next();
		}
		
		if ($msql->getRows()==0) 
			$mout .= '<div class="com-board-info com-board-no-topics">
						<p>'.$this->text[130].'</p>
					</div>';
		
				
		$mout .= '
				</div>
				</div>
				</div>
				</div>';
		
		return $mout;
	}
	
	
	
	
	

	// ----- letzte Nachricht mit Topic anzeigen
	function showLastMessage()
	{
		
		global $REX;
		
		$msql = new rex_sql();
		if($this->debug) 
			$msql->debugsql = 1;
		$msql->setQuery('
			SELECT 
				* 
			FROM
				rex_com_board AS topic, 
				rex_com_board AS message
			LEFT JOIN rex_com_user 
				ON message.user_id = rex_com_user.login
			WHERE 	topic.message_id = message.re_message_id
			AND		topic.replies > "0"
			AND		topic.status= "1" 
			AND 	message.status= "1" 
			ORDER BY 
				message.stamp desc
			LIMIT 1
			');




		$mout .= '	<div class="box-v3-1 box-v3-1-c3">
					<div class="box-v3-2">
					<div class="box-v3-3">
					
						<h3 class="has-icon icon-discussion">Debatte</h3>';
		
		
						
		$link = '';			
		
		if ($msql->getRows()==1) 
		{	
			
			$this->setBoardname($msql->getValue('board_id'));
			
			$link = $this->getLink(array("bb_func" => "showMessage", "bb_msg_id" => $msql->getValue("topic.message_id")));
			
			$mout .= '<div class="txt">
						<p><strong>'.$this->text[45].'</strong><br /> <a href="'.$link.'">';
						
			if ($msql->getValue("subject")== "") $mout .= $this->text[90];
			else $mout .= $msql->getValue("subject");
			
						$mout .= '</a></p>
						<p><strong>'.$this->text[60].'</strong> <a href="'.$link.'">'.$msql->getValue("topic.replies").'</a></p>
						
						<p><strong>'.$this->text[80].'</strong><br />'.date($this->text[150],$msql->getValue("message.stamp")).'<br /> 
						'.rex_com_showUser(&$msql, "name", "", TRUE).'</p>

					</div>';


		}
		
		if ($msql->getRows()==0) 
			$mout .= '<div class="com-board-info com-board-no-topics">
						<p>'.$this->text[130].'</p>
					</div>';
		
				
		$mout .= '<div class="splt"></div>
				
				<div class="txt">';
				
		if ($link != '')
			$mout .= '<p><a href="'.$link.'" class="link-discussion">mitdiskutieren</a></p>';
		
		$mout .= '	
				</div>
			</div>
			</div>
			</div>';
		
		return $mout;
	}
	
	
	
	


	// ----- Topic hinzufügen.. Form

	function showAddTopic()
	{
	
		global $REX;
		
		$form = $this->getFormDetails(array("bb_func"=>"addtopic"));
		
		
			
		$mout .= '	<div class="com-tab com-tab-no-navi com-board">
					<div class="com-tab-cntnt">
					<div class="com-tab-cntnt-2">
					<div class="com-tab-cntnt-3">';
						
		$mout .= '	<div id="rex-form"><div class="spcl-bgcolor">';
		
		$mout .= 		$form["form"].$form["hidden"].'
						<div class="com-board-info">
							<p>'.$this->text[10].' <a href="'.$this->getLink().'">'.$this->realboardname.'</a></p>
							<h2>'.$this->text[30].'</h2>
						</div>
						
						'.$this->warning();
						
						
		if($this->anonymous)
		{
		
			$mout .= '
				<p class="formtext">
					<label class="text" for="f-bb-msg-anouser" >'.$this->text[290].'</label>
					<input type="text" class="text" id="f-bb-msg-anouser" name="bb_msg_anouser" maxlength="30" value="'.stripslashes(htmlspecialchars($this->msg["bb_msg_anouser"])).'" />
				</p>';
		}
		
		$mout .= '
			<p class="formtext">
				<label class="text" for="f-bb-msg-subject" >'.$this->text[45].'</label>
				<input type="text" class="text" id="f-bb-msg-subject" name="bb_msg_subject" maxlength="30" value="'.htmlspecialchars($this->msg["bb_msg_subject"]).'" />
			</p>';
		
		
		
		if(!$this->anonymous)
		{
			$mout .= '
				<p class="formtext">
					<label class="text" for="f-bb-msg-user" >'.$this->text[290].'</label>
					<input type="text" class="inp_disabled" id="f-bb-msg-user" name="bb_msg_user" disabled="disabled" value="'.rex_com_showUser(&$REX["COM_USER"]->USER, "name", "", FALSE).'" />
				</p>';
		}
		
		$mout .= '
	
				<p class="formtextarea">
					<label class="textarea" for="f-bb-msg-message" >'.$this->text[140].'</label>
					<textarea class="textarea " name="bb_msg_message" id="f-bb-msg-message" cols="80" rows="10">'.stripslashes(htmlspecialchars($this->msg["bb_msg_message"])).'</textarea>
				</p>
				
				<div class="clearer"> </div>
				'.$form["submit"];

		$mout .= '</form></div></div>';
		
		$mout .= '
				</div>
				</div>
				</div>
				</div>';
		
		return $mout;
	}


	// ----- Einzelne Message anzeigen
	function showMessage()
	{
		global $REX;
		
		$msql = new rex_sql();
		if($this->debug) $msql->debugsql = 1;
		$msql->setQuery("select * from rex_com_board $this->userjoin_query where rex_com_board.re_message_id='0' and rex_com_board.board_id='".$this->boardname."' and rex_com_board.message_id='".$this->msg["bb_msg_id"]."' and rex_com_board.status='1'");

		if ($msql->getRows() == 1)
		{
			
			
			$mout = '
					<div class="com-tab com-tab-no-navi com-board">
					<div class="com-tab-cntnt">
					<div class="com-tab-cntnt-2">
					<div class="com-tab-cntnt-3">
						<div class="com-board-info">
							<p>'.$this->text[10].' <a href="'.$this->getLink().'">'.$this->realboardname.'</a></p>
							<h2>'.$msql->getValue("rex_com_board.subject").'</h2>
						</div>
						<div class="com-comment-topic com-comment">
							<div class="com-image">
								<p class="image">'.rex_com_showUser(&$msql, "image", "", TRUE, FALSE).'</p>
							</div>
						
							<div class="com-content">
							<div class="com-content-2">
								<p class="user-name"><span>'.rex_com_showUser(&$msql, "name", "", TRUE).'</span> '.$this->text[501].' '.date($this->text[150],$msql->getValue("rex_com_board.stamp")).':</p>
								<p class="message">'.nl2br(htmlspecialchars($msql->getValue("rex_com_board.message"))).'</p>';
			if ($this->admin) 
				$mout .= '		<p class="link-button"><a href="'.$this->getLink(array("bb_func" => "deleteMessage", "bb_msg_id" => $msql->getValue("rex_com_board.message_id"))).'"><span>'.$this->text[270].'</span></a></p>';
				
			$mout .= '		</div></div>
							<div class="clearer"> </div>
						</div>';
		

			$mrsql = new rex_sql();
			if($this->debug) $mrsql->debugsql = 1;

			$mrsql->setQuery("select * from rex_com_board $this->userjoin_query where rex_com_board.re_message_id='".$this->msg["bb_msg_id"]."' and rex_com_board.status=1");

			if ($mrsql->getRows()>0)
			{
				$mout .= '
						<div class="com-board-info">
							<h3>'.$this->text[60].'</h3>
						</div>';
				for ($i=0;$i<$mrsql->getRows();$i++)
				{
					$mout .= '
						<div class="com-comment">
							<div class="com-image">
								<p class="image">'.rex_com_showUser(&$mrsql, "image", "", TRUE, FALSE).'</p>
							</div>
						
							<div class="com-content">
							<div class="com-content-2">
								<p class="user-name"><span>'.rex_com_showUser(&$mrsql, "name", "", TRUE).'</span> '.$this->text[501].' '.date($this->text[150],$mrsql->getValue("rex_com_board.stamp")).':</p>
								<p class="message">'.nl2br(htmlspecialchars($mrsql->getValue("rex_com_board.message"))).'</p>';

					if ($this->admin)
						$mout .= '<p class="link-button"><a href="'.$this->getLink(array("bb_func" => "deleteMessage", "bb_msg_id" => $mrsql->getValue("rex_com_board.message_id"))).'"><span>'.$this->text[280].'</span></a></p>';
						
					$mout .= '
							</div></div>
							<div class="clearer"> </div>
						</div>';
						
						
					$mrsql->next();
				}
			}else
			{
				$mout .= '
						<div class="com-board-info">
							<h3>'.$this->text[170].'</h3>
						</div>';
			}

			if ( $this->checkVars() )
			{
				
				$mout .= '<div class="com-answer">
						<div class="com-board-info">
							<h3>'.$this->text[180].'</h3>
						</div>';
						
				$mout .= $this->warning(2);
				
				$form = $this->getFormDetails(array("bb_func" => "reply", "bb_msg_id" => $this->msg["bb_msg_id"]));
				
				$mout .= '<div id="rex-form">';
				$mout .= $form["form"].$form["hidden"];
				
				
				if (!$this->anonymous)
					$mout .= '	<div class="com-image">
									<p class="image">'.rex_com_showUser(&$REX["COM_USER"]->USER, "image", "", FALSE).'</p>
								</div>';
						
							
						
				
				$mout .= '<div class="com-content">
							<div class="com-content-2">';
						
				if ($this->anonymous)
				{	
				
					$mout .= '
						<p class="formtext">
							<label class="text" for="f-bb-msg-anouser" >'.$this->text[290].'</label>
							<input type="text" class="text" id="f-bb-msg-anouser" name="bb_msg_anouser" maxlength="30" value="'.stripslashes(htmlspecialchars($this->msg["bb_msg_anouser"])).'" />
						</p>';
				}
				
				else
				{
					$mout .= '
						<p class="formtext">
							<label class="text" for="f-bb-msg-user" >'.$this->text[290].'</label>
							<input type="text" class="inp_disabled" id="f-bb-msg-user" name="bb_msg_user" disabled="disabled" value="'.rex_com_showUser(&$REX["COM_USER"]->USER, "name", "", FALSE).'" />
						</p>';
				}
				
				$mout .= '
			
						<p class="formtextarea">
							<label class="textarea" for="f-bb-msg-message" >'.$this->text[200].'</label>
							<textarea class="textarea " name="bb_msg_message" id="f-bb-msg-message" cols="80" rows="10">'.stripslashes(htmlspecialchars($this->msg["bb_msg_message"])).'</textarea>
						</p>
						
						<div class="clearer"> </div>
						'.$form["submit"];
	
				$mout .= '</div></div>
							<div class="clearer"> </div>
							</form></div>';
				
				$mout .= '</div>';

			}
			$mout .= '
					</div>
					</div>
					</div>
					</div>';
		}
		return $mout;
	}


	// ----- Nachrichten speichern
	function saveMessage()
	{
		if(($this->anonymous == true) && ($this->msg["bb_msg_anouser"] == ''))
		{
			$this->errmsg = $this->text[300];

			if($this->msg["bb_msg_id"] > 0)
			{
				return $this->showMessage();
			} else 
			{
				return $this->showAddTopic();
			}
		}

		if ($this->msg["bb_msg_id"] > 0)
		{
			// reply
			$r_sql = new rex_sql();
			if($this->debug) $r_sql->debugsql = 1;

			$r_sql->setQuery("select * from rex_com_board where message_id='".$this->msg["bb_msg_id"]."' and board_id='".$this->boardname."' and status='1'");

			if (trim($this->msg["bb_msg_message"]) == "" && $r_sql->getRows() == 1)
			{
				$this->errmsg = $this->text[200];

			}elseif ($r_sql->getRows() == 1)
			{
				// insert reply
				$r_sql = new rex_sql();
				if($this->debug) $r_sql->debugsql = 1;

				$r_sql->setTable("rex_com_board");
				if ($this->anonymous) $r_sql->setValue("user_id",$this->msg["bb_msg_anouser"]);
				else $r_sql->setValue("user_id",$this->user_id);
				$r_sql->setValue("message",$this->msg["bb_msg_message"]);
				$r_sql->setValue("re_message_id",$this->msg["bb_msg_id"]);
				$r_sql->setValue("stamp",time());
				$r_sql->setValue("board_id",$this->boardname);
				$r_sql->setValue("status",1);
				$r_sql->insert();

				// update message
				$u_sql = new rex_sql();
				if($this->debug) $r_sql->debugsql = 1;
				$u_sql->setQuery("select * from rex_com_board where re_message_id='".$this->msg["bb_msg_id"]."' and status='1'");
				$u_sql->setTable("rex_com_board");
				$u_sql->setWhere("message_id='".$this->msg["bb_msg_id"]."'");
				$u_sql->setValue("last_entry",time());
				$u_sql->setValue("replies",$u_sql->getRows());
				$u_sql->update();
				$this->errmsg = $this->text[210];
				
				$this->msg["bb_msg_message"] = "";
				$this->msg["bb_msg_subject"] = "";

			}else
			{
				$this->errmsg = $this->text[220];
			}
			$return = $this->showMessage();

		}else
		{
			// new topic

			if ($this->msg["bb_msg_subject"] != "")
			{
				$r_sql = new rex_sql();
				if($this->debug) $r_sql->debugsql = 1;
				$r_sql->setTable("rex_com_board");
				if ($this->anonymous) $r_sql->setValue("user_id",$this->msg["bb_msg_anouser"]);
				else $r_sql->setValue("user_id",$this->user_id);
				$r_sql->setValue("subject",$this->msg["bb_msg_subject"]);
				$r_sql->setValue("message",$this->msg["bb_msg_message"]);
				$r_sql->setValue("re_message_id",0);
				$r_sql->setValue("stamp",time());
				$r_sql->setValue("last_entry",time());
				$r_sql->setValue("board_id",$this->boardname);
				$r_sql->setValue("replies",0);
				$r_sql->setValue("status",1);
				$r_sql->insert();
				$this->errmsg = $this->text[230];
				$return = $this->showMessages();

				$this->msg["bb_msg_message"] = "";
				$this->msg["bb_msg_subject"] = "";

			}else
			{
				$this->errmsg = $this->text[240];
				$return = $this->showAddTopic();
			}
		}
		return $return;
	}

	function deleteMessage($message_id)
	{
		// reply
		$r_sql = new rex_sql();
		if($this->debug) $r_sql->debugsql = 1;
		$r_sql->setQuery("select * from rex_com_board where message_id='$message_id' and board_id='".$this->boardname."'");


		if ($r_sql->getRows() == 1)
		{
			if ($r_sql->getValue("re_message_id")!=0)
			{

				// reply
				$ur_sql = new rex_sql();
				if($this->debug) $ur_sql->debugsql = 1;
				$ur_sql->setTable("rex_com_board");
				$ur_sql->setWhere("message_id='$message_id'");
				$ur_sql->setValue("status",0);
				$ur_sql->update();

				$message_id = $r_sql->getValue("re_message_id");

				// update topic
				$u_sql = new rex_sql();
				if($this->debug) $u_sql->debugsql = 1;
				$u_sql->setQuery("select * from rex_com_board where re_message_id='$message_id' and status='1'");

				$u_sql->setTable("rex_com_board");
				$u_sql->setWhere("message_id='$message_id'");
				$u_sql->setValue("replies",$u_sql->getRows());
				$u_sql->update();

				$this->msg["bb_msg_id"] = $r_sql->getValue("re_message_id");

				$return = $this->showMessage();
			}else
			{
				// topic
				$u_sql = new rex_sql();
				if($this->debug) $u_sql->debugsql = 1;
				$u_sql->setTable("rex_com_board");
				$u_sql->setWhere("message_id='$message_id' or re_message_id='$message_id'");
				$u_sql->setValue("status",0);
				$u_sql->update();

				$this->errmsg = $this->text[250];
				$return = $this->showMessages();
			}
		}else
		{
			$this->errmsg = $this->text[260];
			$return = $this->showMessages();
		}


		return $return;
	}

	function warning($colspan=2)
	{
		if ($this->errmsg != "") return '<p class="com-warning">'.$this->errmsg.'</p>';
		else return "";
	}

	function setLang($lang)
	{
		if ($lang == "en")
		{
			// --- en
			$this->text[10] = "Forum name: ";
			$this->text[20] = "Topics found"; // 10 Themen gefunden
			$this->text[22] = "One topic found"; // 1 Thema gefunden
			$this->text[23] = "No topics found"; // Kein Thema gefunden
			$this->text[30] = "Add new topic";
			$this->text[40] = "Topic";
			$this->text[45] = "Topic";
			$this->text[50] = "Author";
			$this->text[60] = "Replies";
			$this->text[70] = "Created";
			$this->text[80] = "Last entry";
			$this->text[90] = "[ No title entered ]";
			$this->text[100]= "New";
			$this->text[110]= "Today";
			$this->text[120]= "Yesterday";
			$this->text[130]= "No topics found"; // ! doppelt, siehe text[23]
			$this->text[140]= "Message";
			$this->text[150]= "d M H:i";
			$this->text[155]= "h";
			$this->text[160]= "Add topic";
			$this->text[170]= "No replies";
			$this->text[180]= "Your reply";
			$this->text[190]= "Add reply";
			$this->text[200]= "Please enter a reply!";
			$this->text[210]= "Reply added";
			$this->text[220]= "No such topic.";
			$this->text[230]= "Topic added";
			$this->text[240]= "You forgot to enter a title for your topic. The topic was not added!";
			$this->text[250]= "Topic and replies deleted!";
			$this->text[260]= "No such topic!";
			$this->text[270]= "[ delete topic and messages ]";
			$this->text[280]= "[ delete message ]";
			$this->text[290]= "Name";
			$this->text[300]= "Please enter your name";
			$this->text[501]= "wrote on";
		}else
		{
			// --- de
			$this->text[10] = "Forumname: ";
			$this->text[20] = "Themen gefunden"; // 10 Themen gefunden
			$this->text[22] = "Ein Thema gefunden"; // 1 Thema gefunden
			$this->text[23] = "Keine Themen gefunden"; // Kein Thema gefunden
			$this->text[30] = "Neues Thema hinzufügen";
			$this->text[40] = "Themen";
			$this->text[45] = "Thema";
			$this->text[50] = "Autor";
			$this->text[60] = "Antworten";
			$this->text[70] = "erstellt am";
			$this->text[80] = "Letzter Beitrag";
			$this->text[90] = "[ Kein Titel eingeben ]";
			$this->text[100]= "Neu";
			$this->text[110]= "Heute";
			$this->text[120]= "Gestern";
			$this->text[130]= "Keine Themen gefunden";
			$this->text[140]= "Nachricht";
			$this->text[150]= "d M H:i";
			$this->text[155]= "h";
			$this->text[160]= "Antwort hinzufügen";
			$this->text[170]= "Keine Antworten";
			$this->text[180]= "Deine Antwort";
			$this->text[190]= "Antwort hinzufügen";
			$this->text[200]= "Bitte gib eine Antwort ein !";
			$this->text[210]= "Antwort wurde hinzugefügt";
			$this->text[220]= "Dieses Thema existiert nicht.";
			$this->text[230]= "Thema wurde hinzugefügt";
			$this->text[240]= "Du hast keine Themaüberschrift eingegeben. Thema wurde nicht hinzugefügt !";
			$this->text[250]= "Thema und Antworten wurden gelöscht !";
			$this->text[260]= "Dieses Thema existiert nicht !";
			$this->text[270]= "[ delete topic and messages ]";
			$this->text[280]= "[ delete message ]";
			$this->text[290]= "Name";
			$this->text[300]= "Bitte gib einen Namen ein";
			$this->text[501]= "schrieb am";
		}
	}
}

?>