<?php

/**
 * Verwaltung der Content Sprachen
 * @package redaxo4
 * @version svn:$Id$
 */

// -------------- Defaults
$func       = rex_request('func', 'string');

$warning = '';
$info = '';

$logFile = $REX['SRC_PATH'] .'/generated/files/system.log';
if ($func == 'delLog')
{
  // close logger, to free remaining file-handles to syslog 
  // so we can safely delete the file 
  rex_logger::getInstance()->close();
  if(unlink($logFile))
  {
    $info = $REX['I18N']->msg('syslog_deleted');
  }
  else
  {
    $warning = $REX['I18N']->msg('syslog_delete_error');
  }
} else if ($func == 'readlog')
{
  // clear output-buffer
  while(ob_get_level()) ob_end_clean();
  
  echo '<html><head></head><body>';
  
  // TODO use rex_send_file (would load entire file in the php-memory!) ?
  readfile($logFile);
  
  echo '
    <span id="endmarker" />
    <script type="text/javascript">
      document.getElementById("endmarker").scrollIntoView(true);
    </script>';
  echo '</body></html>';
  exit();
}

if ($info != '')
  echo rex_info($info);

if ($warning != '')
  echo rex_warning($warning);

?>
<iframe src="index.php?page=<?php echo $page; ?>&amp;subpage=<?php echo $subpage; ?>&amp;func=readlog" class="rex-log" width="100%" height="500px">
</iframe>

<form action="index.php" method="post">
  <input type="hidden" name="page" value="<?php echo $page; ?>" />
  <input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />
  <input type="hidden" name="func" value="delLog" />
  <input type="submit" name="del_btn" value="<?php echo $REX['I18N']->msg('syslog_delete'); ?>" onclick="return confirm('<?php echo $REX['I18N']->msg('delete'); ?>?')">
</form>
