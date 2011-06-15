<?php

/**
 * Function to compensate the deprecated magic_quotes_gpc setting
 *
 * @package redaxo5
 * @version svn:$Id$
 */

if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
{
  $_GET = json_decode(stripslashes(json_encode($_GET, JSON_HEX_APOS)), true);
  $_POST = json_decode(stripslashes(json_encode($_POST, JSON_HEX_APOS)), true);
  $_COOKIE = json_decode(stripslashes(json_encode($_COOKIE, JSON_HEX_APOS)), true);
  $_REQUEST = json_decode(stripslashes(json_encode($_REQUEST, JSON_HEX_APOS)), true);
}