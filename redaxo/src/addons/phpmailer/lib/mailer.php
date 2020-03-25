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
     * used to store information if detour mode is enabled
     * @var array 
    */
    private $xHeader = [];

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

    /**
     * Add an address to one of the recipient arrays or to the ReplyTo array. Because PHPMailer
     * can't validate addresses with an IDN without knowing the PHPMailer::$CharSet (that can still
     * be modified after calling this function), addition of such addresses is delayed until send().
     * Addresses that have been added already return false, but do not throw exceptions.
     *
     * @param string $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
     * @param string $address The email address to send, resp. to reply to
     * @param string $name
     *
     * @throws Exception
     *
     * @return bool true on success, false if address already used or invalid in some way
     */
    protected function addOrEnqueueAnAddress($kind, $address, $name)
    {
        $addon = rex_addon::get('phpmailer');
        
        /**
         * checks if detour mode is enabled
         * @var bool: $detour
         */

        $detour = true == $addon->getConfig('detour_mode') && '' != $addon->getConfig('test_address');

        if(true == $detour) {

            // handle to 
            if ('to' == $kind) {
            
                $detour_address = $addon->getConfig('test_address');

                // check if address is valid
                if (true == rex_validator::factory()->email($detour_address)) {
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
                    $address = $detour_address;
                
                }

            // handle other $kind
            } else {

                // if the xheader is already set add the address to the string
                if (isset($this->xHeader[$kind])) {
                    $this->xHeader[$kind] .= ', ' . $address;

                // else create a new value
                } else {
                    $this->xHeader[$kind] = $address;
                }

                return true;

            }
         
        }

        // call to parent method
        return parent::addOrEnqueueAnAddress($kind, $address, $name);
    }

    public function send()
    {
        return rex_timer::measure(__METHOD__, function () {
            if ($this->archive) {
                $this->archive();
            }
            $addon = rex_addon::get('phpmailer');

            /**
             * checks if detour mode is enabled
             * @var bool: $detour
             */
            
            $detour = true == $addon->getConfig('detour_mode') && '' != $addon->getConfig('test_address');

            // Clears the CCs and BCCs if detour mode is active
            // Sets Subject of E-Mail to [DETOUR] $subject [$this->xHeader['to']]
            if (true == $detour && isset($this->xHeader['to'])) {
                $this->clearCCs();
                $this->clearBCCs();

                // add x header
                foreach (['to', 'cc', 'bcc', 'ReplyTo'] as $kind) {
                    
                    if (isset($this->xHeader[$kind])) {
                        $this->addCustomHeader('x-' . $kind, $this->xHeader[$kind]);
                    }

                }

                // set custom subject
                $this->Subject = '[' . $addon->i18n('detour_subject_start') . '] ' . $this->Subject;
                
                // add the original to 
                if (isset($this->xHeader['to'])) { 
                    $this->Subject .= ' [' . $addon->i18n('detour_subject_end') . ': ' . $this->xHeader['to'] . ']';
                }
            
                // unset xHeader so it can be used again
                $this->xHeader = [];
            }

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
