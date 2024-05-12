<?php

namespace Redaxo\Core\Tests\ExtensionPoint;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/** @internal */
final class ExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIsRegistered(): void
    {
        $EP = 'TEST_IS_REGISTERED';

        self::assertFalse(Extension::isRegistered($EP), 'isRegistered() returns false for non-registered extension points');

        Extension::register($EP, static function () {});

        self::assertTrue(Extension::isRegistered($EP), 'isRegistered() returns true for registered extension points');
    }

    public function testRegisterPoint(): void
    {
        $EP = 'TEST_EP';

        $EPParam = null;
        Extension::register($EP, static function (ExtensionPoint $ep) use (&$EPParam) {
            $EPParam = $ep->getName();
            return $ep->getSubject() . ' test2';
        });

        Extension::register($EP, static function () {});

        Extension::register($EP, static function (ExtensionPoint $ep) {
            return $ep->getSubject() . ' test3';
        });

        $result = Extension::registerPoint(new ExtensionPoint($EP, 'test'));

        self::assertEquals($EP, $EPParam, '$params["extension_point"] contains the extension point name');
        self::assertEquals('test test2 test3', $result, 'registerPoint() returns the returned value of last extension');
    }

    public function testRegisterPointReadOnly(): void
    {
        $EP = 'TEST_EP_READ_ONLY';

        Extension::register($EP, static function () {
            return 'test2';
        });

        $subjectActual = null;
        Extension::register($EP, static function (ExtensionPoint $ep) use (&$subjectActual) {
            $subjectActual = $ep->getSubject();
        });

        $subject = 'test';
        Extension::registerPoint(new ExtensionPoint($EP, $subject, [], true));

        self::assertEquals($subject, $subjectActual, "read-only extention points don't change subject param");
    }

    public function testRegisterPointWithParams(): void
    {
        $EP = 'TEST_EP_WITH_PARAMS';

        $myparamActual = null;
        Extension::register($EP, static function (ExtensionPoint $ep) use (&$myparamActual) {
            $myparamActual = $ep->getParam('myparam');
        });

        $myparam = 'myparam';
        Extension::registerPoint(new ExtensionPoint($EP, null, ['myparam' => $myparam]));

        self::assertEquals($myparam, $myparamActual, 'additional params will be available in extentions');
    }

    public function testRegister(): void
    {
        $EP = 'TEST_EP_LEVELS';

        $callback = static function ($str) {
            return static function (ExtensionPoint $ep) use ($str) {
                return $ep->getSubject() . $str . ' ';
            };
        };

        Extension::register($EP, $callback('late1'), Extension::LATE);
        Extension::register($EP, $callback('normal1'));
        Extension::register($EP, $callback('early1'), Extension::EARLY);
        Extension::register($EP, $callback('late2'), Extension::LATE);
        Extension::register($EP, $callback('normal2'), Extension::NORMAL);
        Extension::register($EP, $callback('early2'), Extension::EARLY);

        $expected = 'early1 early2 normal1 normal2 late1 late2 ';
        $actual = Extension::registerPoint(new ExtensionPoint($EP, ''));

        self::assertEquals($expected, $actual);
    }

    public function testRegisterMultiple(): void
    {
        $EP1 = 'TEST_EP_MULTIPLE_1';
        $EP2 = 'TEST_EP_MULTIPLE_2';

        Extension::register([$EP1, $EP2], static function () {
            return 'foo';
        });

        /** @var string|null $subject */
        $subject = null;

        self::assertSame('foo', Extension::registerPoint(new ExtensionPoint($EP1, $subject)));
        self::assertSame('foo', Extension::registerPoint(new ExtensionPoint($EP2, $subject)));
    }
}
