<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_debug
{
    /** @var \Clockwork\Support\Vanilla\Clockwork */
    private static $instance;

    private static $ignoreClasses = [
        rex_extension_debug::class,
        rex_api_function_debug::class,
        self::class,
        rex_api_debug::class,
        rex_logger_debug::class,
        rex_sql_debug::class,
    ];

    public static function init()
    {
        $clockwork = \Clockwork\Support\Vanilla\Clockwork::init([
            'storage_files_path' => rex_addon::get('debug')->getDataPath('clockwork.db'),
        ]);

        self::$instance = $clockwork;
    }

    public static function getInstance()
    {
        return self::getHelper()->getClockwork();
    }

    public static function getHelper()
    {
        if (!self::$instance) {
            self::init();
        }
        return self::$instance;
    }

    public static function getTrace(array $ignoredClasses = [])
    {
        $ignoredClasses = self::$ignoreClasses + $ignoredClasses;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $start = 0;
        for ($i = 1; $i < count($trace); ++$i) {
            if (isset($trace[$i]['file']) && false !== strpos($trace[$i]['file'], 'debug.php')) {
                continue;
            }

            if (isset($trace[$i]['class']) && in_array($trace[$i]['class'], $ignoredClasses)) {
                continue;
            }

            $start = $i;
            break;
        }
        return [
            'file' => $trace[$start]['file'],
            'line' => $trace[$start]['line'],
            'trace' => array_slice($trace, $start),
        ];
    }
}
