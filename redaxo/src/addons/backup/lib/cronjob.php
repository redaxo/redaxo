<?php

/**
 * @package redaxo\backup
 */
class rex_cronjob_export extends rex_cronjob
{
    const DEFAULT_FILENAME = '%REX_SERVER_rex%REX_VERSION_%Y%m%d_%H%M';

    public function execute()
    {
        $filename = $this->getParam('filename', self::DEFAULT_FILENAME);
        $filename = str_replace('%REX_SERVER', parse_url(rex::getServer(), PHP_URL_HOST), $filename);
        $filename = str_replace('%REX_VERSION', rex::getVersion(), $filename);
        $filename = strftime($filename);
        $file = $filename;
        $dir = rex_backup::getDir() . '/';
        $ext = '.sql';
        if (file_exists($dir . $file . $ext)) {
            $i = 1;
            while (file_exists($dir . $file . '_' . $i . $ext)) {
                ++$i;
            }
            $file = $file . '_' . $i;
        }

        if (rex_backup::exportDb($dir . $file . $ext)) {
            $message = $file . $ext . ' created';

            if ($this->sendmail) {
                if (!rex_addon::get('phpmailer')->isAvailable()) {
                    $this->setMessage($message . ', mail not sent (addon "phpmailer" isn\'t activated)');
                    return false;
                }
                $mail = new rex_mailer();
                $mail->AddAddress($this->mailaddress);
                $mail->Subject = rex_i18n::msg('backup_mail_subject');
                $mail->Body = rex_i18n::msg('backup_mail_body', rex::getServerName());
                $mail->AddAttachment($dir . $file . $ext, $filename . $ext);
                if ($mail->Send()) {
                    $this->setMessage($message . ', mail sent');
                    return true;
                }
                $this->setMessage($message . ', mail not sent');
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
        return rex_i18n::msg('backup_database_export');
    }

    public function getParamFields()
    {
        $fields = [
            [
                'label' => rex_i18n::msg('backup_filename'),
                'name' => 'filename',
                'type' => 'text',
                'default' => self::DEFAULT_FILENAME,
                'notice' => rex_i18n::msg('backup_filename_notice'),
            ],
            [
                'name' => 'sendmail',
                'type' => 'checkbox',
                'options' => [1 => rex_i18n::msg('backup_send_mail')],
            ],
        ];
        if (rex_addon::get('phpmailer')->isAvailable()) {
            $fields[] = [
                'label' => rex_i18n::msg('backup_mailaddress'),
                'name' => 'mailaddress',
                'type' => 'text',
                'visible_if' => ['sendmail' => 1],
            ];
        } else {
            $fields[1]['notice'] = rex_i18n::msg('backup_send_mail_notice');
            $fields[1]['attributes'] = ['disabled' => 'disabled'];
        }
        return $fields;
    }
}
