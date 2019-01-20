<?php

/**
 * @package redaxo\core\minibar
 */
class rex_minibar_element_syslog extends rex_minibar_element
{
    public function render()
    {
        $status = 'rex-syslog-ok';

        $sysLogFile = rex_logger::getPath();
        $lastModified = filemtime($sysLogFile);
        // "last-seen" will be updated, when the user looks into the syslog
        $lastSeen = rex_session('rex_syslog_last_seen');

        // when the user never looked into the file (e.g. after login), we dont have a timely reference point.
        // therefore we check for changes in the file within the last 24hours
        if (!$lastSeen) {
            if ($lastModified > strtotime("-24 hours")) {
                $status = 'rex-syslog-changed';
            }
        } else if ($lastModified && $lastModified > $lastSeen) {
            $status = 'rex-syslog-changed';
        }

        return
            '<div class="rex-minibar-item">
                <a href="'. rex_url::backendPage('system/log/redaxo') .'">
                    <span class="rex-minibar-icon">
                        <i class="rex-minibar-icon--fa rex-minibar-icon--fa-flag '. $status .'"></i>
                    </span>
                    <span class="rex-minibar-value">
                        System Log
                    </span>
                </a>
        </div>';
    }

    public function getOrientation()
    {
        return rex_minibar_element::RIGHT;
    }
}