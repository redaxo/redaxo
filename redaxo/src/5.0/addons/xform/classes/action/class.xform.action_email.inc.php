<?php

class rex_xform_action_email extends rex_xform_action_abstract
{

	function execute()
	{

		$mail_from = $this->action["elements"][2];
		$mail_to = $this->action["elements"][3];
		$mail_subject = $this->action["elements"][4];
		$mail_body = $this->action["elements"][5];

		foreach ($this->elements_email as $search => $replace)
		{
			$mail_body = str_replace('###'. $search .'###', $replace, $mail_body);
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
		$mail->Send();
	}

	function getDescription()
	{
		return "action|email|from@email.de|to@emila.de|Mailsubject|Mailbody###name###";
	}

}

?>