<?php
/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\phpmailer
 */

class rex_cronjob_mailerpurge extends rex_cronjob
{
    public function purgeMailArchive($dir = self::purgeMailArchive(rex_mailer::logFolder()), $log = '')
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $log .= self::purgeMailArchive($file);
            } elseif ((time() - filemtime($file)) > (60 * 60 * 24 * $this->getParam('time'))) {
                if (rex_file::delete($file)) {
                    $log .= 'deleted file: ' . $file . "\n";
                }
            }
        }

        if ($dir != rex_mailer::logFolder() && is_dir($dir)) {
            if (true == rmdir($dir)) {
                $log .= 'deleted directory: ' . $dir . "\n";
            }
        }
        return $log;
    }

    public function execute()
    {
        if (is_dir(rex_mailer::logFolder())) {
            $purgeMail = '';
            $purgeMail = self::purgeMailArchive();
            if ('' != $purgeMail) {
                $this->setMessage($purgeMail);
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
                'name' => 'time',
                'type' => 'select',
                'options' => [
                    '7' => '7 ' . rex_i18n::msg('phpmailer_archivecron_days'),
                    '14' => '14 ' . rex_i18n::msg('phpmailer_archivecron_days'),
                    '30' => '30 ' . rex_i18n::msg('phpmailer_archivecron_days'),
                ],
                'default' => '7',
                'notice' => rex_i18n::msg('phpmailer_archivecron_notice'),
            ],
        ];
        return $fields;
    }
}
