<?php


/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_extension_debug extends rex_extension
{

    private static $registered = [];
    private static $executed = [];

    /**
     * Extends rex_extension::register() with ChromePhp logging.
     */
    public static function register($extensionPoint, callable $extension, $level = self::NORMAL, array $params = [])
    {
        parent::register($extensionPoint, $extension, $level, $params);


        $trace = rex_debug::getTrace([rex_extension::class]);
        rex_debug::getInstance()
            ->addEvent('EXT: '.$extensionPoint, $params, time(), $trace);
        self::$registered[] = [
            '#' => count(self::$registered),
            'name' => $extensionPoint,
            'file' => str_replace(rex_path::base(), '', $trace['trace'][0]['file']),
            'line' => $trace['trace'][0]['line'],
        ];
    }

    /**
     * Extends rex_extension::registerPoint() with ChromePhp logging.
     */
    public static function registerPoint(rex_extension_point $extensionPoint)
    {
        $coreTimer = rex::getProperty('timer');
        $absDur = $coreTimer->getFormattedDelta();



        $rnd = mt_rand();
        rex_debug::getInstance()->startEvent($rnd, 'EP: '. $extensionPoint->getName());

        $timer = new rex_timer();
        $res = parent::registerPoint($extensionPoint);
        $epDur = $timer->getFormattedDelta();

        rex_debug::getInstance()
            ->endEvent($rnd);

        $memory = rex_formatter::bytes(memory_get_usage(true), [3]);

        self::$executed[] = [
            '#' => count(self::$executed),
            'ep' => $extensionPoint->getName(),
            'subject' => $extensionPoint->getSubject(),
            'params' => $extensionPoint->getParams() ?: '',
            'read_only' => $extensionPoint->isReadonly(),
            'started at (ms)' => $absDur,
            'duration (ms)' => $epDur,
            'memory' => $memory,
            'result' => $res,
        ];

        rex_debug::getInstance()
            ->addEvent('EP: '.$extensionPoint->getName(), [
                'subject' => $extensionPoint->getSubject(),
                'params' => $extensionPoint->getParams(),
                'result' => $res,
            ], time(), rex_debug::getTrace([rex_extension::class]));

        return $res;
    }

    public static function getRegistered() {
        return self::$registered;
    }

    public static function getExecuted() {
        return self::$executed;
    }
}
