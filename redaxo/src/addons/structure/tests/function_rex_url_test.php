<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_structure_function_url_test extends TestCase
{
    /**
     * @dataProvider provideRedirectException
     */
    public function testRedirectException($article_id)
    {
        $this->expectException(\InvalidArgumentException::class);

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
