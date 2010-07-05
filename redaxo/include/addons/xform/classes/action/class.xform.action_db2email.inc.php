<?php

class rex_xform_action_db2email extends rex_xform_action_abstract
{
	
	function execute()
	{

		global $REX;

		$gt = rex_sql::factory();
		if ($this->params["debug"]) $gt->debugsql = true;
		$gt->setQuery('select * from '.$REX['TABLE_PREFIX'].'xform_email_template where name="'.$this->action["elements"][2].'"');
		if ($gt->getRows()==1)
		{
			
			$mail_to = $REX['ERROR_EMAIL'];
			if (isset($this->action["elements"][3]) && $this->action["elements"][3] != "")
			{
				foreach($this->elements_email as $key => $value)
					if ($this->action["elements"][3]==$key) $mail_to = $value;
			}
			
			if (isset($this->action["elements"][4]) && $this->action["elements"][4] != "") 
				$mail_to = $this->action["elements"][4];
			
			$mail_from = $gt->getValue("mail_from");
			$mail_subject = $gt->getValue("subject");
			$mail_body = $gt->getValue("body");

			foreach ($this->elements_email as $search => $replace)
			{
				$mail_from = str_replace('###'. $search .'###', $replace, $mail_from);
				$mail_subject = str_replace('###'. $search .'###', $replace, $mail_subject);
				$mail_body = str_replace('###'. $search .'###', $replace, $mail_body);
				$mail_from = str_replace('***'. $search .'***', urlencode($replace), $mail_from);
				$mail_subject = str_replace('***'. $search .'***', urlencode($replace), $mail_subject);
				$mail_body = str_replace('***'. $search .'***', urlencode($replace), $mail_body);
			}
	
			$mail = new rex_mailer();
			$mail->AddAddress($mail_to, $mail_to);
			$mail->WordWrap = 80;
			$mail->FromName = $mail_from;
			$mail->From = $mail_from;
			$mail->Subject = $mail_subject;
			$mail->Body = nl2br($mail_body);
			$mail->AltBody = strip_tags($mail_body);
			// $mail->IsHTML(true);
			if (!$mail->Send()) echo "FAILED";

		}

	}

	function getDescription()
	{
		return "action|db2email|namekey|emaillabel|[email@domain.de]";
	}

}

?>