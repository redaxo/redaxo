<?php
declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Parser;

use PhpMyAdmin\SqlParser\Tests\TestCase;

class ExplainStatementTest extends TestCase
{
    /**
     * @param mixed $test
     *
     * @dataProvider explainProvider
     */
    public function testExplain($test)
    {
        $this->runParserTest($test);
    }

    public function explainProvider()
    {
        return [
            ['parser/parseExplain'],
        ];
    }
}
