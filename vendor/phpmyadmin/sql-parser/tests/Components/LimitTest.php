<?php
declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Components;

use PhpMyAdmin\SqlParser\Components\Limit;
use PhpMyAdmin\SqlParser\Tests\TestCase;

class LimitTest extends TestCase
{
    public function testBuildWithoutOffset()
    {
        $component = new Limit(1);
        $this->assertEquals(Limit::build($component), '0, 1');
    }

    public function testBuildWithOffset()
    {
        $component = new Limit(1, 2);
        $this->assertEquals(Limit::build($component), '2, 1');
    }

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
            ['parser/parseLimitErr1'],
            ['parser/parseLimitErr2'],
        ];
    }
}
