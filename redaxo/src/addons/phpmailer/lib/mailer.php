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
    public const LOG_ERRORS = 1;
    public const LOG_ALL = 2;

    /** @var bool */
    private $archive;

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
            if (!parent::send()) {
                if ($addon->getConfig('logging')) {
                    $this->log('ERROR');
                }
                return false;
            }

            if (self::LOG_ALL == $addon->getConfig('logging')) {
                $this->log('OK');
            }
            return true;
        });
    }

    private function log(string $success): void
    {
        $log = new rex_log_file(self::logFile(), 2000000);
        $data = [
            $success,
            $this->From,
            implode(', ', array_column($this->getToAddresses(), 0)),
            $this->Subject,
            trim(str_replace('https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting', '', strip_tags($this->ErrorInfo))),
        ];
        $log->add($data);
    }

    /**
     * @param bool $status
     *
     * @deprecated use `setArchive` instead
     */
    public function setLog($status)
    {
        $this->setArchive($status);
    }

    /**
     * Enable/disable the mail archive.
     *
     * It overwrites the global `archive` configuration for the current mailer object.
     */
    public function setArchive(bool $status)
    {
        $this->archive = $status;
    }

    private function archive(): void
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

    /**
     * Path to mail archive folder.
     */
    public static function logFolder()
    {
        return rex_path::addonData('phpmailer', 'mail_log');
    }

    /**
     * Path to log file.
     */
    public static function logFile(): string
    {
        return rex_path::log('mail.log');
    }
}
