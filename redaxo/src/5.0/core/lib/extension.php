<?php

class rex_extension
{
  static private $extensions = array();
  /**
   * Definiert einen Extension Point
   *
   * @param $extensionPoint Name des ExtensionPoints
   * @param $subject Objekt/Variable die beeinflusst werden soll
   * @param $params Parameter für die Callback-Funktion
   */
  static public function registerPoint($extensionPoint, $subject = '', $params = array (), $read_only = false)
  {
    $result = $subject;

    if (!is_array($params))
    {
      $params = array ();
    }

    // Name des EP als Parameter mit übergeben
    $params['extension_point'] = $extensionPoint;

    if (isset (self::$extensions[$extensionPoint]) && is_array(self::$extensions[$extensionPoint]))
    {
      $params['subject'] = $subject;
      if ($read_only)
      {
        foreach (self::$extensions[$extensionPoint] as $ext)
        {
          $func = $ext[0];
          $local_params = array_merge($params, $ext[1]);
          rex_call_func($func, $local_params);
        }
      }
      else
      {
        foreach (self::$extensions[$extensionPoint] as $ext)
        {
          $func = $ext[0];
          $local_params = array_merge($params, $ext[1]);
          $temp = rex_call_func($func, $local_params);
          // Rückgabewert nur auswerten wenn auch einer vorhanden ist
          // damit $params['subject'] nicht verfälscht wird
          // null ist default Rückgabewert, falls kein RETURN in einer Funktion ist
          if($temp !== null)
          {
            $result = $temp;
            $params['subject'] = $result;
          }
        }
      }
    }
    return $result;
  }

  /**
   * Definiert eine Callback-Funktion, die an dem Extension Point $extension aufgerufen wird
   *
   * @param $extension Name des ExtensionPoints
   * @param $function Name der Callback-Funktion
   * @param [$params] Array von zusätzlichen Parametern
   */
  static public function register($extensionPoint, $callable, $params = array())
  {
    if(!is_array($params)) $params = array();
    self::$extensions[$extensionPoint][] = array($callable, $params);
  }

  /**
   * Prüft ob eine extension für den angegebenen Extension Point definiert ist
   *
   * @param $extensionPoint Name des ExtensionPoints
   */
  static public function isRegistered($extensionPoint)
  {
    return !empty (self::$extensions[$extensionPoint]);
  }

  /**
   * Gibt ein Array mit Namen von Extensions zurück, die am angegebenen Extension Point definiert wurden
   *
   * @param $extensionPoint Name des ExtensionPoints
   */
  static public function getRegisteredExtensions($extensionPoint)
  {
    if(rex_extension::isRegistered($extensionPoint))
    {
      return self::$extensions[$extensionPoint][0];
    }
    return array();
  }
}