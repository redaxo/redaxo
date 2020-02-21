<?php
declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Components;

use PhpMyAdmin\SqlParser\Components\ArrayObj;
use PhpMyAdmin\SqlParser\Components\Expression;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Tests\TestCase;

class ArrayObjTest extends TestCase
{
    public function testBuildRaw()
    {
        $component = new ArrayObj(['a', 'b'], []);
        $this->assertEquals('(a, b)', ArrayObj::build($component));
    }

    public function testBuildValues()
    {
        $component = new ArrayObj([], ['a', 'b']);
        $this->assertEquals('(a, b)', ArrayObj::build($component));
    }

    public function testParseType()
    {
        $components = ArrayObj::parse(
            new Parser(),
            $this->getTokensList('(1 + 2, 3 + 4)'),
            [
                'type' => Expression::class,
                'typeOptions' => [
                    'breakOnParentheses' => true,
                ],
            ]
        );
        $this->assertInstanceOf(Expression::class, $components[0]);
        $this->assertInstanceOf(Expression::class, $components[1]);
        $this->assertEquals($components[0]->expr, '1 + 2');
        $this->assertEquals($components[1]->expr, '3 + 4');
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
            ['parser/parseArrayErr1'],
            ['parser/parseArrayErr3'],
        ];
    }
}
