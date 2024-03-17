<?php

class rex_be_page_main extends rex_be_page
{
    /** @var string */
    private $block;
    /** @var int */
    private $prio = 0;

    /**
     * @param string $block Navigation block
     */
    public function __construct(string $block, string $key, string $title)
    {
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
