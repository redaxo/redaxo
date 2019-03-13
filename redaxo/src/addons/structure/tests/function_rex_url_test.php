<?php

use PHPUnit\Framework\TestCase;

class rex_structure_function_url_test extends TestCase
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
