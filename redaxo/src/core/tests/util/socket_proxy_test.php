<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_socket_proxy_test extends TestCase
{
    private $proxy;

    protected function setUp(): void
    {
        $this->proxy = rex::getProperty('socket_proxy');
        rex::setProperty('socket_proxy', null);
    }

    protected function tearDown(): void
    {
        rex::setProperty('socket_proxy', $this->proxy);
    }

    public function testFactory()
    {
        $socket = rex_socket_proxy::factory('www.example.com');
        static::assertEquals(rex_socket_proxy::class, get_class($socket));
    }

    public function testFactoryUrl()
    {
        $socket = rex_socket_proxy::factoryUrl('www.example.com');
        static::assertEquals(rex_socket_proxy::class, get_class($socket));
    }
}
