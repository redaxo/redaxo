<?php

/**
 * Backend main page class.
 *
 * @package redaxo\core\backend
 */
class rex_be_page_main extends rex_be_page
{
    /**
     * @var string
     */
    private $block;
    /**
     * @var int
     */
    private $prio = 0;

    /**
     * Constructor.
     *
     * @param string $block Navigation block
     * @param string $key
     * @param string $title
     *
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
     * Sets the navigation block.
     *
     * @param string $block
     *
     * @return $this
     */
    public function setBlock($block)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Returns the navigation block.
     *
     * @return string
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Sets the priority.
     *
     * @param int $prio
     *
     * @return $this
     */
    public function setPrio($prio)
    {
        $this->prio = $prio;

        return $this;
    }

    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPrio()
    {
        return $this->prio;
    }
}
