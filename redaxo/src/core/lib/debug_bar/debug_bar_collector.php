<?php

/**
 * Class for debug bar
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
abstract class rex_debug_bar_collector
{
    /**
     * Returns the html bar item
     *
     * @return string
     */
    abstract public function getBarItem();

    /**
     * Returns the position in the debug bar
     *
     * @return bool
     */
    public function onLeftSide()
    {
        return true;
    }

    /**
     * Returns the danger status
     *
     * @return bool
     */
    public function isDanger()
    {
        return false;
    }

    /**
     * Returns the primary status
     *
     * @return bool
     */
    public function isPrimary()
    {
        return false;
    }

    /**
     * Returns the warning status
     *
     * @return bool
     */
    public function isWarning()
    {
        return false;
    }
}
