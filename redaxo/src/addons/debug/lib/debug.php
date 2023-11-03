<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_debug
{
    /** @var class-string[] */
    private static $ignoreClasses = [
        rex_extension_debug::class,
        rex_api_function_debug::class,
        self::class,
        rex_api_debug::class,
        rex_logger_debug::class,
        rex_sql_debug::class,
        rex_sql::class,
        rex_logger::class,
        rex_error_handler::class,
    ];

    /**
     * @param class-string[] $ignoredClasses
     */
    public static function getTrace(array $ignoredClasses = []): array
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
            'file' => $trace[$start]['file'] ?? null,
            'line' => $trace[$start]['line'] ?? null,
            'trace' => array_slice($trace, $start),
        ];
    }
}
