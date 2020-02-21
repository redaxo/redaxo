<?php
declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Utils;

use PhpMyAdmin\SqlParser\Tests\TestCase;
use PhpMyAdmin\SqlParser\Utils\CLI;

class CLITest extends TestCase
{
    private function getCLI($getopt)
    {
        $cli = $this->getMockBuilder('PhpMyAdmin\SqlParser\Utils\CLI')->setMethods(['getopt'])->getMock();
        $cli->method('getopt')->willReturn($getopt);

        return $cli;
    }

    private function getCLIStdIn($input, $getopt)
    {
        $cli = $this->getMockBuilder('PhpMyAdmin\SqlParser\Utils\CLI')->setMethods(['getopt', 'readStdin'])->getMock();
        $cli->method('getopt')->willReturn($getopt);
        $cli->method('readStdin')->willReturn($input);
        return $cli;
    }

    /**
     * Test that getopt call works.
     *
     * We do mock it for other tests to return values we want.
     */
    public function testGetopt()
    {
        $cli = new CLI();
        $this->assertEquals(
            $cli->getopt('', []),
            []
        );
    }

    /**
     * @param mixed $getopt
     * @param mixed $output
     * @param mixed $result
     *
     * @dataProvider highlightParams
     */
    public function testRunHighlight($getopt, $output, $result)
    {
        $cli = $this->getCLI($getopt);
        $this->expectOutputString($output);
        $this->assertEquals($result, $cli->runHighlight());
    }

    public function highlightParams()
    {
        return [
            [
                ['q' => 'SELECT 1'],
                "\x1b[35mSELECT\n    \x1b[92m1\x1b[0m\n",
                0,
            ],
            [
                ['query' => 'SELECT 1'],
                "\x1b[35mSELECT\n    \x1b[92m1\x1b[0m\n",
                0,
            ],
            [
                [
                    'q' => 'SELECT /* comment */ 1 /* other */',
                    'f' => 'text',
                ],
                "SELECT\n    /* comment */ 1 /* other */\n",
                0,
            ],
            [
                [
                    'q' => 'SELECT 1',
                    'f' => 'foo',
                ],
                "ERROR: Invalid value for format!\n",
                1,
            ],
            [
                [
                    'q' => 'SELECT 1',
                    'f' => 'html',
                ],
                '<span class="sql-reserved">SELECT</span><br/>' .
                '&nbsp;&nbsp;&nbsp;&nbsp;<span class="sql-number">1</span>' . "\n",
                0,
            ],
            [
                ['h' => true],
                'Usage: highlight-query --query SQL [--format html|cli|text]' . "\n" .
                '       cat file.sql | highlight-query' . "\n",
                0,
            ],
            [
                [],
                'ERROR: Missing parameters!' . "\n" .
                'Usage: highlight-query --query SQL [--format html|cli|text]' . "\n" .
                '       cat file.sql | highlight-query' . "\n",
                1,
            ],
            [
                false,
                '',
                1,
            ],
        ];
    }


    /**
     * @dataProvider highlightParamsStdIn
     *
     * @param mixed $input
     * @param mixed $getopt
     * @param mixed $output
     * @param mixed $result
     */
    public function testRunHighlightStdIn($input, $getopt, $output, $result)
    {
        $cli = $this->getCLIStdIn($input, $getopt);
        $this->expectOutputString($output);
        $this->assertEquals($result, $cli->runHighlight());
    }

