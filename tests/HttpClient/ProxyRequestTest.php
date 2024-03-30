<?php

namespace Redaxo\Core\Tests\HttpClient;

use Override;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Core;
use Redaxo\Core\HttpClient\ProxyRequest;

/** @internal */
final class ProxyRequestTest extends TestCase
{
    private ?string $proxy = null;

    #[Override]
    protected function setUp(): void
    {
        $this->proxy = Core::getProperty('http_client_proxy');
        Core::setProperty('http_client_proxy', null);
    }

    #[Override]
    protected function tearDown(): void
    {
        Core::setProperty('http_client_proxy', $this->proxy);
    }

    public function testFactory(): void
    {
        $socket = ProxyRequest::factory('www.example.com');
        self::assertEquals(ProxyRequest::class, $socket::class);
    }

    public function testFactoryUrl(): void
    {
        $socket = ProxyRequest::factoryUrl('www.example.com');
        self::assertEquals(ProxyRequest::class, $socket::class);
    }
}
