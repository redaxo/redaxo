<?php

/**
 * @package redaxo\backup
 */
class rex_cronjob_phpmailer extends rex_cronjob
{
    public const DEFAULT_FILENAME = 'PHPMailer-Logs-%Y%m';

    public function execute()
    {
        $dir = rex_path::addonData('phpmailer');
        $filename = $this->getParam('filename', self::DEFAULT_FILENAME);
        $filename = strftime($filename);
        $file = $filename;
        $ext = '.cronjob.zip';
        if (file_exists($dir . $file . $ext)) {
            $i = 1;
            while (file_exists($dir . $file . '_' . $i . $ext)) {
                ++$i;
            }
            $file = $file . '_' . $i;
        }


        $zip = new ZipArchive();
        
        if ($zip->open($dir . $file . $ext, ZipArchive::CREATE)!==true) {
            $message = "cannot open <$file.$ext>\n";
        } else {
            foreach (glob($dir . 'mail_log'.DIRECTORY_SEPARATOR.'*') as $year) {
                foreach (glob($year .DIRECTORY_SEPARATOR. '*') as $month) {
                        $folder = str_replace($dir."mail_log".DIRECTORY_SEPARATOR, "", $month);
                        $zip->addEmptyDir($folder);
                        foreach (glob($month .DIRECTORY_SEPARATOR. '*') as $logfile) {
                            $logfilename = str_replace($dir."mail_log".DIRECTORY_SEPARATOR, "", $logfile);
                        $zip->addFile($logfile, $logfilename);
                        $zip->setEncryptionName($logfilename, ZipArchive::EM_AES_256, $this->getParam('password'));
                    }
                }
            }
    
            $zip->close();

            rex_dir::delete($dir.'mail_log', false);

            $message = $file . $ext . ' created';

            if ($this->getParam('delete_interval')) {
                $allBackupFiles = glob(rex_path::addonData('phpmailer', '*.cronjob.zip'));
                $backups = [];
                $limit = strtotime('-1 month'); // Generelle Vorhaltezeit: 1 Monat

                foreach ($allBackupFiles as $backupFile) {
                    $timestamp = filectime($backupFile);

                    if ($timestamp > $limit) {
                        // wenn es die generelle Vorhaltezeit unterschreitet
                        continue;
                    }

                    $backups[$backupFile] = $timestamp;
                }

                asort($backups, SORT_NUMERIC);

                $countDeleted = 0;

                foreach ($backups as $backup => $timestamp) {
                    $stepLast = $step;
                    $step = date($this->getParam('delete_interval'), (int) $timestamp);

                    // dann löschen
                    rex_file::delete($backup);
                    ++$countDeleted;
                }

                if ($countDeleted) {
                    $message .= ', '.$countDeleted.' old backup(s) deleted';
                }
            }

            $this->setMessage($message);

            return true;
        }
        $this->setMessage($file . $ext . ' not created');

        return false;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('phpmailer_logs');
    }

    public function getParamFields()
    {
        $fields = [
            [
                'label' => rex_i18n::msg('phpmailer_compressed_filename'),
                'name' => 'filename',
                'type' => 'text',
                'default' => self::DEFAULT_FILENAME,
                'notice' => rex_i18n::msg('phpmailer_compressed_filename_notice'),
            ],
             [
                'label' => rex_i18n::msg('phpmailer_compressed_file_password'),
                'name' => 'password',
                'type' => 'text',
             ],
             [
            'label' => rex_i18n::msg('phpmailer_delete_interval'),
            'name' => 'delete_interval',
            'type' => 'select',
            'options' => [
                '0' => rex_i18n::msg('phpmailer_delete_interval_off'),
                'YW' => rex_i18n::msg('phpmailer_delete_interval_weekly'),
                'YM' => rex_i18n::msg('phpmailer_delete_interval_monthly'), ],
            'default' => 'YW',
            'notice' => rex_i18n::msg('phpmailer_delete_interval_notice'),
            ]
        ];

        return $fields;
    }
}
