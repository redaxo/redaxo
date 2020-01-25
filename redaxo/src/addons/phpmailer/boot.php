<?php
/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$addon = rex_addon::get('phpmailer');

if (!$addon->hasConfig('errormail')) {
    $addon->setConfig('errormail', 0);
}

if (!$addon->hasConfig('security_mode')) {
    $addon->setConfig('security_mode', true); // true = AutoTLS
}

if (!rex::isBackend() && 0 != $addon->getConfig('errormail')) {
    rex_extension::register('RESPONSE_SHUTDOWN', static function (rex_extension_point $ep) use ($addon) {
        $logFile = rex_path::log('system.log');
        $sendTime = $addon->getConfig('last_log_file_send_time', 0);
        $timediff = '';
        $fatalerror = false;
        $logevent = false;
        $timediff = time() - $sendTime;
        if ($timediff > $addon->getConfig('errormail') && filesize($logFile) > 0 && $file = new rex_log_file($logFile)) {
            //Start - generate mailbody
            $mailBody = '<h2>Error protocol for: ' . rex::getServerName() . '</h2>';
            $mailBody .= '<style> .errorbg {background: #F6C4AF; } .eventbg {background: #E1E1E1; } td, th {padding: 5px;} table {width: 100%; border: 1px solid #ccc; } th {background: #b00; color: #fff;} td { border: 0; border-bottom: 1px solid #b00;} </style> ';
            $mailBody .= '<table>';
            $mailBody .= '    <thead>';
            $mailBody .= '        <tr>';
            $mailBody .= '            <th>' . rex_i18n::msg('syslog_timestamp') . '</th>';
            $mailBody .= '            <th>' . rex_i18n::msg('syslog_type') . '</th>';
            $mailBody .= '            <th>' . rex_i18n::msg('syslog_message') . '</th>';
            $mailBody .= '            <th>' . rex_i18n::msg('syslog_file') . '</th>';
            $mailBody .= '            <th>' . rex_i18n::msg('syslog_line') . '</th>';
            $mailBody .= '        </tr>';
            $mailBody .= '    </thead>';
            $mailBody .= '    <tbody>';
            foreach (new LimitIterator($file, 0, 30) as $entry) {
                /** @var rex_log_entry $entry */
                $data = $entry->getData();
                $style = '';
                $logtypes = [
                    'error',
                    'exception',
                ];

                foreach ($logtypes as $type) {
                    if (false !== stripos($data[0], $type)) {
                        $logevent = true;
                        $style = ' class="errorbg"';
                        break;
                    }
                }

                if ('logevent' == $data[0]) {
                    $style = ' class="eventbg"';
                    $logevent = true;
                }
                $mailBody .= '        <tr' . $style . '>';
                $mailBody .= '            <td>' . $entry->getTimestamp('%d.%m.%Y %H:%M:%S') . '</td>';
                $mailBody .= '            <td>' . $data[0] . '</td>';
                $mailBody .= '            <td>' . substr(rex_escape($data[1]), 0, 128) . '</td>';
                $mailBody .= '            <td>' . ($data[2] ?? '') . '</td>';
                $mailBody .= '            <td>' . ($data[3] ?? '') . '</td>';
                $mailBody .= '        </tr>';
            }
            // check if logevent occured then send mail
            if (true == $logevent) {
                $mailBody .= '    </tbody>';
                $mailBody .= '</table>';
                //End - generate mailbody
                $mail = new rex_mailer();
                $mail->Subject = rex::getServerName() . ' - error report ';
                $mail->Body = $mailBody;
                $mail->AltBody = strip_tags($mailBody);
                $mail->setFrom(rex::getErrorEmail(), 'REDAXO error report');
                $mail->addAddress(rex::getErrorEmail());
                $addon->setConfig('last_log_file_send_time', time());
                if ($mail->Send()) {
                    // mail has been sent
                }
            }
            // close logger, to free remaining file-handles to syslog
            rex_logger::close();
            //End  send mail
        }
    });
}
if ('system' == rex_be_controller::getCurrentPagePart(1)) {
    rex_system_setting::register(new rex_system_setting_phpmailer_errormail());
}
