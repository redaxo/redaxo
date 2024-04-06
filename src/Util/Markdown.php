<?php

namespace Redaxo\Core\Util;

use Redaxo\Core\Base\FactoryTrait;
use rex_exception;

/**
 * Markdown parser.
 */
class Markdown
{
    use FactoryTrait;

    public const SOFT_LINE_BREAKS = 'soft_line_breaks';
    public const HIGHLIGHT_PHP = 'highlight_php';

    final private function __construct() {}

    public static function factory(): static
    {
        $class = static::getFactoryClass();
        return new $class();
    }

    /**
     * Parses markdown code.
     *
     * @param string $code Markdown code
     * @param array<self::*, bool> $options
     *
     * @return string HTML code
     */
    public function parse($code, $options = [])
    {
        $parser = new Parsedown();
        $parser->setBreaksEnabled($options[self::SOFT_LINE_BREAKS] ?? true);
        $parser->highlightPhp = $options[self::HIGHLIGHT_PHP] ?? false;

        return Str::sanitizeHtml($parser->text($code));
    }

    /**
     * Parses markdown code and extracts a table-of-content.
     *
     * @param string $code Markdown code
     * @param int $topLevel Top included headline level for TOC, e.g. `1` for `<h1>`
     * @param int $bottomLevel Bottom included headline level for TOC, e.g. `6` for `<h6>`
     * @param array<self::*, bool> $options
     *
     * @return list{string, string} tupel of table-of-content and content
     */
    public function parseWithToc($code, $topLevel = 2, $bottomLevel = 3, $options = [])
    {
        $parser = new Parsedown();
        $parser->setBreaksEnabled($options[self::SOFT_LINE_BREAKS] ?? true);
        $parser->highlightPhp = $options[self::HIGHLIGHT_PHP] ?? false;

        $parser->generateToc = true;
        $parser->topLevel = $topLevel;
        $parser->bottomLevel = $bottomLevel;

        $content = Str::sanitizeHtml($parser->text($code));
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
                        $message .= "a h$previous is followed by a h$level, but only a h" . ($previous + 1) . ' or lower is allowed.';
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
            $toc .= '<a href="#' . rex_escape($header['id']) . '">' . rex_escape($header['text']) . "</a>\n";
        }

        for (; $previous > $topLevel - 1; --$previous) {
            $toc .= "</li>\n";
            $toc .= "</ul>\n";
        }

        return [$toc, $content];
    }
}
