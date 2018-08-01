<?php

/**
 *
 * @package redaxo\core
 */
abstract class rex_minibar_drink
{
    /**
     * Returns the html bar item
     *
     * @return string
     */
    abstract public function serve();

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
