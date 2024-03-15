<?php

use PHPMailer\PHPMailer\PHPMailer;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Log\LogEntry;
use Redaxo\Core\Log\LogFile;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Formatter;
use Redaxo\Core\Util\Timer;

class rex_mailer extends PHPMailer
{
    public const LOG_ERRORS = 1;
    public const LOG_ALL = 2;

    private bool $archive;

    /**
     * used to store information if detour mode is enabled.
     */
    private array $xHeader = [];

    public function __construct($exceptions = false)
    {
        $this->Timeout = 10;
        $this->setLanguage(I18n::getLanguage(), Path::core('vendor/phpmailer/phpmailer/language/'));
        $this->XMailer = 'REXMailer';
        $this->From = Core::getConfig('phpmailer_from');
        $this->FromName = Core::getConfig('phpmailer_fromname');
        $this->ConfirmReadingTo = Core::getConfig('phpmailer_confirmto');
        $this->Mailer = Core::getConfig('phpmailer_mailer');
        $this->Host = Core::getConfig('phpmailer_host');
        $this->Port = Core::getConfig('phpmailer_port');
        $this->CharSet = Core::getConfig('phpmailer_charset');
        $this->WordWrap = Core::getConfig('phpmailer_wordwrap');
        $this->Encoding = Core::getConfig('phpmailer_encoding');
        if (0 == Core::getConfig('phpmailer_priority')) {
            $this->Priority = null;
        } else {
            $this->Priority = Core::getConfig('phpmailer_priority');
        }
        $this->SMTPDebug = Core::getConfig('phpmailer_smtp_debug');
        $this->SMTPSecure = Core::getConfig('phpmailer_smtpsecure');
        $this->SMTPAuth = Core::getConfig('phpmailer_smtpauth');
        $this->SMTPAutoTLS = Core::getConfig('phpmailer_security_mode');
        $this->Username = Core::getConfig('phpmailer_username');
        $this->Password = Core::getConfig('phpmailer_password');

        if ($bcc = Core::getConfig('phpmailer_bcc')) {
            $this->addBCC($bcc);
        }
        $this->archive = Core::getConfig('phpmailer_archive');
        parent::__construct($exceptions);

        rex_extension::registerPoint(new rex_extension_point('PHPMAILER_CONFIG', $this));
    }

    protected function addOrEnqueueAnAddress($kind, $address, $name)
    {
        if (Core::getConfig('phpmailer_detour_mode') && '' != Core::getConfig('phpmailer_test_address')) {
            if ('to' == $kind) {
                $detourAddress = Core::getConfig('phpmailer_test_address');

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
        return Timer::measure(__METHOD__, function () {
            $logging = (int) Core::getConfig('phpmailer_logging');
            $detourModeActive = Core::getConfig('phpmailer_detour_mode') && '' !== Core::getConfig('phpmailer_test_address');

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
        $this->clearCCs();
        $this->clearBCCs();

        foreach (['to', 'cc', 'bcc', 'ReplyTo'] as $kind) {
            if (isset($this->xHeader[$kind])) {
                $this->addCustomHeader('x-' . $kind, $this->xHeader[$kind]);
            }
        }

        $this->Subject = I18n::msg('phpmailer_detour_subject', $this->Subject, $this->xHeader['to']);
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

        $log = LogFile::factory(self::logFile(), 2_000_000);
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
        $dir = self::logFolder() . 'mailer.php/' . date('Y') . '/' . date('m');
        $count = 1;
        $archiveFile = $dir . '/' . $status . date('Y-m-d_H_i_s') . '.eml';
        while (is_file($archiveFile)) {
            $archiveFile = $dir . '/' . $status . date('Y-m-d_H_i_s') . '_' . (++$count) . '.eml';
        }

        File::put($archiveFile, $archivedata);
    }

    /**
     * Path to mail archive folder.
     */
    public static function logFolder(): string
    {
        return Path::coreData('phpmailer/mail_log');
    }

    /**
     * Path to log file.
     */
    public static function logFile(): string
    {
        return Path::log('mail.log');
    }

    /**
     * @internal
     */
    public static function errorMail(): void
    {
        $logFile = Path::log('system.log');
        $sendTime = Core::getConfig('phpmailer_last_log_file_send_time', 0);
        $lasterrors = Core::getConfig('phpmailer_last_errors', '');
        $currenterrors = '';
        $timediff = time() - $sendTime;

        if ($timediff <= Core::getConfig('phpmailer_errormail') || !filesize($logFile)) {
            return;
        }

        $file = LogFile::factory($logFile);

        $logevent = false;

        // Start - generate mailbody
        $mailBody = '<h2>Error protocol for: ' . Core::getServerName() . '</h2>';
        $mailBody .= '<style nonce="' . rex_response::getNonce() . '"> .errorbg {background: #F6C4AF; } .eventbg {background: #E1E1E1; } td, th {padding: 5px;} table {width: 100%; border: 1px solid #ccc; } th {background: #b00; color: #fff;} td { border: 0; border-bottom: 1px solid #b00;} </style> ';
        $mailBody .= '<table>';
        $mailBody .= '    <thead>';
        $mailBody .= '        <tr>';
        $mailBody .= '            <th>' . I18n::msg('syslog_timestamp') . '</th>';
        $mailBody .= '            <th>' . I18n::msg('syslog_type') . '</th>';
        $mailBody .= '            <th>' . I18n::msg('syslog_message') . '</th>';
        $mailBody .= '            <th>' . I18n::msg('syslog_file') . '</th>';
        $mailBody .= '            <th>' . I18n::msg('syslog_line') . '</th>';
        $mailBody .= '            <th>' . I18n::msg('syslog_url') . '</th>';
        $mailBody .= '        </tr>';
        $mailBody .= '    </thead>';
        $mailBody .= '    <tbody>';

        /** @var LogEntry $entry */
        foreach (new LimitIterator($file, 0, 30) as $entry) {
            $data = $entry->getData();
            $time = Formatter::intlDateTime($entry->getTimestamp(), [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]);
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
                    $currenterrors .= $entry->getTimestamp() . ' mailer.php';
                    break;
                }
            }

            if ('logevent' == $type) {
                $style = ' class="eventbg"';
                $logevent = true;
                $currenterrors .= $entry->getTimestamp() . ' mailer.php';
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
        $mail->Subject = Core::getServerName() . ' - error report ';
        $mail->Body = $mailBody;
        $mail->AltBody = strip_tags($mailBody);
        $mail->FromName = 'REDAXO error report';
        $mail->addAddress(Core::getErrorEmail());
        Core::getConfig('phpmailer_last_errors', $currenterrors);
        Core::getConfig('phpmailer_last_log_file_send_time', time());
        $mail->Send();
    }
}
