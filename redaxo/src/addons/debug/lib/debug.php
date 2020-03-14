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
        $ignoredClasses = array_merge(self::$ignoreClasses, $ignoredClasses);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $start = 0;
        for ($i = 0; $i < count($trace); ++$i) {
            if (isset($trace[$i + 1]['class']) && in_array($trace[$i + 1]['class'], $ignoredClasses)) {
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
