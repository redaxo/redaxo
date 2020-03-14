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
    private static $listeners = [];

    public static function register($extensionPoint, callable $extension, $level = self::NORMAL, array $params = [])
    {
        parent::register($extensionPoint, $extension, $level, $params);

        $trace = rex_debug::getTrace([rex_extension::class]);
        if (!is_array($extensionPoint)) {
            $extensionPoint = [$extensionPoint];
        }

        foreach($extensionPoint as $ep) {
            self::$listeners[$ep][] =  $trace['file'].':'.$trace['line'];

            self::$registered[] = [
                '#' => count(self::$registered),
                'name' => $ep,
                'file' => $trace['file'],
                'line' => $trace['line'],
            ];
        }
    }

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

        $data = rex_debug::getTrace([rex_extension::class]);
        $data['listeners'] = self::$listeners[$extensionPoint->getName()];

        rex_debug::getInstance()
            ->addEvent('EP: '.$extensionPoint->getName(), [
                'subject' => $extensionPoint->getSubject(),
                'params' => $extensionPoint->getParams(),
                'result' => $res,
            ], time(), $data);

        return $res;
    }

    public static function getRegistered()
    {
        return self::$registered;
    }

    public static function getExecuted()
    {
        return self::$executed;
    }
}
