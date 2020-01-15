<?php

/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo\phpmailer
 */

use PHPMailer\PHPMailer\PHPMailer;

class rex_mailer extends PHPMailer
{
    private $log;

    public function __construct($exceptions = false)
    {
        $addon = rex_addon::get('phpmailer');
        $this->Timeout = 10;
        $this->setLanguage(rex_i18n::getLanguage(), $addon->getPath('vendor/phpmailer/phpmailer/language/'));
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
        if (0 == $addon->getConfig('priority')) {
            $this->Priority = null;
        } else {
            $this->Priority = $addon->getConfig('priority');
        }
        $this->SMTPDebug = $addon->getConfig('smtp_debug');
        $this->SMTPSecure = $addon->getConfig('smtpsecure');
        $this->SMTPAuth = $addon->getConfig('smtpauth');
        $this->SMTPAutoTLS = $addon->getConfig('security_mode');
        $this->Username = $addon->getConfig('username');
        $this->Password = $addon->getConfig('password');

        if ($bcc = $addon->getConfig('bcc')) {
            $this->addBCC($bcc);
        }
        $this->archive = $addon->getConfig('archive');
        parent::__construct($exceptions);
    }

    public function send()
    {
        return rex_timer::measure(__METHOD__, function () {
            if ($this->archive) {
                $this->archive();
            }
            $addon = rex_addon::get('phpmailer');
            if (!parent::send() && 0 != $addon->getConfig('logging')) {
                $this->toMailerLog('ERROR');
                return false;
            }

            if (2 == $addon->getConfig('logging')) {
                $this->toMailerLog('OK');
            }
            return true;
        });
    }

    /**
     * @param string $success
     */
    public function toMailerLog($succsess)
    {
        $log = new rex_log_file(rex_path::addonData('phpmailer', 'mail.log'), 2000000);
        $data = [
            ($succsess),
            ($this->From.PHP_EOL),
            (implode(', ', array_column($this->getToAddresses(), 0)).PHP_EOL),
            (str_replace('|', ':pipe:', $this->Subject.PHP_EOL)),
            strip_tags($this->ErrorInfo),
        ];
        $log->add($data);
    }

    /**
     * @param bool $status
     */
    public function setArchive($status)
    {
        $this->archive = $status;
    }

    private function archive()
    {
        $content = '<!-- '.PHP_EOL.date('d.m.Y H:i:s').PHP_EOL;
        $content .= 'From : '.$this->From.PHP_EOL;
        $content .= 'To : '.implode(', ', array_column($this->getToAddresses(), 0)).PHP_EOL;
        $content .= 'Subject : '.$this->Subject.PHP_EOL;
        $content .= ' -->'.PHP_EOL;
        $content .= $this->Body;

        $dir = self::logFolder().'/'.date('Y').'/'.date('m');

        $count = 1;
        $archiveFile = $dir.'/'.date('Y-m-d_H_i_s').'.html';
        while (file_exists($archiveFile)) {
            $archiveFile = $dir.'/'.date('Y-m-d_H_i_s').'_'.(++$count).'.html';
        }

        rex_file::put($archiveFile, $content);
    }

    public static function logFolder()
    {
        return rex_path::addonData('phpmailer', 'mail_log');
    }

    public static function logFile()
    {
        return rex_path::addonData('phpmailer', 'mail.log');
    }
}
