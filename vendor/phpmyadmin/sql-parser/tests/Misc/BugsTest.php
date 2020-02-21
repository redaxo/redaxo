<?php
declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Misc;

use PhpMyAdmin\SqlParser\Tests\TestCase;

class BugsTest extends TestCase
{
    /**
     * @param mixed $test
     *
     * @dataProvider bugProvider
     */
    public function testBug($test)
    {
        $this->runParserTest($test);
    }

    public function bugProvider()
    {
        return [
            ['bugs/gh9'],
            ['bugs/gh14'],
            ['bugs/gh16'],
            ['bugs/pma11800'],
            ['bugs/pma11836'],
            ['bugs/pma11843'],
            ['bugs/pma11867'],
            ['bugs/pma11879'],
        ];
    }
}
