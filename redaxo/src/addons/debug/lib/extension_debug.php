<?php

rex_extension::register('OUTPUT_FILTER', ['rex_extension_debug', 'doLog']);

/**
 * Class to monitor extension points via ChromePhp.
 *
 * @author staabm
 *
 * @package redaxo\debug
 */
class rex_extension_debug extends rex_extension
{
    private static $log = [];

    /**
     * Extends rex_extension::register() with ChromePhp logging.
     */
    public static function register($extensionPoint, callable $extension, $level = self::NORMAL, array $params = [])
    {
        $timer = new rex_timer();
        parent::register($extensionPoint, $extension, $level, $params);

        self::$log[] = [
            'type' => 'EXT',
            'ep' => $extensionPoint,
            'callable' => $extension,
            'level' => $level,
            'params' => $params,
            'timer' => $timer->getFormattedDelta(),
        ];
    }

    /**
     * Extends rex_extension::registerPoint() with ChromePhp logging.
     */
    public static function registerPoint(rex_extension_point $extensionPoint)
    {
        $coreTimer = rex::getProperty('timer');
        $absDur = $coreTimer->getFormattedDelta();

        // start timer for this extensionPoint
        $timer = new rex_timer();
        $res = parent::registerPoint($extensionPoint);
        $epDur = $timer->getFormattedDelta();

        $memory = rex_formatter::bytes(memory_get_usage(true), [3]);

        self::$log[] = [
            'type' => 'EP',
            'ep' => $extensionPoint->getName(),
            'started' => $absDur,
            'duration' => $epDur,
            'memory' => $memory,
            'subject' => $extensionPoint->getSubject(),
            'params' => $extensionPoint->getParams(),
            'read_only' => $extensionPoint->isReadonly(),
            'result' => $res,
            'timer' => $epDur,
        ];

        return $res;
    }

    /**
     * process log & send as ChromePhp table.
     */
    public static function doLog()
    {
        $registered_eps = $log_table = [];
        $counter = [
            'ep' => 0,
            'ext' => 0,
        ];

        foreach (self::$log as $count => $entry) {
            switch ($entry['type']) {
                case 'EP':
                    $counter['ep']++;
                    $registered_eps[] = $entry['ep'];
                    $log_table[] = [
                        'Type' => $entry['type'],      // Type
                        'ExtensionPoint' => $entry['ep'] . ($entry['read_only'] ? ' (readonly)' : ''),        // ExtensionPoint / readonly
                        'Callable' => '–',                 // Callable
                        'Start / Dur.' => $entry['started'] . '/ ' . $entry['duration'] . 'ms',   // Start / Dur.
                        'Memory' => $entry['memory'],    // Memory
                        'subject' => $entry['subject'],   // subject
                        'params' => $entry['params'],    // params
                        'result' => $entry['result'],    // result
                    ];
                    break;

                case 'EXT':
                    $counter['ext']++;

                    if (in_array($entry['ep'], $registered_eps)) {
                        ChromePhp::error('EP Timing: Extension "' . $entry['callable'] . '" registered after ExtensionPoint "' . $entry['ep'] . '" !');
                    }

                    $log_table[] = [
                        'Type' => $entry['type'],     // Type
                        'ExtensionPoint' => $entry['ep'],       // ExtensionPoint / readonly
                        'Callable' => $entry['callable'], // Callable
                        'Start / Dur.' => '–',                // Start / Dur.
                        'Memory' => '–',                // Memory
                        'subject' => '–',                // subject
                        'params' => $entry['params'],   // params
                        'result' => '-',                // result
                    ];
                    break;

                default:
                    throw new rex_exception('unexpexted type ' . $entry['type']);
            }
        }

        ChromePhp::log('EP Log ( EPs: ' . $counter['ep'] . ', Extensions: ' . $counter['ext'] . ' )');
        ChromePhp::table($log_table);
    }
}
