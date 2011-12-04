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

    $data = null;
    try
    {
      $socket = new rex_socket(self::HOST, $fullpath, self::PORT);
      $socket->doGet();
      if($socket->getStatus() == 200)
      {
        $data = json_decode($socket->getBody(), true);
        if(isset($data['error']) && is_string($data['error']))
          throw new rex_exception(rex_i18n::msg('install_webservice_error') .'<br />'. $data['error']);
        self::setCache($path, $data);
        return $data;
      }
    }
    catch(rex_exception $e) {}

    throw new rex_exception(rex_i18n::msg('install_webservice_unreachable'));
  }

  static public function getArchive($filename)
  {
    try
    {
      $socket = new rex_socket(self::HOST, self::FILES . $filename, self::PORT);
      $socket->doGet();
      if($socket->getStatus() == 200)
      {
        $file = rex_path::cache('install/'. md5($filename) .'.'. rex_file::extension($filename));
        rex_file::put($file, '');
        $fp = fopen($file, 'w');
        $socket->writeBodyTo($fp);
        fclose($fp);
        return $file;
      }
    }
    catch(rex_exception $e) {}

    throw new rex_exception(rex_i18n::msg('install_archive_unreachable'));
  }

  static private function getCache($path)
  {
    if(self::$cache === null)
    {
      foreach((array) rex_file::getCache(rex_path::cache('install/webservice.cache')) as $p => $cache)
      {
        if($cache['stamp'] > time() - self::REFRESH_CACHE)
          self::$cache[$p] = $cache;
      }
    }
    if(isset(self::$cache[$path]))
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