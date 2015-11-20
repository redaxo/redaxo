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

    public function testFailedLogin()
    {
        $login = new rex_backend_login();
        $login->setLogin($this->login, 'somethingwhichisnotcorrect', false);
        $this->assertFalse($login->checkLogin());
    }

    /**
     * Test if a login is allowed after one failure before
     */
    public function testSuccessfullReLogin()
    {
        $login = new rex_backend_login();

        $login->setLogin($this->login, 'somethingwhichisnotcorrect', false);
        $this->assertFalse($login->checkLogin());

        $login->setLogin($this->login, $this->password, false);
        $this->assertTrue($login->checkLogin());
    }


    /**
     * After LOGIN_TRIES_1 requests, the account should be not accessible for RELOGIN_DELAY_1 seconds
     */
    public function testSuccessfullReLoginAfterLoginTries1Seconds()
    {
        $login = new rex_backend_login();

        for($i = 0; $i < rex_backend_login::LOGIN_TRIES_1; $i++) {
            $login->setLogin($this->login, 'somethingwhichisnotcorrect', false);
            $this->assertFalse($login->checkLogin());
        }

        // we need to re-create login-objects because the time component is static in their sql queries
        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        $this->assertFalse($login->checkLogin(), 'account locked after fast login attempts');

        sleep(1);

        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        $this->assertFalse($login->checkLogin(), 'even seconds later account is locked');

        sleep(rex_backend_login::RELOGIN_DELAY_1 + 2);

        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        $this->assertTrue($login->checkLogin(), 'after waiting the account should be unlocked');
    }


    public function testLogout()
    {
        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        $this->assertTrue($login->checkLogin());
        $login->setLogout(true);
        $this->assertFalse($login->checkLogin());
    }

    public function tearDown()
    {
        $deleteuser = rex_sql::factory();
        $deleteuser->setQuery('DELETE FROM ' . rex::getTablePrefix() . "user WHERE login = '". $this->login ."' LIMIT 1");

        // make sure we don't mess up the global scope
        session_destroy();
    }
}
