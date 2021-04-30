<?php

/**
 * @package redaxo5
 */

$error = '';
$success = '';

$func = rex_request('func', 'string');
$logFile = rex_logger::getPath();

$csrfToken = rex_csrf_token::factory('system');

if ($func && !$csrfToken->isValid()) {
    $error = rex_i18n::msg('csrf_token_invalid');
} elseif ('delLog' == $func) {
    // close logger, to free remaining file-handles to syslog
    // so we can safely delete the file
    rex_logger::close();

    if (rex_log_file::delete($logFile)) {
        $success = rex_i18n::msg('syslog_deleted');
    } else {
        $error = rex_i18n::msg('syslog_delete_error');
    }
} elseif ('download' == $func && is_file($logFile)) {
    rex_response::sendFile($logFile, 'application/octet-stream', 'attachment');
    exit;
}

$message = '';
$content = '';

if ('' != $success) {
    $message .= rex_view::success($success);
}

if ('' != $error) {
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

$editor = rex_editor::factory();

$file = new rex_log_file($logFile);
foreach (new LimitIterator($file, 0, 100) as $entry) {
    /** @var rex_log_entry $entry */
    $data = $entry->getData();

    $class = strtolower($data[0]);
    $class = ('notice' == $class || 'warning' == $class || 'success' == $class || 'info' == $class) ? $class : 'error';

    $path = '';
    if (isset($data[2])) {
        $path = rex_escape($data[2]);

        if ($url = $editor->getUrl(rex_path::base($data[2]), $data[3] ?? 1)) {
            $path = '<a href="'.$url.'">'.$path.'</a>';
        }
    }

    $content .= '
                <tr class="rex-state-' . $class . '">
                    <td data-title="' . rex_i18n::msg('syslog_timestamp') . '">' . $entry->getTimestamp('%d.%m.%Y %H:%M:%S') . '</td>
                    <td data-title="' . rex_i18n::msg('syslog_type') . '"><div class="rex-word-break">' . rex_escape($data[0]) . '</div></td>
                    <td data-title="' . rex_i18n::msg('syslog_message') . '"><div class="rex-word-break">' . nl2br(rex_escape($data[1])) . '</div></td>
                    <td data-title="' . rex_i18n::msg('syslog_file') . '"><div class="rex-word-break">' . $path . '</div></td>
                    <td class="rex-table-number" data-title="' . rex_i18n::msg('syslog_line') . '">' . (isset($data[3]) ? rex_escape($data[3]) : '') . '</td>
                </tr>';
}

$content .= '
                </tbody>
            </table>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-delete" type="submit" name="del_btn" data-confirm="' . rex_i18n::msg('delete') . '?">' . rex_i18n::msg('syslog_delete') . '</button>';
$formElements[] = $n;

if ($url = $editor->getUrl($logFile, 0)) {
    $n = [];
    $n['field'] = '<a class="btn btn-save" href="'. $url .'">' . rex_i18n::msg('system_editor_open_file', rex_path::basename($logFile)) . '</a>';
    $formElements[] = $n;
}

if (is_file($logFile)) {
    $url = rex_url::currentBackendPage(['func' => 'download'] + $csrfToken->getUrlParams());
    $n = [];
    $n['field'] = '<a class="btn btn-save" href="'. $url .'" download>' . rex_i18n::msg('syslog_download', rex_path::basename($logFile)) . '</a>';
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('syslog_title', $logFile), false);
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="delLog" />
        ' . $csrfToken->getHiddenField() . '
        ' . $content . '
    </form>';

echo $message;
echo $content;
