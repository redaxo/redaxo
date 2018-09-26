<?php

/**
 * @package redaxo\core
 */
abstract class rex_minibar_element
{
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';

    /**
     * Returns the html bar item.
     *
     * @return string
     */
    abstract public function render();

    /**
     * Returns the orientation in the minibar.
     *
     * @return string `rex_minibar_element::LEFT` or `rex_minibar_element::RIGHT`
     */
    public function getOrientation()
    {
        return self::LEFT;
    }

    /**
     * Returns the danger status.
     *
     * @return bool
     */
    public function isDanger()
    {
        return false;
    }

    /**
     * Returns the primary status.
     *
     * @return bool
     */
    public function isPrimary()
    {
        return false;
    }

    /**
     * Returns the warning status.
     *
     * @return bool
     */
    public function isWarning()
    {
        return false;
    }
}
