<?php

class rex_install_webservice
{
  const
    HOST = 'www.redaxo.org',
    PORT = 80,
    PATH = '/de/_system/_webservice/',
    TIMEOUT = 15,
    REFRESH_CACHE = 600;

  static private $cache;

  static public function getJson($path)
  {
    if(is_array($cache = self::getCache($path)))
    {
      return $cache;
    }
    $path = strpos($path, '?') === false ? rtrim($path, '/') .'/?' : $path .'&';
    $path = self::PATH . $path .'v=4.3'; //rex::getVersion();

    $data = json_decode(self::request('GET', $path), true);
    self::setCache($path, $data);
    return $data;
  }

  static public function getZip($path)
  {
    $path = parse_url($path);
    $content = self::request('GET', $path['path'], $path['host']);
    $file = rex_path::addonData('install', 'temp/'. md5($path['path']).'.zip');
    rex_file::put($file, $content);
    $zip = new dUnzip2($file);
    return $zip;
  }

  static private function getCache($path)
  {
    if(self::$cache === null)
    {
      self::$cache = (array) rex_file::getCache(rex_path::cache('install/webservice.cache'));
    }
    if(isset(self::$cache[$path]) && self::$cache[$path]['stamp'] > time() - self::REFRESH_CACHE)
    {
      return self::$cache[$path]['data'];
    }
    return null;
  }

  static private function setCache($path, $data)
  {
    self::$cache[$path]['stamp'] = time();
    self::$cache[$path]['data'] = $data;
    rex_file::putCache(rex_path::cache('install/webservice.cache'), self::$cache);
  }

  static private function request($method, $path, $host = self::HOST)
  {
    if(!($fp = @fsockopen($host, self::PORT)))
    {
      return false;
    }

    stream_set_timeout($fp, self::TIMEOUT);

    $eol = "\r\n";
    $out  = "$method $path HTTP/1.1$eol";
    $out .= 'Host: '. self::HOST . $eol;
    $out .= 'Connection: Close'. $eol . $eol;

    fwrite($fp, $out);

    $meta = stream_get_meta_data($fp);
    if($meta['timed_out'])
    {
      fclose($fp);
      return false;
    }

    $header = '';
    $content = '';
    while(!feof($fp))
    {
      $content .= fgets($fp);

      if($header == '' && ($headEnd = strpos($content, $eol.$eol)) !== false)
      {
        $header = substr($content, 0, $headEnd); // extract http header
        $content = substr($content, $headEnd+4); // trim buf to contain only http data
      }
    }

    fclose($fp);

    if(strpos($header, 'HTTP/1.1 200') !== 0)
    {
      return false;
    }

    return $content;
  }
}