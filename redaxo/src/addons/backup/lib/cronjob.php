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

            if ($this->delete_interval) {
                $files = (glob(rex_path::addonData('backup').'*.sql'));
                $backups = [];

                foreach ($files as $file) {
                    $backups[filemtime($file)] = $file;
                }
                ksort($backups);

                $limit = 60 * 60 * 24 * 31; // Generelle Vorhaltezeit: 1 Monat
                $step = '';

                foreach ($backups as $timestamp => $backup) {
                    $step_last = $step;
                    $step = date($this->delete_interval, (int) $timestamp);

                    if ($step_last != $step) { // wenn es in diesem bestimmten Zeitraum unterschreitet
                        continue;
                    }
                    if ($timestamp > (time() - $limit)) { // wenn es zu diesem Interval schon ein Backup gibt
                        continue;
                    }   // dann löschen
                    unlink($backup);
                    $message .= '\n'.$backup.' deleted';
                }
            }

            if ($this->sendmail) {
                if (!rex_addon::get('phpmailer')->isAvailable()) {
                    $this->setMessage($message . ', mail not sent (addon "phpmailer" isn\'t activated)');

                    return false;
                }
                $mail = new rex_mailer();
                $mail->AddAddress($this->mailaddress);
                $mail->Subject = rex_i18n::rawMsg('backup_mail_subject');
                $mail->Body = rex_i18n::rawMsg('backup_mail_body', rex::getServerName());
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
            [
                'label' => rex_i18n::msg('backup_delete_interval'),
                'name' => 'delete_interval',
                'type' => 'select',
                'options' => [
                    '0' => rex_i18n::msg('backup_delete_interval_off'),
                    'YW' => rex_i18n::msg('backup_delete_interval_weekly'),
                    'YM' => rex_i18n::msg('backup_delete_interval_monthly'), ],
                'default' => 'YW',
                'notice' => rex_i18n::msg('backup_delete_interval_notice'),
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
