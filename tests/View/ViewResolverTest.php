<?php

namespace Redaxo\Core\Tests\View;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Tests\View\Fixtures\Card;
use Redaxo\Core\Tests\View\Fixtures\Greeting;
use Redaxo\Core\View\Html;
use Redaxo\Core\View\ViewResolver;

use function str_replace;

/** @internal */
final class ViewResolverTest extends TestCase
{
    private const string OVERRIDE = __DIR__ . '/Fixtures/GreetingOverride.view.php';
    private const string NAMESPACE_OVERRIDE = __DIR__ . '/Fixtures/Overrides/Greeting.view.php';

    #[Override]
    protected function tearDown(): void
    {
        ViewResolver::reset();
    }

    public function testResolvesCoLocatedViewByDefault(): void
    {
        $path = str_replace('\\', '/', ViewResolver::resolve(Greeting::class));

        self::assertStringEndsWith('tests/View/Fixtures/Greeting.view.php', $path);
    }

    public function testRegisteredOverrideWins(): void
    {
        ViewResolver::override(Greeting::class, self::OVERRIDE);

        self::assertSame(self::OVERRIDE, ViewResolver::resolve(Greeting::class));
    }

    public function testOverrideChangesRenderedMarkup(): void
    {
        ViewResolver::override(Greeting::class, self::OVERRIDE);

        $html = (string) new Greeting(name: 'World', message: Html::raw('x'))->render();

        self::assertStringContainsString('OVERRIDDEN: World', $html);
        self::assertStringNotContainsString('<section class="greeting">', $html);
    }

    public function testNamespaceOverrideResolvesViewByRelativeClassPath(): void
    {
        ViewResolver::overrideNamespace('Redaxo\Core\Tests\View', __DIR__);

        self::assertSame(__DIR__ . '/Fixtures/Greeting.view.php', ViewResolver::resolve(Greeting::class));
    }

    public function testNamespaceOverrideFallsBackToCoLocatedView(): void
    {
        ViewResolver::overrideNamespace('Redaxo\Core\Tests\View\Fixtures\\', __DIR__ . '/Fixtures/Overrides/');

        self::assertSame(self::NAMESPACE_OVERRIDE, ViewResolver::resolve(Greeting::class));

        $path = str_replace('\\', '/', ViewResolver::resolve(Card::class));
        self::assertStringEndsWith('tests/View/Fixtures/Card.view.php', $path);
    }

    public function testLastRegistrationWins(): void
    {
        ViewResolver::override(Greeting::class, self::OVERRIDE);
        ViewResolver::overrideNamespace('Redaxo\Core\Tests\View\Fixtures', __DIR__ . '/Fixtures/Overrides');

        self::assertSame(self::NAMESPACE_OVERRIDE, ViewResolver::resolve(Greeting::class));

        ViewResolver::override(Greeting::class, self::OVERRIDE);

        self::assertSame(self::OVERRIDE, ViewResolver::resolve(Greeting::class));
    }
}
