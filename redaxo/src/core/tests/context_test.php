<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_context_test extends TestCase
{
    public function testGetUrl(): void
    {
        $globalParams = ['int' => '25', 'str' => '<a b$c&?>'];
        $context = new rex_context($globalParams);

        static::assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E', $context->getUrl(), 'parameters get properly encoded');
        static::assertEquals('index.php?int=25&amp;str=xyz', $context->getUrl(['str' => 'xyz']), 'local params override global params');
        static::assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E&amp;str2=xyz', $context->getUrl(['str2' => 'xyz']), 'new params are appended');
        static::assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E&amp;myarr[0]=xyz&amp;myarr[1]=123', $context->getUrl(['myarr' => ['xyz', 123]]), 'numeric arrays are handled');
        static::assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E&amp;myarr[a]=xyz&amp;myarr[b]=123', $context->getUrl(['myarr' => ['a' => 'xyz', 'b' => 123]]), 'assoc arrays are handled');
    }

    public function testGetHiddenInputFields(): void
    {
        $globalParams = ['int' => '25', 'str' => '<a b$c&?>'];
        $context = new rex_context($globalParams);

        static::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" />',
            $context->getHiddenInputFields(),
            'parameters get properly encoded',
        );

        static::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="&lt;mystr&gt;" value="abc" />',
            $context->getHiddenInputFields(['<mystr>' => 'abc']),
            'names get properly encoded',
        );

        static::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="xyz" />',
            $context->getHiddenInputFields(['str' => 'xyz']),
            'local params override global params',
        );

        static::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="str2" value="xyz" />',
            $context->getHiddenInputFields(['str2' => 'xyz']),
            'new params are appended',
        );

        static::assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="myarr[0]" value="xyz" /><input type="hidden" name="myarr[1]" value="123" />',
            $context->getHiddenInputFields(['myarr' => ['xyz', 123]]),
            'numeric arrays are handled',
        );

        static::assertEquals(
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

        static::assertEquals($_GET[$key], $context->getParam($key));
    }

    public function testFromPost(): void
    {
        $key = 'context_test_post';
        $_POST[$key] = 'foo';

        $context = rex_context::fromPost();

        static::assertEquals($_POST[$key], $context->getParam($key));
    }

    public function testRestore(): void
    {
        $keyGet = 'context_test_restore_1';
        $keyPost = 'context_test_restore_2';
        $_GET[$keyGet] = 'foo';
        $_POST[$keyPost] = 'bar';

        $context = rex_context::restore();

        static::assertEquals($_GET[$keyGet], $context->getParam($keyGet));
        static::assertEquals($_POST[$keyPost], $context->getParam($keyPost));
    }
}
