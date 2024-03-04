<?php

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Core;

/**
 * @internal
 */
class rex_socket_proxy_test extends TestCase
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
        $socket = rex_socket_proxy::factory('www.example.com');
        self::assertEquals(rex_socket_proxy::class, $socket::class);
    }

    public function testFactoryUrl(): void
    {
        $socket = rex_socket_proxy::factoryUrl('www.example.com');
        self::assertEquals(rex_socket_proxy::class, $socket::class);
    }
}
