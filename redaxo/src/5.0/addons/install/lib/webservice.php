<?php

class rex_install_webservice
{
  const
    HOST = 'meyerharlan.de',
    PORT = 80,
    PATH = '/rex-webservice/',
    FILES = '/rex-webservice/files/',
    REFRESH_CACHE = 600;

  static private $cache;

  static public function getJson($path)
  {
    if(is_array($cache = self::getCache($path)))
    {
      return $cache;
    }
    $fullpath = strpos($path, '?') === false ? rtrim($path, '/') .'/?' : $path .'&';
    $fullpath = self::PATH . $fullpath .'v='. rex::getVersion();

    $data = array();
    try
    {
      $socket = new rex_socket(self::HOST, $fullpath, self::PORT);
      $socket->doGet();
      if($socket->getStatus() == 200)
      {
        $data = json_decode($socket->getBody(), true);
      }
    }
    catch(rex_exception $e) {}

    self::setCache($path, $data);
    return $data;
  }

  static public function getZip($filename)
  {
    try
    {
      $socket = new rex_socket(self::HOST, self::FILES . $filename, self::PORT);
      $socket->doGet();
      if($socket->getStatus() == 200)
      {
        $content = $socket->getBody();
        $file = rex_path::addonData('install', 'temp/'. md5($filename).'.zip');
        rex_file::put($file, $content);
        $zip = new dUnzip2($file);
        return $zip;
      }
    }
    catch(rex_exception $e) {}

    return null;
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
}