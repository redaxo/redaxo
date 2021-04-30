<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_be_navigation_test extends TestCase
{
    public function testSetPrio(): void
    {
        $navi = rex_be_navigation::factory();
        $navi->addPage((new rex_be_page_main('addons', 'addon', 'addon'))->setIsActive(false));
        $navi->addPage((new rex_be_page_main('system', 'system', 'system'))->setIsActive(true));
        $navi->addPage((new rex_be_page_main('test', 'test', 'test'))->setIsActive(false));

        $navi->setHeadline('test', 'Test');
        $navi->setHeadline('system', 'System');
        $navi->setHeadline('addons', 'Addons');

        $navi->setPrio('test', 15);

        $user = rex::getUser();

        try {
            rex::setProperty('user', new rex_user(rex_sql::factory()));
            $navi = $navi->getNavigation();
        } finally {
            rex::setProperty('user', $user);
        }

        static::assertSame('System', $navi[0]['headline']['title']);
        static::assertSame('Test', $navi[1]['headline']['title']);
        static::assertSame('Addons', $navi[2]['headline']['title']);
    }
}
