<?php

/**
 * @package redaxo\backup
 */
class rex_cronjob_export extends rex_cronjob
{
    public const DEFAULT_FILENAME = '%REX_SERVER_%Y%m%d_%H%M_rex%REX_VERSION';

    public function execute()
    {
        $filename = $this->getParam('filename', self::DEFAULT_FILENAME);
        $filename = str_replace('%REX_SERVER', rex_string::normalize(rex::getServerName(), '-'), $filename);
        $filename = str_replace('%REX_VERSION', rex::getVersion(), $filename);
        $filename = strftime($filename);
        $file = $filename;
        $dir = rex_backup::getDir() . '/';
        $ext = '.cronjob.sql';

        $tables = rex_backup::getTables();
        $blacklist_tables = explode('|', $this->getParam('blacklist_tables'));
        $whitelist_tables = array_diff($tables, $blacklist_tables);

        if (file_exists($dir . $file . $ext)) {
            $i = 1;
            while (file_exists($dir . $file . '_' . $i . $ext)) {
                ++$i;
            }
            $file = $file . '_' . $i;
        }

        if (rex_backup::exportDb($dir . $file . $ext, $whitelist_tables)) {
            $message = $file . $ext . ' created';

            if ($this->getParam('delete_interval')) {
                $allSqlfiles = glob(rex_path::addonData('backup', '*'.$ext));
                $backups = [];
                $limit = strtotime('-1 month'); // Generelle Vorhaltezeit: 1 Monat

                foreach ($allSqlfiles as $sqlFile) {
                    $timestamp = filectime($sqlFile);

                    if ($timestamp > $limit) {
                        // wenn es die generelle Vorhaltezeit unterschreitet
                        continue;
                    }

                    $backups[$sqlFile] = $timestamp;
                }

                asort($backups, SORT_NUMERIC);

                $step = '';
                $countDeleted = 0;

                foreach ($backups as $backup => $timestamp) {
                    $stepLast = $step;
                    $step = date($this->getParam('delete_interval'), (int) $timestamp);

                    if ($stepLast !== $step) {
                        // wenn es zu diesem Interval schon ein Backup gibt
                        continue;
                    }

                    // dann löschen
                    rex_file::delete($backup);
                    ++$countDeleted;
                }

                if ($countDeleted) {
                    $message .= ', '.$countDeleted.' old backup(s) deleted';
                }
            }

            if ($this->getParam('sendmail')) {
                if (!rex_addon::get('phpmailer')->isAvailable()) {
                    $this->setMessage($message . ', mail not sent (addon "phpmailer" isn\'t activated)');

                    return false;
                }
                $mail = new rex_mailer();
                $mail->AddAddress($this->getParam('mailaddress'));
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
        $tables = rex_backup::getTables();

        $fields = [
            [
                'label' => rex_i18n::msg('backup_filename'),
                'name' => 'filename',
                'type' => 'text',
                'default' => self::DEFAULT_FILENAME,
                'notice' => rex_i18n::msg('backup_filename_notice'),
            ],
            [
                'label' => rex_i18n::msg('backup_blacklist_tables'),
                'name' => 'blacklist_tables',
                'type' => 'select',
                'attributes' => ['multiple' => 'multiple', 'data-live-search' => 'true'],
                'options' => array_combine($tables, $tables),
                'notice' => rex_i18n::msg('backup_blacklist_tables_notice'),
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
            $fields[2]['notice'] = rex_i18n::msg('backup_send_mail_notice');
            $fields[2]['attributes'] = ['disabled' => 'disabled'];
        }

        $fields[] = [
            'label' => rex_i18n::msg('backup_delete_interval'),
            'name' => 'delete_interval',
            'type' => 'select',
            'options' => [
                '0' => rex_i18n::msg('backup_delete_interval_off'),
                'YW' => rex_i18n::msg('backup_delete_interval_weekly'),
                'YM' => rex_i18n::msg('backup_delete_interval_monthly'), ],
            'default' => 'YW',
            'notice' => rex_i18n::msg('backup_delete_interval_notice'),
        ];

        return $fields;
    }
}
