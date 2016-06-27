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
    use rex_factory_trait;

    /** @var ParsedownExtra */
    private $parser;

    private function __construct()
    {
        $this->parser = new ParsedownExtra();
        $this->parser->setBreaksEnabled(true);
    }

    /**
     * @return static
     */
    public static function factory()
    {
        $class = static::getFactoryClass();
        return new $class();
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
