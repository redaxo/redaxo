<?php

namespace Redaxo\Core\Tests\RexVar;

use PHPUnit\Framework\Attributes\DataProvider;
use Redaxo\Core\RexVar\RexVar;

/** @internal */
final class RexVarTestVarA extends RexVar
{
    public function getOutput(): string
    {
        return $this->getParsedArg('content', "'default'", true);
    }

    public static function quote($string)
    {
        // make quote() public
        return parent::quote($string);
    }
}

/** @internal */
final class RexVarTestVarB extends RexVar
{
    public function getOutput(): string
    {
        return '2';
    }
}

/**
 * @internal
 */
final class RexVarTest extends RexVarTestBase
{
    public static function setUpBeforeClass(): void
    {
        RexVar::register('REX_TEST_VAR_A', RexVarTestVarA::class);
        RexVar::register('REX_TEST_VAR_B', RexVarTestVarB::class);
    }

    /** @return list<array{string, string}> */
    public static function parseTokensProvider(): array
    {
        return [
            ['aREX_TEST_VAR_A[content=b]c', 'abc'],
            ["a<?php echo 'bREX_TEST_VAR_A[content=c]d'; ?>e", 'abcde'],
            ['a<?php $foo = "123"; echo "REX_TEST_VAR_A[content=b]$foo"; ?>', 'ab123'],
            ['a<?php echo REX_TEST_VAR_A[content=b]; ?>c', 'abc'],
            ['REX_TEST_VAR_B[]', '2'],
            ['a<?php echo <<<EOT
bREX_TEST_VAR_A[content=c]d
EOT;
?>e', 'abcde'],
            ['a<?php echo <<<\'EOT\'
bREX_TEST_VAR_A[content=c]d
EOT;
?>e', 'abcde'],
            ['a
REX_TEST_VAR_A[content=b]
c', "a\nb\nc"],
        ];
    }

    #[DataProvider('parseTokensProvider')]
    public function testParseTokens(string $content, string $expectedOutput): void
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }

    /** @return list<array{string, string}> */
    public static function parseArgsSyntaxProvider(): array
    {
        return [
            ['REX_TEST_VAR_A[]', 'default'],
            ['REX_TEST_VAR_A[""]', ''],
            ['REX_TEST_VAR_A[ab]', 'ab'],
            ['REX_TEST_VAR_A["ab c"]', 'ab c'],
            ['REX_TEST_VAR_A[REX_TEST_VAR_A[ab]]', 'ab'],
            ['REX_TEST_VAR_A[content=ab]', 'ab'],
            [
                <<<'EOT'
                    REX_TEST_VAR_A[content="a 'b' \"c\" \ \\ \\\[d\]"]
                    EOT,
                'a \'b\' "c" \ \ \[d]',
            ],
            [
                <<<'EOT'
                    REX_TEST_VAR_A[content="a REX_TEST_VAR_A[content=\"b 'c' \\\"d\\\" \ \\ \\\[e\]\"] f"]
                    EOT,
                'a b \'c\' "d" \ \ \[e] f',
            ],
            [
                <<<'EOT'
                    REX_TEST_VAR_A[content="REX_TEST_VAR_A[content='\'a\' \"b\"']"]
                    EOT,
                '\'a\' "b"',
            ],
            [
                <<<'EOT'
                    <?php echo "REX_TEST_VAR_A[content=\"a 'b' \\\"c\\\" \ \\ \\\[d\]\"]";
                    EOT,
                'a \'b\' "c" \ \ \[d]',
            ],
            [
                <<<'EOT'
                    <?php echo 'REX_TEST_VAR_A[content="a \'b\' \"c\" \ \\ \\\[d\]"]';
                    EOT,
                'a \'b\' "c" \ \ \[d]',
            ],
            [
                <<<'EOT'
                    <?php echo 'REX_TEST_VAR_A[content=\'REX_TEST_VAR_A[content="a \\\'b\\\' \"c\" \ \\ \\\[d\]"]\']';
                    EOT,
                'a \'b\' "c" \ \ \[d]',
            ],
            [
                <<<'EOT'
                    REX_TEST_VAR_A[
                        content="ab
                    cd ef"
                    ]
                    EOT,
                "ab\ncd ef",
            ],
            ['REX_TEST_VAR_A[REX_NON_EXISTING[]]', 'REX_NON_EXISTING[]'],
            ['REX_NON_EXISTING[REX_TEST_VAR_A[ab]]', 'REX_NON_EXISTING[ab]'],
        ];
    }

    #[DataProvider('parseArgsSyntaxProvider')]
    public function testParseArgsSyntax(string $content, string $expectedOutput): void
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }

    /** @return list<array{string, string}> */
    public static function parseGlobalArgsProvider(): array
    {
        return [
            ['REX_TEST_VAR_A[content=ab instead=cd]', 'cd'],
            ['REX_TEST_VAR_A[content="" instead=cd]', ''],
            ['REX_TEST_VAR_A[content=ab ifempty=cd]', 'ab'],
            ['REX_TEST_VAR_A[content="" ifempty=cd]', 'cd'],
            ['REX_TEST_VAR_A[content="" ifempty="REX_TEST_VAR_A[cd]"]', 'cd'],
            ['REX_TEST_VAR_A[content=ab instead=cd ifempty=ef]', 'cd'],
            ['REX_TEST_VAR_A[content="" instead=cd ifempty=ef]', 'ef'],
            ['REX_TEST_VAR_A[content=cd prefix=ab]', 'abcd'],
            ['REX_TEST_VAR_A[content="" prefix=ab]', ''],
            ['REX_TEST_VAR_A[content=ef prefix="REX_TEST_VAR_A[content=cd prefix=ab]"]', 'abcdef'],
            ['REX_TEST_VAR_A[content=cd suffix=ef]', 'cdef'],
            ['REX_TEST_VAR_A[content="" suffix=ef]', ''],
            ['REX_TEST_VAR_A[content=cd prefix=ab suffix=ef]', 'abcdef'],
            ['REX_TEST_VAR_A[content="" prefix=ab suffix=ef]', ''],
            ['REX_TEST_VAR_A[content=cd prefix=ab suffix=ef instead=gh ifempty=ij]', 'abghef'],
            ['REX_TEST_VAR_A[content="" prefix=ab suffix=ef instead=gh ifempty=ij]', 'abijef'],
            ['REX_TEST_VAR_A[content=ab callback="' . self::class . '::varCallback" suffix=cd]', 'var:REX_TEST_VAR_A class:' . RexVarTestVarA::class . ' subject:ab content:ab suffix:cd'],
            ['REX_TEST_VAR_A[content="REX_TEST_VAR_A[ab]" callback="' . self::class . '::varCallback" suffix=cd]', 'var:REX_TEST_VAR_A class:' . RexVarTestVarA::class . ' subject:ab content:ab suffix:cd'],
        ];
    }

    public static function varCallback(array $params): string
    {
        return sprintf('var:%s class:%s subject:%s content:%s suffix:%s', $params['var'], $params['class'], $params['subject'], $params['content'], $params['suffix']);
    }

    #[DataProvider('parseGlobalArgsProvider')]
    public function testParseGlobalArgs(string $content, string $expectedOutput): void
    {
        $this->assertParseOutputEquals($expectedOutput, $content);
    }

    public function testToArray(): void
    {
        $content = '<?php echo ' . RexVar::class . '::toArray("REX_TEST_VAR_A[content=\'test\']") === null ? "null" : "";';
        $this->assertParseOutputEquals('null', $content, 'toArray() returns null for non-arrays');

        $array = ['1', '3', 'test'];

        $content = '<?php print_r(' . RexVar::class . '::toArray("REX_TEST_VAR_A[content=\'' . addcslashes(json_encode($array), '[]"') . '\']"));';
        $this->assertParseOutputEquals(print_r($array, true), $content, "toArray() works with non-htmlspecialchar'ed data");

        $content = '<?php print_r(' . RexVar::class . '::toArray("REX_TEST_VAR_A[content=\'' . addcslashes(htmlspecialchars(json_encode($array)), '[]"') . '\']"));';
        $this->assertParseOutputEquals(print_r($array, true), $content, "toArray() works with htmlspecialchar'ed data");

        $array = ['&#039;', '\&quot;']; // [code for ', code for "]
        $unescapedArray = ["'", '"'];
        $content = '<?php print_r(' . RexVar::class . '::toArray("REX_TEST_VAR_A[content=\'' . addcslashes(json_encode($array), '[]"') . '\']"));';
        $this->assertParseOutputEquals(print_r($unescapedArray, true), $content, 'toArray() rebuilds quotes');

        $array = ['&lt;strong&gt;inject me&lt;/strong&gt;', 'foo&amp;bar'];
        $unescapedArray = ['<strong>inject me</strong>', 'foo&bar'];
        $content = '<?php print_r(' . RexVar::class . '::toArray("REX_TEST_VAR_A[content=\'' . addcslashes(json_encode($array), '[]"') . '\']"));';
        $this->assertParseOutputEquals(print_r($unescapedArray, true), $content, 'toArray() rebuilds HTML');
    }

    public function testQuote(): void
    {
        $string = "abc 'de' \"fg\" \\ \nh\r\ni";
        $expected = <<<'EOD'
            'abc \'de\' "fg" \\ ' . "\n" . 'h' . "\r\n" . 'i'
            EOD;

        self::assertEquals($expected, RexVarTestVarA::quote($string));
    }
}
