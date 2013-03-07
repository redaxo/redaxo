<?php

class rex_string_test extends PHPUnit_Framework_TestCase
{
    public function splitProvider()
    {
        return [
            ['',                                          []],
            ['a b c',                                     ['a', 'b', 'c']],
            ['"a b" cdef \'ghi kl\'',                     ['a b', 'cdef', 'ghi kl']],
            ['a=1 b=xyz c="hu hu" 123=\'he he\'',         ['a' => 1, 'b' => 'xyz', 'c' => 'hu hu', '123' => 'he he']],
            ['a="a \"b\" c" b=\'a \\\'b\\\'\' c="a\\\\"', ['a' => 'a "b" c', 'b' => "a 'b'", 'c' => "a\\"]],
            ["\n a=1\n b='aa\nbb'\n c='a'\n ",            ['a' => '1', 'b' => "aa\nbb", 'c' => 'a']]
        ];
    }

    /**
     * @dataProvider splitProvider
     */
    public function testSplit($string, $expectedArray)
    {
        $this->assertEquals($expectedArray, rex_string::split($string));
    }

    public function testSize()
    {
        $this->assertEquals(3, rex_string::size('aä'));
    }

    public function versionSplitProvider()
    {
        return [
            ['1.1.2',      ['1', '1', '2']],
            ['1.2alpha1',  ['1', '2', 'alpha', '1']],
            ['1_2 beta 2', ['1', '2', 'beta', '2']],
            ['2.2.3-dev',  ['2', '2', '3', 'dev']]
        ];
    }

    /**
     * @dataProvider versionSplitProvider
     */
    public function testVersionSplit($version, $expected)
    {
        $this->assertEquals($expected, rex_string::versionSplit($version));
    }

    public function versionCompareProvider()
    {
        return [
            ['1',      '1',      '='],
            ['1.0',    '1.0',    '='],
            ['1',      '1.0',    '='],
            ['1.0 a1', '1.0.a1', '='],
            ['1.0a1',  '1.0.a1', '='],
            ['1.0 alpha 1', '1.0.a1', '='],

            ['1',      '2',        '<'],
            ['1',      '1.1',      '<'],
            ['1.0',    '1.1',      '<'],
            ['1.1',    '1.2',      '<'],
            ['1.2',    '1.10',     '<'],
            ['1.a1',   '1',        '<'],
            ['1.a1',   '1.0',      '<'],
            ['1.a1',   '1.a2',     '<'],
            ['1.a1',   '1.b1',     '<'],
            ['1.0.a1', '1',        '<'],
            ['1.0.a1', '1.0.0.0.', '<'],
            ['1.0a1',  '1.0',      '<'],
            ['1.0a1',  '1.0.1',    '<'],
            ['1.0a1',  '1.0a2',    '<'],
            ['1.0',    '1.1a1',    '<'],
            ['1.0.1',  '1.1a1',    '<']
        ];
    }

    /**
     * @dataProvider versionCompareProvider
     */
    public function testVersionCompare($version1, $version2, $comparator)
    {
        $this->assertTrue(rex_string::versionCompare($version1, $version2, $comparator));
    }

    public function buildQueryProvider()
    {
        return [
            ['', []],
            ['page=system/settings&a%2Bb=test+test', ['page' => 'system/settings', 'a+b' => 'test test']],
            ['arr[0]=a&arr[1]=b&arr[key]=c', ['arr' => ['a', 'b', 'key' => 'c']]],
            ['a=1&amp;b=2', ['a' => 1, 'b' => 2], '&amp;']
        ];
    }

    /**
     * @dataProvider buildQueryProvider
     */
    public function testBuildQuery($expected, $params, $argSeparator = '&')
    {
        $this->assertEquals($expected, rex_string::buildQuery($params, $argSeparator));
    }
}
