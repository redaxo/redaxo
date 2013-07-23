<?php

/**
 * Functions
 * @package redaxo5
 */


if (!function_exists('rex_e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param string $value
     * @return string
     */
    function rex_e($value)
    {
        return htmlspecialchars($value, ENT_COMPAT | ENT_HTML401, 'UTF-8', true);
    }
}


if (!function_exists('rex_vd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  dynamic  mixed
     * @return void
     */
    function rex_vd()
    {
        array_map(function ($x) {
            echo '<pre>'; var_dump($x); echo '</pre>';
        }, func_get_args()); die;
    }
}


if (!function_exists('rex_pr')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  dynamic  mixed
     * @return void
     */
    function rex_pr()
    {
        array_map(function ($x) {
            echo '<pre>'; print_r($x); echo '</pre>';
        }, func_get_args()); die;
    }
}
