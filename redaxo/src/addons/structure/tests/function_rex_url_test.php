<?php

class rex_structure_function_url_test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideRedirectException
     * @expectedException \InvalidArgumentException
     */
    public function testRedirectException($article_id)
    {
        rex_redirect($article_id);
    }

    public function provideRedirectException()
    {
        return [
            ['http://www.example.com'],
            ['1 Foo'],
        ];
    }
}
