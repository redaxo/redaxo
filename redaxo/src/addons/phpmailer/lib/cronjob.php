<?php

/**
 * @package redaxo\phpmailer
 */
class rex_cronjob_mailer_purge extends rex_cronjob
{
    private function purgeMailarchive(int $days = 7, string $dir = ''): int
    {
        $log = 0;
        $files = glob($dir . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $log += self::purgeMailarchive($days, $file);
                } elseif ((time() - filemtime($file)) > (60 * 60 * 24 * $days)) {
                    if (rex_file::delete($file)) {
                        ++$log;
                    }
                }
            }
            if ('' != $dir && $dir != rex_mailer::logFolder() && is_dir($dir)) {
                @rmdir($dir);
            }
        }
        return $log;
    }

    public function execute()
    {
        $logfolder = rex_mailer::logFolder();
        if ('' != $logfolder && is_dir($logfolder)) {
            $days = (int) $this->getParam('days');
            $purgeLog = self::purgeMailarchive($days, $logfolder);
            if (0 != $purgeLog) {
                $this->setMessage('Mails deleted: '.$purgeLog);
                return true;
            }
            $this->setMessage('No Mails found to delete');
            return true;
        }
        $this->setMessage('Unable to find the phpmailer archive folder');
        return false;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('phpmailer_archivecron');
    }

    public function getParamFields()
    {
        return [
            [
                'label' => rex_i18n::msg('phpmailer_archivecron_label'),
                'name' => 'days',
                'type' => 'select',
                'options' => [
                    7 => '7 ' . rex_i18n::msg('phpmailer_archivecron_days'),
                    14 => '14 ' . rex_i18n::msg('phpmailer_archivecron_days'),
                    30 => '30 ' . rex_i18n::msg('phpmailer_archivecron_days'),
                ],
                'default' => 7,
            ],
        ];
    }
}
