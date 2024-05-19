<?php

namespace Redaxo\Core\Util;

use Override;
use ParsedownExtra;

use const E_DEPRECATED;

/**
 * @internal
 */
final class Parsedown extends ParsedownExtra
{
    public bool $highlightPhp = false;
    public bool $generateToc = false;
    public int $topLevel = 2;
    public int $bottomLevel = 3;

    /** @var list<array{level: int, id: string, text: string}> */
    public array $headers = [];

    /** @var array<string, true> */
    private array $ids = [];

    /**
     * @return string
     */
    #[Override]
    public function text($text)
    {
        // https://github.com/erusev/parsedown-extra/issues/173
        $errorReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

        try {
            return parent::text($text);
        } finally {
            error_reporting($errorReporting);
        }
    }

    /**
     * @return array|null
     */
    #[Override]
    protected function blockHeader($Line)
    {
        $block = parent::blockHeader($Line);

        return $this->handleHeader($block);
    }

    /**
     * @return array|null
     */
    #[Override]
    protected function blockSetextHeader($Line, ?array $Block = null)
    {
        $block = parent::blockSetextHeader($Line, $Block);

        return $this->handleHeader($block);
    }

    /**
     * @return array
     */
    #[Override]
    protected function blockFencedCodeComplete($Block)
    {
        /** @var array $Block */
        $Block = parent::blockFencedCodeComplete($Block);

        if (!$this->highlightPhp) {
            return $Block;
        }

        /** @psalm-suppress MixedArrayAccess */
        if ('language-php' !== ($Block['element']['text']['attributes']['class'] ?? null)) {
            return $Block;
        }

        /**
         * @var string $text
         * @psalm-suppress MixedArrayAccess
         */
        $text = $Block['element']['text']['text'];

        $missingPhpStart = !str_contains($text, '<?php') && !str_contains($text, '<?=');
        if ($missingPhpStart) {
            $text = '<?php ' . $text;
        }

        $text = highlight_string($text, true);
        if (str_starts_with($text, '<pre>')) {
            $text = substr($text, 5, -6);
        }

        // php 8.3 fix
        $text = preg_replace('@<span style="color:[^"]+">\n(<span style="color:[^"]+">)@', '$1', $text, 1);
        $text = preg_replace('@<\/span>\n(<\/span>\n<\/code>)$@', '$1', $text, 1);

        if ($missingPhpStart) {
            $text = preg_replace('@(<span style="color:[^"]+">)(?:&lt;|<)\?php(?:&nbsp;|\s)@', '$1', $text, 1);
        }

        /** @psalm-suppress MixedArrayAssignment */
        $Block['element']['rawHtml'] = $text;
        /** @psalm-suppress MixedArrayAccess */
        unset($Block['element']['text'], $Block['element']['handler']);

        return $Block;
    }

    private function handleHeader(?array $block = null): ?array
    {
        if (!$this->generateToc) {
            return $block;
        }

        if (!$block) {
            return $block;
        }

        [$level] = sscanf($block['element']['name'], 'h%d');

        $plainText = strip_tags($this->{$block['element']['handler']}($block['element']['text']));
        $plainText = htmlspecialchars_decode($plainText);

        if (!isset($block['element']['attributes']['id'])) {
            $baseId = $id = Str::normalize($plainText, '-');

            for ($i = 1; isset($this->ids[$id]); ++$i) {
                $id = $baseId . '-' . $i;
            }

            $block['element']['attributes']['id'] = $id;
        }

        $id = $block['element']['attributes']['id'];
        $this->ids[$id] = true;

        if ($level >= $this->topLevel && $level <= $this->bottomLevel) {
            $this->headers[] = [
                'level' => $level,
                'id' => $id,
                'text' => $plainText,
            ];
        }

        return $block;
    }
}
