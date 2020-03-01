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

    private function __construct()
    {
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
        $parser = new ParsedownExtra();
        $parser->setBreaksEnabled(true);

        return rex_string::sanitizeHtml($parser->text($code));
    }

    /**
     * Parses markdown code and extracts a table-of-content.
     *
     * @param string $code        Markdown code
     * @param int    $topLevel    Top included headline level for TOC, e.g. `1` for `<h1>`
     * @param int    $bottomLevel Bottom included headline level for TOC, e.g. `6` for `<h6>`
     *
     * @return array tupel of table-of-content and content
     */
    public function parseWithToc($code, $topLevel = 2, $bottomLevel = 3)
    {
        $parser = new rex_parsedown_with_toc();
        $parser->setBreaksEnabled(true);
        $parser->topLevel = $topLevel;
        $parser->bottomLevel = $bottomLevel;

        $content = rex_string::sanitizeHtml($parser->text($code));
        $headers = $parser->headers;

        $previous = $topLevel - 1;
        $toc = '';

        foreach ($headers as $header) {
            $level = $header['level'];

            if ($level > $previous) {
                if ($level > $previous + 1) {
                    $message = 'The headline structure in the given markdown document is malformed, ';
                    if ($previous < $topLevel) {
                        $message .= "it starts with a h$level instead of a h$topLevel.";
                    } else {
                        $message .= "a h$previous is followed by a h$level, but only a h".($previous + 1).' or lower is allowed.';
                    }

                    throw new rex_exception($message);
                }

                $toc .= "<ul>\n";
                $previous = $level;
            } elseif ($level < $previous) {
                for (; $level < $previous; --$previous) {
                    $toc .= "</li>\n";
                    $toc .= "</ul>\n";
                }
            } else {
                $toc .= "</li>\n";
            }

            $toc .= "<li>\n";
            $toc .= '<a href="#'.rex_escape($header['id']).'">'.rex_escape($header['text'])."</a>\n";
        }

        for (; $previous > $topLevel - 1; --$previous) {
            $toc .= "</li>\n";
            $toc .= "</ul>\n";
        }

        return [$toc, $content];
    }
}

/**
 * @internal
 */
final class rex_parsedown_with_toc extends ParsedownExtra
{
    private $ids = [];

    public $topLevel = 2;
    public $bottomLevel = 3;
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

    /**
     * @return array|null
     */
    private function handleHeader(array $block = null)
    {
        if (!$block) {
            return $block;
        }

        [$level] = sscanf($block['element']['name'], 'h%d');

        if ($level < $this->topLevel || $level > $this->bottomLevel) {
            return $block;
        }

        if (!isset($block['element']['attributes']['id'])) {
            $baseId = $id = 'header-'.rex_string::normalize($block['element']['text'], '-');

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
