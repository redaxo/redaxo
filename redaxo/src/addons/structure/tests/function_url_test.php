<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_structure_function_url_test extends TestCase
{
    #[DataProvider('provideRedirectException')]
    public function testRedirectException(string $articleId): never
    {
        $this->expectException(InvalidArgumentException::class);

        rex_redirect($articleId);
    }

    /** @return list<array{string}> */
    public static function provideRedirectException(): array
    {
        return [
            ['http://www.example.com'],
            ['1 Foo'],
        ];
    }
}
