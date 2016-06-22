<?php

/**
 * Markdown parser.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_markdown
{
    /** @var ParsedownExtra */
    private $parser;

    public function __construct()
    {
        $this->parser = new ParsedownExtra();
        $this->parser->setBreaksEnabled(true);
    }

    /**
     * Parses markdown code.
     *
     * @param string $code Markdown code
     *
     * @return string HTML code
     */
    public function parse($code)
    {
        return $this->parser->text($code);
    }
}
