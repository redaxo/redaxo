<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\phpmailer
 */

class rex_cronjob_mailer_purge extends rex_cronjob
{    
    /**
     * purgeMailarchive
     *
     * @param  int $days
     * @param  mixed $dir
     * @param  int $log
     * @return void
     */
    protected function purgeMailarchive($days = 7, $dir = '', $log = 0)
    {
        $files = glob($dir . '/*');
        if ($files)
        {    
        foreach ($files as $file) {
            if (is_dir($file)) {
                $log = $log + self::purgeMailarchive($days, $file);
            } elseif ((time() - filemtime($file)) > (60 * 60 * 24 * $days)) {
                if (rex_file::delete($file)) {
                    ++$log;
                }
            }
        }

        if ($dir != rex_mailer::logFolder() && is_dir($dir)) {
            if (0 === count(glob("$dir/*")) && true == rmdir($dir)) {
                ++$log;
            }
        }
        }       
        return $log;
    }
        
 
    public function execute()
    {
        $purgeLog = 0;
        if (is_dir(rex_mailer::logFolder())) {
            $purgeLog = self::purgeMailarchive($this->getParam('days'), rex_mailer::logFolder());
            if (0 != $purgeLog) {
                $this->setMessage('Objekte gelöscht: '.$purgeLog);
                return true;
            }
            $this->setMessage(rex_i18n::msg('phpmailer_archivecron_nothing_to_delete'));
            return true;
        }
        $this->setMessage(rex_i18n::msg('phpmailer_archivecron_folder_not_found'));
        return false;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('phpmailer_archivecron');
    }

    public function getParamFields()
    {
        $fields = [
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
                'notice' => rex_i18n::msg('phpmailer_archivecron_notice'),
            ],
        ];
        return $fields;
    }
}

