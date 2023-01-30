<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_backend_login_test extends TestCase
{
    private const LOGIN = 'testusr';
    private const PASSWORD = 'test1234';
    private const COOKIEKEY = 'mycookie';

    protected function setUp(): void
    {
        $adduser = rex_sql::factory();
        $adduser->setTable(rex::getTablePrefix() . 'user');
        $adduser->setValue('name', 'test user');
        $adduser->setValue('login', self::LOGIN);
        $adduser->setValue('password', rex_login::passwordHash(self::PASSWORD));
        $adduser->setValue('status', '1');
        $adduser->setValue('login_tries', '0');
        $adduser->setValue('cookiekey', self::COOKIEKEY);
        $adduser->insert();
    }

    protected function tearDown(): void
    {
        $deleteuser = rex_sql::factory();
        $deleteuser->setQuery('DELETE FROM ' . rex::getTablePrefix() . "user WHERE login = '". self::LOGIN ."' LIMIT 1");
    }

    public function testSuccessfullLogin()
    {
        $login = new rex_backend_login();
        $login->setLogin(self::LOGIN, self::PASSWORD, false);
        static::assertTrue($login->checkLogin());
    }

    public function testFailedLogin()
    {
        $login = new rex_backend_login();
        $login->setLogin(self::LOGIN, 'somethingwhichisnotcorrect', false);
        static::assertFalse($login->checkLogin());
    }

    /**
     * Test if a login is allowed after one failure before.
     */
    public function testSuccessfullReLogin()
    {
        $login = new rex_backend_login();

        $login->setLogin(self::LOGIN, 'somethingwhichisnotcorrect', false);
        static::assertFalse($login->checkLogin());

        $login->setLogin(self::LOGIN, self::PASSWORD, false);
        static::assertTrue($login->checkLogin());
    }

    /**
     * After LOGIN_TRIES requests, the account should be not accessible for RELOGIN_DELAY seconds.
     */
    public function testSuccessfullReLoginAfterLoginTriesSeconds()
    {
        $login = new rex_backend_login();
        $tries = $login->getLoginPolicy()->getMaxTriesUntilDelay();

        for ($i = 0; $i < $tries; ++$i) {
            $login->setLogin(self::LOGIN, 'somethingwhichisnotcorrect', false);
            static::assertFalse($login->checkLogin());
        }

        // we need to re-create login-objects because the time component is static in their sql queries
        $login = new rex_backend_login();
        $login->setLogin(self::LOGIN, self::PASSWORD, false);
        static::assertFalse($login->checkLogin(), 'account locked after fast login attempts');

        sleep(1);

        $login = new rex_backend_login();
        $login->setLogin(self::LOGIN, self::PASSWORD, false);
        static::assertFalse($login->checkLogin(), 'even seconds later account is locked');

        sleep($login->getLoginPolicy()->getReloginDelay() + 1);

        $login = new rex_backend_login();
        $login->setLogin(self::LOGIN, self::PASSWORD, false);
        static::assertTrue($login->checkLogin(), 'after waiting the account should be unlocked');
    }

    public function testLogout()
    {
        $login = new rex_backend_login();
        $login->setLogin(self::LOGIN, self::PASSWORD, false);
        static::assertTrue($login->checkLogin());
        $login->setLogout(true);
        static::assertFalse($login->checkLogin());
    }
}
