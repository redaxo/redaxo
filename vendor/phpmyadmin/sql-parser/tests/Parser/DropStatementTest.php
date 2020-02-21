<?php

namespace PhpMyAdmin\SqlParser\Tests\Parser;

use PhpMyAdmin\SqlParser\Tests\TestCase;

class DropStatementTest extends TestCase
{
    /**
     * @dataProvider dropProvider
     *
     * @param mixed $test
     */
    public function testDrop($test)
    {
        $this->runParserTest($test);
    }

    public function dropProvider()
    {
        return [
            ['parser/parseDrop'],
            ['parser/parseDrop2'],
        ];
    }
}
