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
        $parser = new ParsedownExtra();
        $parser->setBreaksEnabled(true);

        return $parser->text($code);
    }

    /**
     * Parses markdown code and extracts a table-of-content.
     *
     * @param string $code Markdown code
     *
     * @return array tupel of table-of-content and content
     */
    public function parseWithToc($code)
    {
        $parser = new rex_parsedown();
        $parser->setBreaksEnabled(true);

        $content = $parser->text($code);
        $headers = $parser->headers;

        $previous = 1;
        $toc = '';

        foreach ($headers as $header) {
            $level = $header['level'];

            if ($level > $previous) {
                $toc .= "<ul>\n";
                $previous = $level;
            } elseif ($level < $previous) {
                $toc .= "</li>\n";
                $toc .= "</ul>\n";
                $previous = $level;
            } else {
                $toc .= "</li>\n";
            }

            $toc .= "<li>\n";
            $toc .= '<a href="#'.$header['id'].'">'.$header['text']."</a>\n";
        }

        for (; $previous > 1; --$previous) {
            $toc .= "</li>\n";
            $toc .= "</ul>\n";
        }

        return [$toc, $content];
    }
}

/**
 * @internal
 */
class rex_parsedown extends ParsedownExtra
{
    private $ids = [];

    public $headers = [];

    protected function blockHeader($line)
    {
        $block = parent::blockHeader($line);

        return $this->handleHeader($block);
    }

    protected function blockSetextHeader($line, array $block = null)
    {
        $block = parent::blockSetextHeader($line, $block);

        return $this->handleHeader($block);
    }

    private function handleHeader(array $block)
    {
        [$level] = sscanf($block['element']['name'], 'h%d');

        if ($level < 2 || $level > 3) {
            return $block;
        }

        if (!isset($block['element']['attributes']['id'])) {
            $baseId = $id = rex_string::normalize($block['element']['text'], '-');

            for ($i = 2; isset($this->ids[$id]); ++$i) {
                $id = $baseId.'-'.$i;
            }

            $block['element']['attributes']['id'] = $id;
        }

        $id = $block['element']['attributes']['id'];
        $this->ids[$id] = true;

        $this->headers[] = [
            'level' => $level,
            'id' => $id,
            'text' => $block['element']['text'],
        ];

        return $block;
    }
}
