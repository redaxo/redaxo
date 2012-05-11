<?php

/**
 * Klasse die Einsprungpunkte zur Erweiterung der Kernfunktionalitaetet bietet.
 *
 * @author Markus Staab
 */
abstract class rex_extension extends rex_factory_base
{
  /**
   * Array aller ExtensionsPoints und deren Extensions
   * @var array
   */
  static private $extensions = array();

  private function __construct(){
    // subclassing not allowed
  }

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
    if(static::hasFactoryClass())
    {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }

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
          static::invokeExtension($func, $local_params);
        }
      }
      else
      {
        foreach (self::$extensions[$extensionPoint] as $ext)
        {
          $func = $ext[0];
          $local_params = array_merge($params, $ext[1]);
          $temp = static::invokeExtension($func, $local_params);
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

  protected static function invokeExtension($function, $params)
  {
    return call_user_func($function, $params);
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
    if(static::hasFactoryClass())
    {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }
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
    if(static::hasFactoryClass())
    {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }
    return !empty (self::$extensions[$extensionPoint]);
  }
}
