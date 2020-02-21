<?php
declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Parser;

use PhpMyAdmin\SqlParser\Exceptions\ParserException;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Tests\TestCase;
use PhpMyAdmin\SqlParser\Token;
use PhpMyAdmin\SqlParser\TokensList;

class ParserTest extends TestCase
{
    /**
     * @param mixed $test
     *
     * @dataProvider parseProvider
     */
    public function testParse($test)
    {
        $this->runParserTest($test);
    }

    public function parseProvider()
    {
        return [
            ['parser/parse'],
            ['parser/parse2'],
            ['parser/parseDelimiter'],
        ];
    }

    public function testUnrecognizedStatement()
    {
        $parser = new Parser('SELECT 1; FROM');
        $this->assertEquals(
            'Unrecognized statement type.',
            $parser->errors[0]->getMessage()
        );
    }

    public function testUnrecognizedKeyword()
    {
        $parser = new Parser('SELECT 1 FROM foo PARTITION(bar, baz) AS');
        $this->assertEquals(
            'Unrecognized keyword.',
            $parser->errors[0]->getMessage()
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testError()
    {
        $parser = new Parser(new TokensList());

        $parser->error('error #1', new Token('foo'), 1);
        $parser->error(sprintf('%2$s #%1$d', 2, 'error'), new Token('bar'), 2);

        $this->assertEquals(
            $parser->errors,
            [
                new ParserException('error #1', new Token('foo'), 1),
                new ParserException('error #2', new Token('bar'), 2),
            ]
        );
    }

    public function testErrorStrict()
    {
        $this->expectExceptionCode(3);
        $this->expectExceptionMessage('strict error');
        $this->expectException(ParserException::class);
        $parser = new Parser(new TokensList());
        $parser->strict = true;

        $parser->error('strict error', new Token('foo'), 3);
    }
}
