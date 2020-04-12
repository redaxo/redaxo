<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_extension_debug extends rex_extension
{
    private static $extensionPoints = [];
    private static $extensions = [];
    private static $listeners = [];

    public static function registerPoint(rex_extension_point $extensionPoint)
    {
        $coreTimer = rex::getProperty('timer');
        $absDur = $coreTimer->getFormattedDelta();

        $timer = new rex_timer();
        $res = parent::registerPoint($extensionPoint);
        $epDur = $timer->getFormattedDelta();

        $memory = rex_formatter::bytes(memory_get_usage(true), [3]);

        self::$extensionPoints[] = [
            '#' => count(self::$extensionPoints),
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
        $data['listeners'] = self::$listeners[$extensionPoint->getName()] ?? [];

        rex_debug::getInstance()
            ->addEvent('EP: '.$extensionPoint->getName(), [
                'subject' => $extensionPoint->getSubject(),
                'params' => $extensionPoint->getParams(),
                'result' => $res,
            ], time(), $data);

        return $res;
    }

    public static function register($extensionPoint, callable $extension, $level = self::NORMAL, array $params = [])
    {
        parent::register($extensionPoint, $extension, $level, $params);

        $trace = rex_debug::getTrace([rex_extension::class]);
        if (!is_array($extensionPoint)) {
            $extensionPoint = [$extensionPoint];
        }

        foreach ($extensionPoint as $ep) {
            self::$listeners[$ep][] = $trace['file'].':'.$trace['line'];

            self::$extensions[] = [
                '#' => count(self::$extensions),
                'name' => $ep,
                'file' => $trace['file'],
                'line' => $trace['line'],
            ];
        }
    }

    public static function getExtensionPoints(): array
    {
        return self::$extensionPoints;
    }

    public static function getExtensions(): array
    {
        return self::$extensions;
    }
}
