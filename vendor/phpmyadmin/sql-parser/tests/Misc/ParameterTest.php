<?php
declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Misc;

use PhpMyAdmin\SqlParser\Tests\TestCase;

class ParameterTest extends TestCase
{
    /**
     * @param mixed $test
     *
     * @dataProvider parameterProvider
     */
    public function testParameter($test)
    {
        $this->runParserTest($test);
    }

    public function parameterProvider()
    {
        return [
            ['misc/parseParameter'],
        ];
    }
}
