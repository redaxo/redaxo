<?php

class rex_context_test extends PHPUnit_Framework_TestCase
{
    public function testGetUrl()
    {
        $globalParams = ['int' => '25', 'str' => '<a b$c&?>'];
        $context = new rex_context($globalParams);

        $this->assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E', $context->getUrl(), 'parameters get properly encoded');
        $this->assertEquals('index.php?int=25&amp;str=xyz', $context->getUrl(['str' => 'xyz']), 'local params override global params');
        $this->assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E&amp;str2=xyz', $context->getUrl(['str2' => 'xyz']), 'new params are appended');
        $this->assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E&amp;myarr[0]=xyz&amp;myarr[1]=123', $context->getUrl(['myarr' => ['xyz', 123]]), 'numeric arrays are handled');
        $this->assertEquals('index.php?int=25&amp;str=%3Ca+b%24c%26%3F%3E&amp;myarr[a]=xyz&amp;myarr[b]=123', $context->getUrl(['myarr' => ['a' => 'xyz', 'b' => 123]]), 'assoc arrays are handled');
    }

    public function testGetHiddenInputFields()
    {
        $globalParams = ['int' => '25', 'str' => '<a b$c&?>'];
        $context = new rex_context($globalParams);

        $this->assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" />',
            $context->getHiddenInputFields(),
            'parameters get properly encoded'
        );

        $this->assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="xyz" />',
            $context->getHiddenInputFields(['str' => 'xyz']),
            'local params override global params'
        );

        $this->assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="str2" value="xyz" />',
            $context->getHiddenInputFields(['str2' => 'xyz']),
            'new params are appended'
        );

        $this->assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="myarr[0]" value="xyz" /><input type="hidden" name="myarr[1]" value="123" />',
            $context->getHiddenInputFields(['myarr' => ['xyz', 123]]),
            'numeric arrays are handled'
        );

        $this->assertEquals(
            '<input type="hidden" name="int" value="25" /><input type="hidden" name="str" value="&lt;a b$c&amp;?&gt;" /><input type="hidden" name="myarr[a]" value="xyz" /><input type="hidden" name="myarr[b]" value="123" />',
            $context->getHiddenInputFields(['myarr' => ['a' => 'xyz', 'b' => 123]]),
            'assoc arrays are handled'
        );
    }

    public function testFromGet()
    {
        $key = 'context_test_get';
        $_GET[$key] = 1;

        $context = rex_context::fromGet();

        $this->assertEquals($_GET[$key], $context->getParam($key));
    }

    public function testFromPost()
    {
        $key = 'context_test_post';
        $_POST[$key] = 'foo';

        $context = rex_context::fromPost();

        $this->assertEquals($_POST[$key], $context->getParam($key));
    }

    public function testRestore()
    {
        $keyGet = 'context_test_restore_1';
        $keyPost = 'context_test_restore_2';
        $_GET[$keyGet] = 'foo';
        $_POST[$keyPost] = 'bar';

        $context = rex_context::restore();

        $this->assertEquals($_GET[$keyGet], $context->getParam($keyGet));
        $this->assertEquals($_POST[$keyPost], $context->getParam($keyPost));
    }
}
