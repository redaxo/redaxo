<?php

rex_extension::register('OUTPUT_FILTER', ['rex_extension_debug', 'doLog']);

/**
 * Class to monitor extension points via FirePHP
 *
 * @author staabm
 * @package redaxo\debug
 */
class rex_extension_debug extends rex_extension
{
    private static $log = [];

    /**
     * Extends rex_extension::register() with FirePHP logging
     */
    public static function register($extensionPoint, callable $extension, $level = self::NORMAL, array $params = [])
    {
        $timer  = new rex_timer();
        parent::register($extensionPoint, $extension, $level, $params);

        self::$log[] = [
            'type'     => 'EXT',
            'ep'       => $extensionPoint,
            'callable' => $extension,
            'level'    => $level,
            'params'   => $params,
            'timer'    => $timer->getFormattedDelta(),
        ];
    }


    /**
     * Extends rex_extension::registerPoint() with FirePHP logging
     */
    public static function registerPoint(rex_extension_point $extensionPoint)
    {
        $coreTimer = rex::getProperty('timer');
        $absDur    = $coreTimer->getFormattedDelta();

        // start timer for this extensionPoint
        $timer  = new rex_timer();
        $res    = parent::registerPoint($extensionPoint);
        $epDur  = $timer->getFormattedDelta();

        $memory = rex_formatter::bytes(memory_get_usage(true), [3]);

        self::$log[] = [
            'type'      => 'EP',
            'ep'        => $extensionPoint->getName(),
            'started'   => $absDur,
            'duration'  => $epDur,
            'memory'    => $memory,
            'subject'   => $extensionPoint->getSubject(),
            'params'    => $extensionPoint->getParams(),
            'read_only' => $extensionPoint->isReadonly(),
            'result'    => $res,
            'timer'     => $epDur
        ];

        return $res;
    }


    /**
     * process log & send as FirePHP table
     */
    public static function doLog()
    {
        $firephp = FirePHP::getInstance(true);

        $registered_eps = $log_table = [];
        $counter        = [
            'ep'       => 0,
            'ext'      => 0,
        ];
        $log_table[]    = [
            'Type',
            'ExtensionPoint',
            'Callable',
            'Start / Dur.',
            'Memory',
            'subject',
            'params',
            'result',
        ];

        foreach (self::$log as $count => $entry) {
            switch ($entry['type']) {
                case 'EP':
                    $counter['ep']++;
                    $registered_eps[] = $entry['ep'];
                    $log_table[] = [
                        $entry['type'],      // Type
                        $entry['ep'] . ($entry['read_only'] ? ' (readonly)' : ''),        // ExtensionPoint / readonly
                        '–',                 // Callable
                        $entry['started'] . '/ ' . $entry['duration'] . 'ms',   // Start / Dur.
                        $entry['memory'],    // Memory
                        $entry['subject'],   // subject
                        $entry['params'],    // params
                        $entry['result'],    // result
                    ];
                    break;

                case 'EXT':
                    $counter['ext']++;

                    if (in_array($entry['ep'], $registered_eps)) {
                        $firephp->error('EP Timing: Extension "' . $entry['callable'] . '" registered after ExtensionPoint "' . $entry['ep'] . '" !');
                    }

                    $log_table[] = [
                        $entry['type'],     // Type
                        $entry['ep'],       // ExtensionPoint / readonly
                        $entry['callable'], // Callable
                        '–',                // Start / Dur.
                        '–',                // Memory
                        '–',                // subject
                        $entry['params'],   // params
                        '-',                // result
                    ];
                    break;

                default:
                    throw new rex_exception('unexpexted type ' . $entry['type']);
            }
        }

        $firephp->table('EP Log ( EPs: ' . $counter['ep'] . ', Extensions: ' . $counter['ext'] . ' )', $log_table);
    }
}
