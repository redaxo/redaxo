<?php

/**
 * Backend main page class
 *
 * @package redaxo\core
 */
class rex_be_page_main extends rex_be_page
{
    private $block;
    private $prio = 0;

    /**
     * Constructor
     *
     * @param string $block Navigation block
     * @param string $key
     * @param string $title
     * @throws InvalidArgumentException
     */
    public function __construct($block, $key, $title)
    {
        if (!is_string($block)) {
            throw new InvalidArgumentException('Expecting $block to be a string, ' . gettype($block) . 'given!');
        }
        $this->block = $block;

        parent::__construct($key, $title);
    }

    /**
     * Sets the navigation block
     *
     * @param string $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * Returns the navigation block
     *
     * @return string
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Sets the priority
     *
     * @param int $prio
     */
    public function setPrio($prio)
    {
        $this->prio = $prio;
    }

    /**
     * Returns the priority
     *
     * @return int
     */
    public function getPrio()
    {
        return $this->prio;
    }
}
