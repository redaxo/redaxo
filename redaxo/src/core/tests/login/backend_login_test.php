<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_backend_login_test extends TestCase
{
    private $skipped = false;

    private $login = 'testusr';
    private $password = 'test1234';
    private $cookiekey = 'mycookie';

    protected function setUp()
    {
        if (rex::getUser()) {
            $this->skipped = true;
            static::markTestSkipped('The rex_backend_login class can not be tested when test suite is running in redaxo backend.');
        }

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

    protected function tearDown()
    {
        if ($this->skipped) {
            return;
        }

        $deleteuser = rex_sql::factory();
        $deleteuser->setQuery('DELETE FROM ' . rex::getTablePrefix() . "user WHERE login = '". $this->login ."' LIMIT 1");

        // make sure we don't mess up the global scope
        session_destroy();
    }

    public function testSuccessfullLogin()
    {
        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        static::assertTrue($login->checkLogin());
    }

    public function testFailedLogin()
    {
        $login = new rex_backend_login();
        $login->setLogin($this->login, 'somethingwhichisnotcorrect', false);
        static::assertFalse($login->checkLogin());
    }

    /**
     * Test if a login is allowed after one failure before.
     */
    public function testSuccessfullReLogin()
    {
        $login = new rex_backend_login();

        $login->setLogin($this->login, 'somethingwhichisnotcorrect', false);
        static::assertFalse($login->checkLogin());

        $login->setLogin($this->login, $this->password, false);
        static::assertTrue($login->checkLogin());
    }

    /**
     * After LOGIN_TRIES_1 requests, the account should be not accessible for RELOGIN_DELAY_1 seconds.
     */
    public function testSuccessfullReLoginAfterLoginTries1Seconds()
    {
        $login = new rex_backend_login();

        for ($i = 0; $i < rex_backend_login::LOGIN_TRIES_1; ++$i) {
            $login->setLogin($this->login, 'somethingwhichisnotcorrect', false);
            static::assertFalse($login->checkLogin());
        }

        // we need to re-create login-objects because the time component is static in their sql queries
        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        static::assertFalse($login->checkLogin(), 'account locked after fast login attempts');

        sleep(1);

        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        static::assertFalse($login->checkLogin(), 'even seconds later account is locked');

        // FIXME Does not work at travis
        //sleep(rex_backend_login::RELOGIN_DELAY_1 + 2);

        //$login = new rex_backend_login();
        //$login->setLogin($this->login, $this->password, false);
        //$this->assertTrue($login->checkLogin(), 'after waiting the account should be unlocked');
    }

    public function testLogout()
    {
        $login = new rex_backend_login();
        $login->setLogin($this->login, $this->password, false);
        static::assertTrue($login->checkLogin());
        $login->setLogout(true);
        static::assertFalse($login->checkLogin());
    }
}
