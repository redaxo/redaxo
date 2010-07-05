<?php

/**
 * REDAXO Version Checker Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

function rex_a657_get_latest_version()
{
  $updateUrl = 'http://www.redaxo.de/de/latestversion';
  
  $latestVersion = rex_a657_open_http_socket($updateUrl, $errno, $errstr, 15);
  if($latestVersion !== false)
  {
    return preg_replace('/[^0-9\.]/', '', $latestVersion);
  }
  
  return false;
}

function rex_a657_check_version()
{
  global $I18N, $REX;
  
  $latestVersion = rex_a657_get_latest_version();
  if(!$latestVersion) return false;
  
  $rexVersion = $REX['VERSION'].'.'.$REX['SUBVERSION'].'.'.$REX['MINORVERSION'];
  if(version_compare($rexVersion, $latestVersion, '>'))
  {
    // Dev version
    $notice = rex_warning($I18N->msg('vchecker_dev_version', $rexVersion));
  }
  else if (version_compare($rexVersion, $latestVersion, '<'))
  {
    // update required
    $notice = rex_warning($I18N->msg('vchecker_old_version', $rexVersion, $latestVersion));
  }
  else
  {
    // current version
    $notice = rex_info($I18N->msg('vchecker_current_version', $rexVersion));
  }
  
  return $notice;
}

function rex_a657_open_http_socket($url, &$errno, &$errstr, $timeout)
{
  $buf = '';
  $parts = parse_url($url);
  $port = isset($parts['port']) ? $parts['port'] : 80;
  $path = isset($parts['path']) ? $parts['path'] : '/';
  
  // use timeout for opening connection
  $fp = fsockopen($parts['host'], $port, $errno, $errstr, $timeout);
  if ($fp)
  {
    // allow write/read timeouts
    stream_set_timeout($fp, $timeout);
    
    $out  = "";
    $out .= "GET ". $path ." HTTP/1.1\r\n";
    $out .= "Host: ". $parts['host'] ."\r\n";
    $out .= "Connection: Close\r\n\r\n";
    
    fwrite($fp, $out);
    
    // check write timeout
    $info = stream_get_meta_data($fp);
    if ($info['timed_out']) {
       return false;
    }

    $httpHead = '';
    while (!feof($fp))
    {
      $buf .= fgets($fp, 512);
      
      if($httpHead == '' && ($headEnd = strpos($buf, "\r\n\r\n")) !== false)
      {
        $httpHead = substr($buf, 0, $headEnd); // extract http header
        $buf = substr($buf, $headEnd+4); // trim buf to contain only http data
      }
    }
    fclose($fp);
    
    $chunked = false;
    foreach(explode("\r\n", $httpHead) as $headPart)
    {
      $headPart = strtolower($headPart);
      if(strpos($headPart, 'http') !== false)
      {
        $mainHeader = explode(' ', $headPart);
        
        if($mainHeader[1] !== '200')
        {
          $errno  = $mainHeader[1];
          $errstr = $mainHeader[2];
          return false;
        }
      }
      else if(strpos($headPart, 'transfer-encoding: chunked') !== false)
      {
        $chunked = true;
      }
    }
    
    if($chunked)
    {
      $buf = unchunkHttp11($buf);
    }
  }
  else
  {
    return false;
  }
  
  return $buf;
}

function unchunkHttp11($data) {
    $fp = 0;
    $outData = '';
    while ($fp < strlen($data)) {
        $rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
        $num = hexdec(trim($rawnum));
        $fp += strlen($rawnum);
        $chunk = substr($data, $fp, $num);
        $outData .= $chunk;
        $fp += strlen($chunk);
    }
    return $outData;
}