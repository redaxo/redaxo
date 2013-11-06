<?php
class rex_backend_login_test extends PHPUnit_Framework_TestCase
{
    private $login = 'testusr', $password = 'test1234', $cookiekey = 'mycookie';

    public function setUp()
    {
        parent::setUp();

        $adduser = rex_sql::factory();
        $adduser->setTable(rex::getTablePrefix() . 'user');
        $adduser->setValue('name', 'test user');
        $adduser->setValue('login', $this->login);
        $adduser->setValue('password', rex_login::passwordHash($this->password));
        $adduser->setValue('status', '1');
        $adduser->setValue('login_tries', '0');
        $adduser->setValue('cookiekey', $this->cookiekey);
        $adduser->insert();
    }

    public function testSuccessfullLogin()
    {
        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        $this->assertTrue($login->checkLogin());
    }

    public function tearDown()
    {
        $deleteuser = rex_sql::factory();
        $deleteuser->setQuery('DELETE FROM ' . rex::getTablePrefix() . "user WHERE login = '". $this->login ."' LIMIT 1");

        // make sure we don't mess up the global scope
        // session_destroy();
    }
}
