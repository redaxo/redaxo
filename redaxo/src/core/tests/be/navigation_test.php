<?php

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;

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

        $user = Core::getUser();

        try {
            Core::setProperty('user', new rex_user(Sql::factory()));
            $navi = $navi->getNavigation();
        } finally {
            Core::setProperty('user', $user);
        }

        self::assertSame('System', $navi[0]['headline']['title']);
        self::assertSame('Test', $navi[1]['headline']['title']);
        self::assertSame('Addons', $navi[2]['headline']['title']);
    }
}
