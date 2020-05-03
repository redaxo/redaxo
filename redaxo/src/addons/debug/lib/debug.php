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

    private static function init(): void
    {
        $clockwork = \Clockwork\Support\Vanilla\Clockwork::init([
            'storage_files_path' => rex_addon::get('debug')->getCachePath('clockwork.db'),
        ]);

        self::$instance = $clockwork;
    }

    public static function getInstance(): \Clockwork\Clockwork
    {
        return self::getHelper()->getClockwork();
    }

    public static function getHelper(): \Clockwork\Support\Vanilla\Clockwork
    {
        if (!self::$instance) {
            self::init();
        }
        return self::$instance;
    }
}
