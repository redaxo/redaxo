<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_socket_proxy_test extends TestCase
{
    private ?string $proxy = null;

    protected function setUp(): void
    {
        $this->proxy = rex::getProperty('socket_proxy');
        rex::setProperty('socket_proxy', null);
    }

    protected function tearDown(): void
    {
        rex::setProperty('socket_proxy', $this->proxy);
    }

    public function testFactory(): void
    {
        $socket = rex_socket_proxy::factory('www.example.com');
        static::assertEquals(rex_socket_proxy::class, $socket::class);
    }

    public function testFactoryUrl(): void
    {
        $socket = rex_socket_proxy::factoryUrl('www.example.com');
        static::assertEquals(rex_socket_proxy::class, $socket::class);
    }
}
