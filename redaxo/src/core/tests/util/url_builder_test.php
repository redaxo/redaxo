<?php

class rex_url_builder_test extends PHPUnit_Framework_TestCase
{
  public function testRelatviveBased()
  {
    $newUrl = 'index.php?myparam1=1&myparam2=2&abc=def';

    $builder = new rex_url_builder('index.php');
    $builder->addParams(array('myparam1' => 1, 'myparam2' => 2));
    $builder->setParam('abc', 'def');

    $this->assertEquals($newUrl, $builder->getUrl(), 'url correctly rebuild');
  }

  public function testGetParam()
  {
    $url = 'http://www.example.org:81/a-path/to/my-page?myparam=1&myparam2=2#my-anchor';
    $builder = new rex_url_builder($url);

    $this->assertEquals('1', $builder->getParam('myparam', 'XX'), 'param value returned');
    $this->assertEquals('2', $builder->getParam('myparam2', 'YY'), 'param value returned');
    $this->assertEquals('1234', $builder->getParam('another_param', '1234'), 'param default-value returned');
  }

  public function testSetNewParam()
  {
    $url    = 'http://www.example.org:81/a-path/to/my-page?myparam=1&myparam2=2#my-anchor';
    $newUrl = 'http://www.example.org:81/a-path/to/my-page?myparam=1&myparam2=2&another_param=X#my-anchor';
    $builder = new rex_url_builder($url);
    $builder->setParam('another_param', 'X');

    $this->assertEquals('1', $builder->getParam('myparam'), 'param value returned');
    $this->assertEquals('2', $builder->getParam('myparam2'), 'param value returned');
    $this->assertEquals('X', $builder->getParam('another_param'), 'param value returned');

    $this->assertEquals($newUrl, $builder->getUrl(), 'url correctly rebuild');
  }

  public function testResetExistingParam()
  {
    $url    = 'http://www.example.org/a-path/to/my-page?myparam=1&myparam2=2#my-anchor';
    $newUrl = 'http://www.example.org/a-path/to/my-page?myparam=X&myparam2=2#my-anchor';
    $builder = new rex_url_builder($url);

    $this->assertEquals('1', $builder->getParam('myparam'), 'param value returned');
    $this->assertEquals('2', $builder->getParam('myparam2'), 'param value returned');

    $builder->setParam('myparam', 'X');

    $this->assertEquals('X', $builder->getParam('myparam'), 'param value returned');
    $this->assertEquals('2', $builder->getParam('myparam2'), 'param value returned');

    $this->assertEquals($newUrl, $builder->getUrl(), 'url correctly rebuild');
  }

  public function testRemoveParam()
  {
    $url    = 'http://www.example.org:81/a-path/to/my-page?myparam=1&myparam2=2#my-anchor';
    $newUrl = 'http://www.example.org:81/a-path/to/my-page?myparam=1#my-anchor';
    $builder = new rex_url_builder($url);

    $this->assertEquals('1', $builder->getParam('myparam'), 'param value returned');
    $this->assertEquals('2', $builder->getParam('myparam2'), 'param value returned');

    $builder->removeParam('myparam2');

    $this->assertEquals('1', $builder->getParam('myparam'), 'param value returned');
    $this->assertEquals(null, $builder->getParam('myparam2'), 'param value returned');

    $this->assertEquals($newUrl, $builder->getUrl(), 'url correctly rebuild');
  }

  public function testAddParams()
  {
    $url    = 'http://www.example.org:81/a-path/to/my-page?myparam=1&myparam2=2#my-anchor';
    $newUrl = 'http://www.example.org:81/a-path/to/my-page?myparam=1&myparam2=2X&myparam3=3#my-anchor';
    $builder = new rex_url_builder($url);

    $builder->addParams(array('myparam2' => '2X', 'myparam3' => 3));

    $this->assertEquals('2X', $builder->getParam('myparam2'), 'param value returned');
    $this->assertEquals(3, $builder->getParam('myparam3'), 'param value returned');

    $this->assertEquals($newUrl, $builder->getUrl(), 'url correctly rebuild');
  }
}
