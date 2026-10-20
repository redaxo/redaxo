<?php

namespace Redaxo\Core\Tests\Content;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Content\CategoryNavigation;
use Redaxo\Core\Exception\LogicException;

/** @internal */
final class CategoryNavigationTest extends TestCase
{
    public function testContruct(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Base class ' . CategoryNavigation::class . ' must be instantiated via ' . CategoryNavigation::class . '::factory().');

        new CategoryNavigation();
    }

    public function testFactory(): void
    {
        $nav = CategoryNavigation::factory();

        self::assertInstanceOf(CategoryNavigation::class, $nav); // @phpstan-ignore-line
    }

    #[DoesNotPerformAssertions]
    public function testConstructAnonymousClass(): void
    {
        // no exception
        new class extends CategoryNavigation {};
    }
}
