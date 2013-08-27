<?php

/**
 * Verwaltung der Content Sprachen
 * @package redaxo5
 */

// -------------- Defaults
$func       = rex_request('func', 'string');

$error = '';
$success = '';

$logFile = rex_path::cache('system.log');
if ($func == 'delLog') {
    // close logger, to free remaining file-handles to syslog
    // so we can safely delete the file
    rex_logger::close();

    if (rex_log_file::delete($logFile)) {
        $success = rex_i18n::msg('syslog_deleted');
    } else {
        $error = rex_i18n::msg('syslog_delete_error');
    }

}

$content = '';

if ($success != '') {
    $content .= rex_view::success($success);
}

if ($error != '') {
    $content .= rex_view::error($error);
}

$content .= '
            <table id="rex-table-log" class="rex-table rex-table-middle rex-table-striped">
                <thead>
                    <tr>
                        <th>timestamp</th>
                        <th>type</th>
                        <th>message</th>
                        <th>file</th>
                        <th>line</th>
                    </tr>
                </thead>
                <tbody>';

if ($file = new rex_log_file(rex_path::cache('system.log'))) {
    foreach (new LimitIterator($file, 0, 30) as $entry) {
        /* @var rex_log_entry $entry */
        $data = $entry->getData();
        $content .= '
                    <tr>
                        <td>' . $entry->getTimestamp('%d.%m.%Y %H:%M:%S') . '</td>
                        <td>' . $data[0] . '</td>
                        <td>' . $data[1] . '</td>
                        <td>' . (isset($data[2]) ? $data[2] : '') . '</td>
                        <td>' . (isset($data[3]) ? $data[3] : '') . '</td>
                    </tr>';
    }
}

$content .= '
                </tbody>
            </table>';

$content .= '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="delLog" />';


$formElements = [];

$n = [];
$n['field'] = '<button class="rex-button" type="submit" name="del_btn" data-confirm="' . rex_i18n::msg('delete') . '?">' . rex_i18n::msg('syslog_delete') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/submit.php');


$content .= '
    </form>';

echo rex_view::content('block', $content, '', $params = ['flush' => true]);
