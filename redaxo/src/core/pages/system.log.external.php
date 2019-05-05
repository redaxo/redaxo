<?php

/**
 * @package redaxo5
 */

$func = rex_request('func', 'string');

if (!isset($logFile)) {
    $logFile = ini_get('error_log');
}

$content = '
            <table class="table table-hover">
                <tbody>';

$buttons = '';
if (!is_file($logFile) || !is_readable($logFile) || filesize($logFile) <= 0) {
    $content .= '<tr><td>'. rex_i18n::msg('syslog_empty') .'</td></tr>';
} else {
    // TODO make this more effienct with things like rex_log_file->next()
    $file = new SplFileObject($logFile, 'r');
    $file->seek(PHP_INT_MAX);
    $last_line = $file->key();

    $limit = 30;
    try {
        $lines = iterator_to_array(new LimitIterator($file, max(0, $last_line - $limit), $last_line));
    } catch (OutOfBoundsException $exception) {
        // handle logfiles which contain a single line of text, no newlines.
        // "Cannot seek to 0 which is behind offset 0 plus count 0"
        $lines = file($logFile);
    }
    foreach (array_reverse($lines) as $logLine) {
        if (empty(trim($logLine))) {
            continue;
        }

        $content .= '
        <tr>
            <td>' . rex_escape($logLine) . '</td>
        </tr>';
    }

    if ($url = rex_editor::factory()->getUrl($logFile, $last_line)) {
        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-save" href="'. $url .'">' . rex_i18n::msg('system_editor_open_file', basename($logFile)) . '</a>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');
    }
}

$content .= '
                </tbody>
            </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('syslog_title', $logFile), false);
$fragment->setVar('content', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
