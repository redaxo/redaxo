<?php

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Core;
use Redaxo\Core\HttpClient\ProxyRequest;

/** @internal */
final class ProxyRequestTest extends TestCase
{
    private ?string $proxy = null;

    protected function setUp(): void
    {
        $this->proxy = Core::getProperty('socket_proxy');
        Core::setProperty('socket_proxy', null);
    }

    protected function tearDown(): void
    {
        Core::setProperty('socket_proxy', $this->proxy);
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
