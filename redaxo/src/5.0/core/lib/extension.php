<?php

/**
 * Klasse die Einsprungpunkte zur Erweiterung der Kernfunktionalitaetet bietet.
 * 
 * @author Markus Staab
 */
abstract class rex_extension
{
  private function __construct(){
    // subclassing not allowed
  }
  
  /**
   * Array aller ExtensionsPoints und deren Extensions
   * @var array
   */
  static private $extensions = array();
  
  /**
   * Definiert einen Extension Point
   *
   * @param $extensionPoint Name des ExtensionPoints
   * @param $subject Objekt/Variable die beeinflusst werden soll
   * @param $params Parameter für die Callback-Funktion
   * 
   * @return mixed $subject, ggf. manipuliert durch registrierte Extensions.
   */
  static public function registerPoint($extensionPoint, $subject = '', array $params = array (), $read_only = false)
  {
    $result = $subject;

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
  static public function register($extensionPoint, $callable, array $params = array())
  {
    self::$extensions[$extensionPoint][] = array($callable, $params);
  }

  /**
   * Prüft ob eine extension für den angegebenen Extension Point definiert ist
   *
   * @param $extensionPoint Name des ExtensionPoints
   * 
   * @return boolean True, wenn eine Extension für den uebergeben ExtensionPoint definiert ist, sonst False
   */
  static public function isRegistered($extensionPoint)
  {
    return !empty (self::$extensions[$extensionPoint]);
  }

  /**
   * Gibt ein Array mit Namen von Extensions zurück, die am angegebenen Extension Point definiert wurden
   *
   * @param $extensionPoint Name des ExtensionPoints
   * 
   * @return array Ein array von registrierten Extensions
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