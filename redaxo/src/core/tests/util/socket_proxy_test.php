<?php

class rex_socket_proxy_test extends PHPUnit_Framework_TestCase
{
    private $proxy;

    protected function setUp()
    {
        $this->proxy = rex::getProperty('socket_proxy');
        rex::setProperty('socket_proxy', null);
    }

    protected function tearDown()
    {
        rex::setProperty('socket_proxy', $this->proxy);
    }

    public function testFactory()
    {
        $socket = rex_socket_proxy::factory('www.example.com');
        $this->assertEquals('rex_socket_proxy', get_class($socket));
    }

    public function testFactoryUrl()
    {
        $socket = rex_socket_proxy::factoryUrl('www.example.com');
        $this->assertEquals('rex_socket_proxy', get_class($socket));
    }
}
