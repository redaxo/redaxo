<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\phpmailer
 */

class rex_mailer extends PHPMailer
{
    public function __construct($exceptions = false)
    {
        $addon = rex_addon::get('phpmailer');
        $this->XMailer = 'REXMailer';
        $this->From = $addon->getConfig('from');
        $this->FromName = $addon->getConfig('fromname');
        $this->ConfirmReadingTo = $addon->getConfig('confirmto');
        $this->Mailer = $addon->getConfig('mailer');
        $this->Host = $addon->getConfig('host');
        $this->Port = $addon->getConfig('port');
        $this->CharSet = $addon->getConfig('charset');
        $this->WordWrap = $addon->getConfig('wordwrap');
        $this->Encoding = $addon->getConfig('encoding');
        if ($addon->getConfig('priority') == 0) {
            $this->Priority = null;
        } else {
            $this->Priority = $addon->getConfig('priority');
        }
        $this->Backup = $addon->getConfig('backup');
        $this->SMTPDebug = $addon->getConfig('smtp_debug');
        $this->SMTPSecure = $addon->getConfig('smtpsecure');
        $this->SMTPAuth = $addon->getConfig('smtpauth');
        $this->Username = $addon->getConfig('username');
        $this->Password = $addon->getConfig('password');

        if ($bcc = $addon->getConfig('bcc')) {
            $this->AddBCC($bcc);
        }

        $this->PluginDir = $addon->getPath('lib/phpmailer/');

        parent::__construct($exceptions);
    }

    public function send()
    {
        if (isset($this->Backup) && $this->Backup) {
            $this->backup();
        }
        return parent::send();
    }

    public function setBackup($status = true)
    {
        $this->Backup = $status;
    }

    private function backup()
    {
        $content = '<!-- '.PHP_EOL.date('d.m.Y H:i:s').PHP_EOL;
        $content .= 'From : '.$this->From.PHP_EOL;
        $content .= 'To : '.implode(', ', array_column($this->getToAddresses(), 0)).PHP_EOL;
        $content .= 'Subject : '.$this->Subject.PHP_EOL;
        $content .= ' -->'.PHP_EOL;
        $content .= $this->Body;

        $dir = self::backupFolder().'/'.date('Y').'/'.date('m');

        $count = 1;
        $backupFile = $dir.'/'.date('Y-m-d_H_i_s').'.html';
        while (file_exists($backupFile)) {
            $backupFile = $dir.'/'.date('Y-m-d_H_i_s').'_'.(++$count).'.html';
        }

        rex_file::put($backupFile, $content);
    }

    public static function backupFolder()
    {
        return rex_path::addonData('phpmailer', 'mail_backup');
    }
}
