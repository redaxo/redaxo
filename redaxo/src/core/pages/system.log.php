<?php

/**
 * Verwaltung der Content Sprachen.
 *
 * @package redaxo5
 */

// -------------- Defaults
$func = rex_request('func', 'string');

$error = '';
$success = '';

$logFile = rex_logger::getPath();
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
$message = '';
$content = '';

if ($success != '') {
    $message .= rex_view::success($success);
}

if ($error != '') {
    $message .= rex_view::error($error);
}

$content .= '
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>' . rex_i18n::msg('syslog_timestamp') . '</th>
                        <th>' . rex_i18n::msg('syslog_type') . '</th>
                        <th>' . rex_i18n::msg('syslog_message') . '</th>
                        <th>' . rex_i18n::msg('syslog_file') . '</th>
                        <th class="rex-table-number">' . rex_i18n::msg('syslog_line') . '</th>
                    </tr>
                </thead>
                <tbody>';

if ($file = new rex_log_file($logFile)) {
    foreach (new LimitIterator($file, 0, 30) as $entry) {
        /* @var rex_log_entry $entry */
        $data = $entry->getData();

        $class = strtolower($data[0]);
        $class = ($class == 'notice' || $class == 'warning') ? $class : 'error';

        $content .= '
                    <tr class="rex-state-' . $class . '">
                        <td data-title="' . rex_i18n::msg('syslog_timestamp') . '">' . $entry->getTimestamp('%d.%m.%Y %H:%M:%S') . '</td>
                        <td data-title="' . rex_i18n::msg('syslog_type') . '">' . htmlspecialchars($data[0]) . '</td>
                        <td data-title="' . rex_i18n::msg('syslog_message') . '">' . nl2br(htmlspecialchars($data[1])) . '</td>
                        <td data-title="' . rex_i18n::msg('syslog_file') . '"><div class="rex-word-break">' . (isset($data[2]) ? htmlspecialchars($data[2]) : '') . '</div></td>
                        <td class="rex-table-number" data-title="' . rex_i18n::msg('syslog_line') . '">' . (isset($data[3]) ? htmlspecialchars($data[3]) : '') . '</td>
                    </tr>';
    }
}

$content .= '
                </tbody>
            </table>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-delete" type="submit" name="del_btn" data-confirm="' . rex_i18n::msg('delete') . '?">' . rex_i18n::msg('syslog_delete') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('syslog'), false);
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="delLog" />
        ' . $content . '
    </form>';

echo $message;
echo $content;
