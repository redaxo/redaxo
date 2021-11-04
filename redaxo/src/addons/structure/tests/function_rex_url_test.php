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
    public function testRedirectException($articleId)
    {
        $this->expectException(InvalidArgumentException::class);

        rex_redirect($articleId);
    }

    public function provideRedirectException()
    {
        return [
            ['http://www.example.com'],
            ['1 Foo'],
        ];
    }
}
