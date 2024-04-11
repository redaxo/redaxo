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

    /**
     * used to store information if detour mode is enabled.
     */
    private array $xHeader = [];

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

        rex_extension::registerPoint(new rex_extension_point('PHPMAILER_CONFIG', $this));
    }

    protected function addOrEnqueueAnAddress($kind, $address, $name)
    {
        $addon = rex_addon::get('phpmailer');

        if ($addon->getConfig('detour_mode') && '' != $addon->getConfig('test_address')) {
            if ('to' == $kind) {
                $detourAddress = $addon->getConfig('test_address');

                // store the address so we can use it in the subject later

                // if there has already been a call to addOrEnqueueAnAddress and detour mode is on
                // xHeader['to'] should have already been set
                // therefore we add the address to xHeader['to'] for the subject later
                // and parent::addOrEnqueueAnAddress doesnt need to be called since it would be the test address again

                if (isset($this->xHeader['to'])) {
                    $this->xHeader['to'] .= ', ' . $address;
                    return true;
                }

                $this->xHeader['to'] = $address;

                // Set $address to the detour address
                $address = $detourAddress;
            } else {
                if (isset($this->xHeader[$kind])) {
                    $this->xHeader[$kind] .= ', ' . $address;
                } else {
                    $this->xHeader[$kind] = $address;
                }

                return true;
            }
        }

        return parent::addOrEnqueueAnAddress($kind, $address, $name);
    }

    /**
     * @return bool
     */
    public function send()
    {
        return rex_timer::measure(__METHOD__, function () {
            $addon = rex_addon::get('phpmailer');
            $logging = (int) $addon->getConfig('logging');
            $detourModeActive = $addon->getConfig('detour_mode') && '' !== $addon->getConfig('test_address');

            rex_extension::registerPoint(new rex_extension_point('PHPMAILER_PRE_SEND', $this));

            if ($detourModeActive && isset($this->xHeader['to'])) {
                $this->prepareDetourMode();
            }

            if (!parent::send()) {
                if ($logging) {
                    $this->log('ERROR');
                }
                if ($this->archive) {
                    $this->archive($this->getSentMIMEMessage(), 'not_sent_');
                }
                return false;
            }

            if ($this->archive) {
                $this->archive($this->getSentMIMEMessage());
            }

            if (self::LOG_ALL === $logging) {
                $this->log('OK');
            }

            rex_extension::registerPoint(new rex_extension_point('PHPMAILER_POST_SEND', $this));

            return true;
        });
    }

    private function prepareDetourMode(): void
    {
        $addon = rex_addon::get('phpmailer');
        $this->clearCCs();
        $this->clearBCCs();

        foreach (['to', 'cc', 'bcc', 'ReplyTo'] as $kind) {
            if (isset($this->xHeader[$kind])) {
                $this->addCustomHeader('x-' . $kind, $this->xHeader[$kind]);
            }
        }

        $this->Subject = $addon->i18n('detour_subject', $this->Subject, $this->xHeader['to']);
        $this->xHeader = []; // Bereinigung für die nächste Verwendung
    }

    /**
     * @return void
     */
    public function clearQueuedAddresses($kind)
    {
        parent::clearQueuedAddresses($kind);

        unset($this->xHeader[$kind]);
    }

    /**
     * @return void
     */
    public function clearAllRecipients()
    {
        parent::clearAllRecipients();

        $this->xHeader = [];
    }

    private function log(string $success): void
    {
        $replytos = '';
        if (count($this->getReplyToAddresses()) > 0) {
            $replytos = implode(', ', array_column($this->getReplyToAddresses(), 0));
        }

        $log = rex_log_file::factory(self::logFile(), 2_000_000);
        $data = [
            $success,
            $this->From . ($replytos ? '; reply-to: ' . $replytos : ''),
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
     * @return void
     */
    public function setLog($status)
    {
        $this->setArchive($status);
    }

    /**
     * Enable/disable the mail archive.
     *
     * It overwrites the global `archive` configuration for the current mailer object.
     * @return void
     */
    public function setArchive(bool $status)
    {
        $this->archive = $status;
    }

    private function archive(string $archivedata = '', string $status = ''): void
    {
        $dir = self::logFolder() . '/' . date('Y') . '/' . date('m');
        $count = 1;
        $archiveFile = $dir . '/' . $status . date('Y-m-d_H_i_s') . '.eml';
        while (is_file($archiveFile)) {
            $archiveFile = $dir . '/' . $status . date('Y-m-d_H_i_s') . '_' . (++$count) . '.eml';
        }

        rex_file::put($archiveFile, $archivedata);
    }

    /**
     * Path to mail archive folder.
     */
    public static function logFolder(): string
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

    /**
     * @internal
     */
    public static function errorMail(): void
    {
        $addon = rex_addon::get('phpmailer');
        $logFile = rex_path::log('system.log');
        $sendTime = $addon->getConfig('last_log_file_send_time', 0);
        $lasterrors = $addon->getConfig('last_errors', '');
        $currenterrors = '';
        $timediff = time() - $sendTime;

        if ($timediff <= $addon->getConfig('errormail') || !filesize($logFile)) {
            return;
        }

        $file = rex_log_file::factory($logFile);

        $logevent = false;

        // Start - generate mailbody
        $mailBody = '<h2>Error protocol for: ' . rex::getServerName() . '</h2>';
        $mailBody .= '<style nonce="' . rex_response::getNonce() . '"> .errorbg {background: #F6C4AF; } .eventbg {background: #E1E1E1; } td, th {padding: 5px;} table {width: 100%; border: 1px solid #ccc; } th {background: #b00; color: #fff;} td { border: 0; border-bottom: 1px solid #b00;} </style> ';
        $mailBody .= '<table>';
        $mailBody .= '    <thead>';
        $mailBody .= '        <tr>';
        $mailBody .= '            <th>' . rex_i18n::msg('syslog_timestamp') . '</th>';
        $mailBody .= '            <th>' . rex_i18n::msg('syslog_type') . '</th>';
        $mailBody .= '            <th>' . rex_i18n::msg('syslog_message') . '</th>';
        $mailBody .= '            <th>' . rex_i18n::msg('syslog_file') . '</th>';
        $mailBody .= '            <th>' . rex_i18n::msg('syslog_line') . '</th>';
        $mailBody .= '            <th>' . rex_i18n::msg('syslog_url') . '</th>';
        $mailBody .= '        </tr>';
        $mailBody .= '    </thead>';
        $mailBody .= '    <tbody>';

        /** @var rex_log_entry $entry */
        foreach (new LimitIterator($file, 0, 30) as $entry) {
            $data = $entry->getData();
            $time = rex_formatter::intlDateTime($entry->getTimestamp(), [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]);
            $type = $data[0];
            $message = $data[1];
            $file = $data[2] ?? '';
            $line = $data[3] ?? '';
            $url = $data[4] ?? '';

            $style = '';
            $logtypes = [
                'error',
                'exception',
            ];

            foreach ($logtypes as $logtype) {
                if (false !== stripos($type, $logtype)) {
                    $logevent = true;
                    $style = ' class="errorbg"';
                    $currenterrors .= $entry->getTimestamp() . ' ';
                    break;
                }
            }

            if ('logevent' == $type) {
                $style = ' class="eventbg"';
                $logevent = true;
                $currenterrors .= $entry->getTimestamp() . ' ';
            }

            $mailBody .= '        <tr' . $style . '>';
            $mailBody .= '            <td>' . $time . '</td>';
            $mailBody .= '            <td>' . $type . '</td>';
            $mailBody .= '            <td>' . substr($message, 0, 128) . '</td>';
            $mailBody .= '            <td>' . $file . '</td>';
            $mailBody .= '            <td>' . $line . '</td>';
            $mailBody .= '            <td>' . $url . '</td>';
            $mailBody .= '        </tr>';
        }

        // check if logevent occured then send mail
        if (!$logevent) {
            return;
        }

        if ($lasterrors === $currenterrors || '' == $currenterrors) {
            return;
        }

        $mailBody .= '    </tbody>';
        $mailBody .= '</table>';
        // End - generate mailbody

        $mail = new self();
        $mail->Subject = rex::getServerName() . ' - error report ';
        $mail->Body = $mailBody;
        $mail->AltBody = strip_tags($mailBody);
        $mail->FromName = 'REDAXO error report';
        $mail->addAddress(rex::getErrorEmail());
        $addon->setConfig('last_errors', $currenterrors);
        $addon->setConfig('last_log_file_send_time', time());
        $mail->Send();
    }
}
