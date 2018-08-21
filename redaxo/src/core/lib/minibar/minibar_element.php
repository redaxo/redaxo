<?php

/**
 *
 * @package redaxo\core
 */
abstract class rex_minibar_element
{
    /**
     * Returns the html bar item
     *
     * @return string
     */
    abstract public function render();

    /**
     * Returns the orientation in the minibar
     *
     * @return string `rex_minibar::LEFT` or `rex_minibar::RIGHT`
     */
    public function getOrientation()
    {
        return rex_minibar::LEFT;
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
