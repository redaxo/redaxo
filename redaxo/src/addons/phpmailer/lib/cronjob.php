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
    protected function purgeMailarchive($days = 7, $dir = '', $log = 0)
    {
        foreach (glob($dir . '/*') as $file) {
	            if (is_dir($file)) {
                $log = + self::purgeMailarchive($days, $file);
            } elseif ((time() - filemtime($file)) > (60 * 60 * 24 * $days)) {
                if (rex_file::delete($file)) {
                    $log++;
                }
            }
        }

        if ($dir != rex_mailer::logFolder() && is_dir($dir)) {
			if(count(glob("$dir/*")) === 0 && true == rmdir($dir))
			{
            $log++;
			}
        }
        return $log;
    }

    public function execute()
    {
        $purgeLog = 0;
        if (is_dir(rex_mailer::logFolder())) {
            $purgeLog = self::purgeMailarchive($this->getParam('days'),rex_mailer::logFolder());
            if (0 != $purgeLog) {
                $this->setMessage(rex_i18n::msg('phpmailer_archivecron_deleted').': ' .$purgeLog);
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
