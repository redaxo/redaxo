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

if($func == 'readlog')
{
  // clear output-buffer
  while(ob_get_level()) ob_end_clean();
  
  echo '<html><head></head><body>';
  
  // TODO use rex_send_file (would load entire file in the php-memory!) ?
  readfile($REX['SRC_PATH'] .'/generated/files/system.log');
  
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


echo '<iframe src="index.php?page=specials&amp;subpage=log&amp;func=readlog" class="rex-log" width="100%" height="500px" />';
