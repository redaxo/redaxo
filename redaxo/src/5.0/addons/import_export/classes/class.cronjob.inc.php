<?php

class rex_cronjob_export extends rex_cronjob
{
  const
    DEFAULT_FILENAME = '%HTTP_HOST_rex%REX_VERSION_%Y%m%d';

  public function execute()
  {
    global $REX;

    include_once $REX['INCLUDE_PATH'] .'/addons/import_export/functions/function_import_export.inc.php';
    include_once $REX['INCLUDE_PATH'] .'/addons/import_export/functions/function_import_folder.inc.php';

    $filename = $this->getParam('filename', self::DEFAULT_FILENAME);
    $filename = str_replace("%HTTP_HOST", $_SERVER['HTTP_HOST'], $filename);
    $filename = str_replace("%REX_VERSION", $REX['VERSION'].$REX['SUBVERSION'].$REX['MINORVERSION'], $filename);
    $filename = strftime($filename);
    $file = $filename;
    $dir = getImportDir() .'/';
    $ext = '.sql';
    if (file_exists($dir . $file . $ext))
    {
      $i = 1;
      while (file_exists($dir . $file .'_'. $i . $ext)) $i++;
      $file = $file .'_'. $i;
    }

    if (rex_a1_export_db($dir . $file . $ext))
    {
      $message = $file . $ext . ' created';

      if ($this->sendmail)
      {
        if (!rex_ooAddon::isActivated('phpmailer'))
        {
          $this->setMessage($message .', mail not sent (addon "phpmailer" isn\'t activated)');
          return false;
        }
        $mail = new rex_mailer;
        $mail->AddAddress($this->mailaddress);
        $mail->Subject = $REX['I18N']->msg('im_export_mail_subject');
        $mail->Body = $REX['I18N']->msg('im_export_mail_body', $REX['SERVERNAME']);
        $mail->AddAttachment($dir . $file . $ext, $filename . $ext);
        if ($mail->Send())
        {
          $this->setMessage($message .', mail sent');
          return true;
        }
        $this->setMessage($message .', mail not sent');
        return false;
      }

      $this->setMessage($message);
      return true;
    }
    $this->setMessage($file . $ext . ' not created');
    return false;
  }

  public function getTypeName()
  {
    global $REX;
    return $REX['I18N']->msg('im_export_database_export');
  }

  public function getParamFields()
  {
		global $REX;

    $fields = array(
      array(
        'label' => $REX['I18N']->msg('im_export_filename'),
        'name'  => 'filename',
        'type'  => 'text',
        'default' => self::DEFAULT_FILENAME,
        'notice'  => $REX['I18N']->msg('im_export_filename_notice')
      ),
  		array(
        'name'  => 'sendmail',
        'type'  => 'checkbox',
        'options' => array(1 => $REX['I18N']->msg('im_export_send_mail'))
      )
    );
    if (rex_ooAddon::isActivated('phpmailer'))
    {
      $fields[] = array(
        'label' => $REX['I18N']->msg('im_export_mailaddress'),
        'name'  => 'mailaddress',
        'type'  => 'text',
        'visible_if' => array('sendmail' => 1)
      );
    }
    else
    {
  		$fields[1]['notice'] = $REX['I18N']->msg('im_export_send_mail_notice');
  		$fields[1]['attributes'] = array('disabled' => 'disabled');
    }
    return $fields;
  }
}