    public function highlightParamsStdIn()
    {
        return [
            [
                'SELECT 1',
                [],
                "\x1b[35mSELECT\n    \x1b[92m1\x1b[0m\n",
                0,
            ],
            [
                'SELECT /* comment */ 1 /* other */',
                [
                    'f' => 'text',
                ],
                "SELECT\n    /* comment */ 1 /* other */\n",
                0,
            ],
            [
                'SELECT 1',
                [
                    'f' => 'foo',
                ],
                "ERROR: Invalid value for format!\n",
                1,
            ],
            [
                'SELECT 1',
                [
                    'f' => 'html',
                ],
                '<span class="sql-reserved">SELECT</span>' . '<br/>' .
                '&nbsp;&nbsp;&nbsp;&nbsp;<span class="sql-number">1</span>' . "\n",
                0,
            ],
            [
                '',
                ['h' => true],
                'Usage: highlight-query --query SQL [--format html|cli|text]' . "\n" .
                '       cat file.sql | highlight-query' . "\n",
                0,
            ],
            [
                '',
                [],
                'ERROR: Missing parameters!' . "\n" .
                'Usage: highlight-query --query SQL [--format html|cli|text]' . "\n" .
                '       cat file.sql | highlight-query' . "\n",
                1,
            ],
            [
                '',
                false,
                '',
                1,
            ],
        ];
    }

    /**
     * @param mixed $input
     * @param mixed $getopt
     * @param mixed $output
     * @param mixed $result
     *
     * @dataProvider lintParamsStdIn
     */
    public function testRunLintFromStdIn($input, $getopt, $output, $result)
    {
        $cli = $this->getCLIStdIn($input, $getopt);
        $this->expectOutputString($output);
        $this->assertEquals($result, $cli->runLint());
    }

    public function lintParamsStdIn()
    {
        return [
            [
                'SELECT 1',
                [],
                '',
                0,
            ],
            [
                'SELECT SELECT',
                [],
                '#1: An expression was expected. (near "SELECT" at position 7)' . "\n" .
                '#2: This type of clause was previously parsed. (near "SELECT" at position 7)' . "\n" .
                '#3: An expression was expected. (near "" at position 0)' . "\n",
                10,
            ],
            [
                'SELECT SELECT',
                ['c' => 'MySql80000'],
                '#1: An expression was expected. (near "SELECT" at position 7)' . "\n" .
                '#2: This type of clause was previously parsed. (near "SELECT" at position 7)' . "\n" .
                '#3: An expression was expected. (near "" at position 0)' . "\n",
                10,
            ],
            [
                '',
                [],
                'ERROR: Missing parameters!' . "\n" .
                'Usage: lint-query --query SQL' . "\n" .
                '       cat file.sql | lint-query' . "\n",
                1,
            ],
            [
                '',
                ['h' => true],
                'Usage: lint-query --query SQL' . "\n" .
                '       cat file.sql | lint-query' . "\n",
                0,
            ],
            [
                '',
                false,
                '',
                1,
            ],
        ];
    }

    /**
     * @param mixed $getopt
     * @param mixed $output
     * @param mixed $result
     *
     * @dataProvider lintParams
     */
    public function testRunLint($getopt, $output, $result)
    {
        $cli = $this->getCLI($getopt);
        $this->expectOutputString($output);
        $this->assertEquals($result, $cli->runLint());
    }

    public function lintParams()
    {
        return [
            [
                ['q' => 'SELECT 1'],
                '',
                0,
            ],
            [
                ['query' => 'SELECT 1'],
                '',
                0,
            ],
            [
                ['q' => 'SELECT SELECT'],
                '#1: An expression was expected. (near "SELECT" at position 7)' . "\n" .
                '#2: This type of clause was previously parsed. (near "SELECT" at position 7)' . "\n" .
                '#3: An expression was expected. (near "" at position 0)' . "\n",
                10,
            ],
            [
                [
                    'q' => 'SELECT SELECT',
                    'c' => 'MySql80000',
                ],
                '#1: An expression was expected. (near "SELECT" at position 7)' . "\n" .
                '#2: This type of clause was previously parsed. (near "SELECT" at position 7)' . "\n" .
                '#3: An expression was expected. (near "" at position 0)' . "\n",
                10,
            ],
            [
                ['h' => true],
                'Usage: lint-query --query SQL' . "\n" .
                '       cat file.sql | lint-query' . "\n",
                0,
            ],
            [
                [],
                'ERROR: Missing parameters!' . "\n" .
                'Usage: lint-query --query SQL' . "\n" .
                '       cat file.sql | lint-query' . "\n",
                1,
            ],
            [
                false,
                '',
                1,
            ],
        ];
    }

