<?php

use PHPUnit\Framework\TestCase;

/** @internal */
final class rex_context_test extends TestCase
{
    public function testGetUrl(): void
    {
        $globalParams = ['int' => '25', 'str' => '<a b$c&?>'];
        $context = new rex_context($globalParams);

        self::assertEquals('index.php?int=25&str=%3Ca+b%24c%26%3F%3E', $context->getUrl(), 'parameters get properly encoded');
        self::assertEquals('index.php?int=25&str=xyz', $context->getUrl(['str' => 'xyz']), 'local params override global params');
        self::assertEquals('index.php?int=25&str=%3Ca+b%24c%26%3F%3E&str2=xyz', $context->getUrl(['str2' => 'xyz']), 'new params are appended');
        self::assertEquals('index.php?int=25&str=%3Ca+b%24c%26%3F%3E&myarr[0]=xyz&myarr[1]=123', $context->getUrl(['myarr' => ['xyz', 123]]), 'numeric arrays are handled');
        self::assertEquals('index.php?int=25&str=%3Ca+b%24c%26%3F%3E&myarr[a]=xyz&myarr[b]=123', $context->getUrl(['myarr' => ['a' => 'xyz', 'b' => 123]]), 'assoc arrays are handled');
    }

    public function testGetHiddenInputFields(): void
    {
        $globalParams = ['int' => '25', 'str' => '<a b$c&?>'];
        $context = new rex_context($globalParams);

        self::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" />',
            $context->getHiddenInputFields(),
            'parameters get properly encoded',
        );

        self::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="&lt;mystr&gt;" value="abc" />',
            $context->getHiddenInputFields(['<mystr>' => 'abc']),
            'names get properly encoded',
        );

        self::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="xyz" />',
            $context->getHiddenInputFields(['str' => 'xyz']),
            'local params override global params',
        );

        self::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="str2" value="xyz" />',
            $context->getHiddenInputFields(['str2' => 'xyz']),
            'new params are appended',
        );

        self::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="myarr[0]" value="xyz" /><input type="hidden" name="myarr[1]" value="123" />',
            $context->getHiddenInputFields(['myarr' => ['xyz', 123]]),
            'numeric arrays are handled',
        );

        self::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="myarr[a]" value="xyz" /><input type="hidden" name="myarr[b]" value="123" />',
            $context->getHiddenInputFields(['myarr' => ['a' => 'xyz', 'b' => 123]]),
            'assoc arrays are handled',
        );
    }

    public function testFromGet(): void
    {
        $key = 'context_test_get';
        $_GET[$key] = 1;

        $context = rex_context::fromGet();

        self::assertEquals($_GET[$key], $context->getParam($key));
    }

    public function testFromPost(): void
    {
        $key = 'context_test_post';
        $_POST[$key] = 'foo';

        $context = rex_context::fromPost();

        self::assertEquals($_POST[$key], $context->getParam($key));
    }

    public function testRestore(): void
    {
        $keyGet = 'context_test_restore_1';
        $keyPost = 'context_test_restore_2';
        $_GET[$keyGet] = 'foo';
        $_POST[$keyPost] = 'bar';

        $context = rex_context::restore();

        self::assertEquals($_GET[$keyGet], $context->getParam($keyGet));
        self::assertEquals($_POST[$keyPost], $context->getParam($keyPost));
    }
}
