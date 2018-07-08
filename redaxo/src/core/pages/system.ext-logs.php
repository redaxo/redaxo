<?php

/**
 * @package redaxo5
 */

$func = rex_request('func', 'string');
$logFile = ini_get('error_log');
/*
$logFile = rex_request('file', 'string');
$availableLogs = [ini_get('error_log')];

if (!in_array($logFile, $availableLogs)) {
    unset($logFile);
}
*/

$error = '';
$success = '';

if ($func == 'delLog') {
    if (rex_file::delete($logFile)) {
        $success = rex_i18n::msg('extlog_deleted', $logFile);
    } else {
        $error = rex_i18n::msg('extlog_delete_error', $logFile);
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

if (!isset($logFile)) {
    return;
}

$content .= '
            <table class="table table-hover">
                <tbody>';

$file = new SplFileObject($logFile, 'r');
$file->seek(PHP_INT_MAX);
$last_line = $file->key();

$limit = 10;
foreach (new LimitIterator($file, max(0, $last_line - $limit), $last_line) as $logLine) {
    $content .= '
        <tr>
            <td>' . htmlspecialchars($logLine) . '</td>
        </tr>';
}

$content .= '
                </tbody>
            </table>';

$buttons = '';
if (is_writable($logFile)) {
    $formElements = [];

    $n = [];
    $n['field'] = '<button class="btn btn-delete" type="submit" name="del_btn" data-confirm="' . rex_i18n::msg('delete') . '?">' . rex_i18n::msg('extlog_delete', $logFile) . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');
}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('extlog_title', $logFile), false);
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="delLog" />
        <input type="hidden" name="log" value="'. htmlspecialchars($logFile) .'" />
        ' . $content . '
    </form>';

echo $message;
echo $content;