    /**
     * @param mixed $getopt
     * @param mixed $output
     * @param mixed $result
     *
     * @dataProvider tokenizeParams
     */
    public function testRunTokenize($getopt, $output, $result)
    {
        $cli = $this->getCLI($getopt);
        $this->expectOutputString($output);
        $this->assertEquals($result, $cli->runTokenize());
    }

    public function tokenizeParams()
    {
        $result = "[TOKEN 0]\nType = 1\nFlags = 3\nValue = 'SELECT'\nToken = 'SELECT'\n\n"
            . "[TOKEN 1]\nType = 3\nFlags = 0\nValue = ' '\nToken = ' '\n\n"
            . "[TOKEN 2]\nType = 6\nFlags = 0\nValue = 1\nToken = '1'\n\n"
            . "[TOKEN 3]\nType = 9\nFlags = 0\nValue = NULL\nToken = NULL\n\n";

        return [
            [
                ['q' => 'SELECT 1'],
                $result,
                0,
            ],
            [
                ['query' => 'SELECT 1'],
                $result,
                0,
            ],
            [
                ['h' => true],
                'Usage: tokenize-query --query SQL' . "\n" .
                '       cat file.sql | tokenize-query' . "\n",
                0,
            ],
            [
                [],
                'ERROR: Missing parameters!' . "\n" .
                'Usage: tokenize-query --query SQL' . "\n" .
                '       cat file.sql | tokenize-query' . "\n",
                1,
            ],
            [
                false,
                '',
                1,
            ],
        ];
    }

    /**
     * @param mixed $input
     * @param mixed $getopt
     * @param mixed $output
     * @param mixed $result
     *
     * @dataProvider tokenizeParamsStdIn
     */
    public function testRunTokenizeStdIn($input, $getopt, $output, $result)
    {
        $cli = $this->getCLIStdIn($input, $getopt);
        $this->expectOutputString($output);
        $this->assertEquals($result, $cli->runTokenize());
    }

    public function tokenizeParamsStdIn()
    {
        $result = (
            "[TOKEN 0]\nType = 1\nFlags = 3\nValue = 'SELECT'\nToken = 'SELECT'\n\n"
            . "[TOKEN 1]\nType = 3\nFlags = 0\nValue = ' '\nToken = ' '\n\n"
            . "[TOKEN 2]\nType = 6\nFlags = 0\nValue = 1\nToken = '1'\n\n"
            . "[TOKEN 3]\nType = 9\nFlags = 0\nValue = NULL\nToken = NULL\n\n"
        );

        return [
            [
                'SELECT 1',
                [],
                $result,
                0,
            ],
            [
                '',
                ['h' => true],
                'Usage: tokenize-query --query SQL' . "\n" .
                '       cat file.sql | tokenize-query' . "\n",
                0,
            ],
            [
                '',
                [],
                'ERROR: Missing parameters!' . "\n" .
                'Usage: tokenize-query --query SQL' . "\n" .
                '       cat file.sql | tokenize-query' . "\n",
                1,
            ],
            [
                '',
                false,
                '',
                1,
            ],
        ];
    }

    /**
     * @param string $cmd
     * @param int    $result
     *
     * @dataProvider stdinParams
     */
    public function testStdinPipe($cmd, $result)
    {
        exec($cmd, $out, $ret);
        $this->assertSame($result, $ret);
    }

    public function stdinParams()
    {
        $binPath = PHP_BINARY . ' ' . dirname(__DIR__, 2) . '/bin/';

        return [
            [
                'echo "SELECT 1" | ' . $binPath . 'highlight-query',
                0,
            ],
            [
                'echo "invalid query" | ' . $binPath . 'highlight-query',
                0,
            ],
            [
                'echo "SELECT 1" | ' . $binPath . 'lint-query',
                0,
            ],
            [
                'echo "invalid query" | ' . $binPath . 'lint-query',
                10,
            ],
            [
                'echo "SELECT 1" | ' . $binPath . 'tokenize-query',
                0,
            ],
            [
                'echo "invalid query" | ' . $binPath . 'tokenize-query',
                0,
            ],
        ];
    }
}
