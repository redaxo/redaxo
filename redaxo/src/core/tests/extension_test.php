<?php

class rex_extension_test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testIsRegistered()
    {
        $EP = 'TEST_IS_REGISTERED';

        $this->assertFalse(rex_extension::isRegistered($EP), 'isRegistered() returns false for non-registered extension points');

        rex_extension::register($EP, function () {
        });

        $this->assertTrue(rex_extension::isRegistered($EP), 'isRegistered() returns true for registered extension points');
    }

    public function testRegisterPoint()
    {
        $EP = 'TEST_EP';

        $EPParam = null;
        rex_extension::register($EP, function ($params) use (&$EPParam) {
            $EPParam = $params['extension_point'];
            return $params['subject'] . ' test2';
        });

        rex_extension::register($EP, function ($params) {
        });

        rex_extension::register($EP, function ($params) {
            return $params['subject'] . ' test3';
        });

        $result = rex_extension::registerPoint($EP, 'test');

        $this->assertEquals($EP, $EPParam, '$params["extension_point"] contains the extension point name');
        $this->assertEquals('test test2 test3', $result, 'registerPoint() returns the returned value of last extension');
    }

    public function testRegisterPointReadOnly()
    {
        $EP = 'TEST_EP_READ_ONLY';

        rex_extension::register($EP, function ($params) {
            return 'test2';
        });

        $subjectActual = null;
        rex_extension::register($EP, function ($params) use (&$subjectActual) {
            $subjectActual = $params['subject'];
        });

        $subject = 'test';
        rex_extension::registerPoint($EP, $subject, array(), true);

        $this->assertEquals($subject, $subjectActual, 'read-only extention points don\'t change subject param');
    }

    public function testRegisterPointWithParams()
    {
        $EP = 'TEST_EP_WITH_PARAMS';

        $myparamActual = null;
        rex_extension::register($EP, function ($params) use (&$myparamActual) {
            $myparamActual = $params['myparam'];
        });

        $myparam = 'myparam';
        rex_extension::registerPoint($EP, null, array('myparam' => $myparam));

        $this->assertEquals($myparam, $myparamActual, 'additional params will be available in extentions');
    }

    public function testRegister()
    {
        $EP = 'TEST_EP_LEVELS';

        $callback = function ($str) {
            return function ($params) use ($str) {
                return $params['subject'] . $str . ' ';
            };
        };

        rex_extension::register($EP, $callback('late1'),   rex_extension::LATE);
        rex_extension::register($EP, $callback('normal1'));
        rex_extension::register($EP, $callback('early1'),  rex_extension::EARLY);
        rex_extension::register($EP, $callback('late2'),   rex_extension::LATE);
        rex_extension::register($EP, $callback('normal2'), rex_extension::NORMAL);
        rex_extension::register($EP, $callback('early2'),  rex_extension::EARLY);

        $expected = 'early1 early2 normal1 normal2 late1 late2 ';
        $actual = rex_extension::registerPoint($EP, '');

        $this->assertEquals($expected, $actual);
    }
}
