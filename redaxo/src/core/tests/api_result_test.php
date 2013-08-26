<?php

class rex_api_result_test extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $result = new rex_api_result(true, 'lala');
        $this->assertSame(true, $result->isSuccessfull());
        $this->assertSame('lala', $result->getMessage());
    }

    public function testEmptyToJsonData()
    {
        $result = new rex_api_result(true, 'a message');
        $this->assertSame('[]', $result->toJsonData());
    }

    public function testToJsonData()
    {
        $result = new rex_api_result(true, 'a message');
        $result->data = 'yes';
        $result->array = [1, 2, 3];
        $this->assertSame('{"data":"yes","array":[1,2,3]}', $result->toJsonData());
    }

    public function testEmptyToJson()
    {
        $result = new rex_api_result(true, 'a message');
        $this->assertSame('{"succeeded":true,"message":"a message","requiresReboot":false}', $result->toJson());
    }

    public function testToJson()
    {
        $result = new rex_api_result(true, 'a message');
        $result->data = 'yes';
        $result->array = [1, 2, 3];
        $this->assertSame('{"succeeded":true,"message":"a message","requiresReboot":false,"data":"yes","array":[1,2,3]}', $result->toJson());
    }
}
