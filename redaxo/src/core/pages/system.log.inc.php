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

  if (rex_file::delete($logFile)) {
    $success = rex_i18n::msg('syslog_deleted');
  } else {
    $error = rex_i18n::msg('syslog_delete_error');
  }

} elseif ($func == 'readlog') {
  // clear output-buffer
  while (ob_get_level()) ob_end_clean();

  echo '<html><head><style type="text/css">div:nth-child(even) {margin-bottom: 1em;}</style></head><body><code>';

  // log files tend to get very big over time. therefore we read only the last n lines
  $n = 500;
  $fp = fopen($logFile, 'r');
  if ($fp) {
    // go backwards from the end of the file
    // a line in the logfile has round about 500 chars
    fseek($fp, -1 * $n * 500, SEEK_END);
    // find the next beginning of a line
    fgets($fp);
    // stream all remaining lines
    while (($buf = fgets($fp)) !== false) {
      echo $buf;
    }
    fclose($fp);
  }

  echo '
    </code>
    <span id="endmarker" />
    <script type="text/javascript">
      document.getElementById("endmarker").scrollIntoView(true);
    </script>';
  echo '</body></html>';
  exit();
}

$content = '';

if ($success != '')
  $content .= rex_view::success($success);

if ($error != '')
  $content .= rex_view::error($error);

$content .= '<iframe src="' . rex_url::currentBackendPage(array('func' => 'readlog')) . '" class="rex-log" width="100%" height="500px"></iframe>';



$content .= '
  <form action="' . rex_url::currentBackendPage() . '" method="post">
    <input type="hidden" name="func" value="delLog" />';


$formElements = array();

$n = array();
$n['field'] = '<button class="rex-button" type="submit" name="del_btn" data-confirm="' . rex_i18n::msg('delete') . '?">' . rex_i18n::msg('syslog_delete') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/submit.tpl');


$content .= '
  </form>';

echo rex_view::contentBlock($content);
