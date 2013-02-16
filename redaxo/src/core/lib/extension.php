<?php

/**
 * Klasse die Einsprungpunkte zur Erweiterung der Kernfunktionalitaetet bietet.
 *
 * @author Markus Staab
 */
abstract class rex_extension extends rex_factory_base
{
  const
    EARLY = -1,
    NORMAL = 0,
    LATE = 1;

  /**
   * Array aller ExtensionsPoints und deren Extensions
   * @var array
   */
  static private $extensions = array();

  private function __construct()
  {
    // subclassing not allowed
  }

  /**
   * Definiert einen Extension Point
   *
   * @param string $extensionPoint Name des ExtensionPoints
   * @param mixed  $subject        Objekt/Variable die beeinflusst werden soll
   * @param array  $params         Parameter für die Callback-Funktion
   * @param bool   $read_only
   * @return mixed $subject, ggf. manipuliert durch registrierte Extensions.
   */
  static public function registerPoint($extensionPoint, $subject = '', array $params = array(), $read_only = false)
  {
    if (static::hasFactoryClass()) {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }

    $result = $subject;

    // Name des EP als Parameter mit übergeben
    $params['extension_point'] = $extensionPoint;

    if (isset (self::$extensions[$extensionPoint]) && is_array(self::$extensions[$extensionPoint])) {
      $params['subject'] = $subject;
      foreach (array(self::EARLY, self::NORMAL, self::LATE) as $level) {
        if (isset(self::$extensions[$extensionPoint][$level]) && is_array(self::$extensions[$extensionPoint][$level])) {
          if ($read_only) {
            foreach (self::$extensions[$extensionPoint][$level] as $ext) {
              $func = $ext[0];
              $local_params = array_merge($params, $ext[1]);
              static::invokeExtension($func, $local_params);
            }
          } else {
            foreach (self::$extensions[$extensionPoint][$level] as $ext) {
              $func = $ext[0];
              $local_params = array_merge($params, $ext[1]);
              $temp = static::invokeExtension($func, $local_params);
              // Rückgabewert nur auswerten wenn auch einer vorhanden ist
              // damit $params['subject'] nicht verfälscht wird
              // null ist default Rückgabewert, falls kein RETURN in einer Funktion ist
              if ($temp !== null) {
                $result = $temp;
                $params['subject'] = $result;
              }
            }
          }
        }
      }
    }
    return $result;
  }

  static protected function invokeExtension($function, $params)
  {
    return call_user_func($function, $params);
  }

  /**
   * Definiert eine Callback-Funktion, die an dem Extension Point $extension aufgerufen wird
   *
   * @param string   $extensionPoint
   * @param callable $callable       Name der Callback-Funktion
   * @param int      $level          Ausführungslevel (EARLY, NORMAL oder LATE)
   * @param array    $params         Array von zusätzlichen Parametern
   */
  static public function register($extensionPoint, $callable, $level = self::NORMAL, array $params = array())
  {
    if (static::hasFactoryClass()) {
      static::callFactoryClass(__FUNCTION__, func_get_args());
      return;
    }
    self::$extensions[$extensionPoint][(int) $level][] = array($callable, $params);
  }

  /**
   * Prüft ob eine extension für den angegebenen Extension Point definiert ist
   *
   * @param string $extensionPoint Name des ExtensionPoints
   *
   * @return boolean True, wenn eine Extension für den uebergeben ExtensionPoint definiert ist, sonst False
   */
  static public function isRegistered($extensionPoint)
  {
    if (static::hasFactoryClass()) {
      return static::callFactoryClass(__FUNCTION__, func_get_args());
    }
    return !empty (self::$extensions[$extensionPoint]);
  }
}
