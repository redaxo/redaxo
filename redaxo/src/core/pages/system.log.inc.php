<?php

/**
 * Verwaltung der Content Sprachen
 * @package redaxo5
 */

// -------------- Defaults
$func       = rex_request('func', 'string');

$warning = '';
$info = '';

$logFile = rex_path::cache('system.log');
if ($func == 'delLog')
{
  // close logger, to free remaining file-handles to syslog
  // so we can safely delete the file
  rex_logger::unregister();

  if(rex_file::delete($logFile))
  {
    $info = rex_i18n::msg('syslog_deleted');
  }
  else
  {
    $warning = rex_i18n::msg('syslog_delete_error');
  }

  rex_logger::register();

} else if ($func == 'readlog')
{
  // clear output-buffer
  while(ob_get_level()) ob_end_clean();

  echo '<html><head></head><body>';

  // log files tend to get very big over time. therefore we read only the last n lines
  $n = 500;
  $fp = fopen($logFile, 'r');
  if($fp)
  {
    // go backwards from the end of the file
    // a line in the logfile has round about 500 chars
    fseek($fp, -1 * $n * 500, SEEK_END);
    // find the next beginning of a line
    fgets($fp);
    // stream all remaining lines
    while(($buf = fgets($fp)) !== false)
    {
      echo $buf;
    }
    fclose($fp);
  }

  echo '
    <span id="endmarker" />
    <script type="text/javascript">
      document.getElementById("endmarker").scrollIntoView(true);
    </script>';
  echo '</body></html>';
  exit();
}

$content = '';

if ($info != '')
  $content .= rex_view::info($info);

if ($warning != '')
  $content .= rex_view::warning($warning);

$content .= '<iframe src="index.php?page='.$page.'&amp;subpage='.$subpage.'&amp;func=readlog" class="rex-log" width="100%" height="500px"></iframe>';

$content .= '
  <form action="index.php" method="post">
    <input type="hidden" name="page" value="'.$page.'" />
    <input type="hidden" name="subpage" value="'.$subpage.'" />
    <input type="hidden" name="func" value="delLog" />
    <input type="submit" name="del_btn" value="'.rex_i18n::msg('syslog_delete').'" data-confirm="'.rex_i18n::msg('delete').'?">
  </form>
  ';

echo rex_view::contentBlock($content,'','blank');


?>
