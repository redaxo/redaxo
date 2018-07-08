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

if (!isset($logFile)) {
    return;
}

$content = '
            <table class="table table-hover">
                <tbody>';

$file = new SplFileObject($logFile, 'r');
$file->seek(PHP_INT_MAX);
$last_line = $file->key();

$limit = 10;
foreach (new LimitIterator($file, $last_line - $limit, $last_line) as $logLine) {
    $content .= '
        <tr>
            <td>' . htmlspecialchars($logLine) . '</td>
        </tr>';
}

$content .= '
                </tbody>
            </table>';

$buttons = '';
// XXX add "open with editor" button

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('extlog_title', $logFile), false);
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
