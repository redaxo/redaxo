<?php

class rex_media_manager_test extends PHPUnit_Framework_TestCase
{
    public function testGetCacheFilename()
    {
        $media = new rex_managed_media(__DIR__.'/foo.jpg');
        $manager = new rex_media_manager($media);

        $cachePath = rex_path::addonCache('media_manager');
        $manager->setCachePath($cachePath);

        $media->setMediaPath(__DIR__.'/bar.gif');

        $property = new ReflectionProperty(rex_media_manager::class, 'type');
        $property->setAccessible(true);
        $property->setValue($manager, 'test');

        $this->assertSame($cachePath.'test/foo.jpg', $manager->getCacheFilename());
    }

    public function testGetMediaFile()
    {
        $_GET['rex_media_file'] = '../foo/bar/baz.jpg';
        $this->assertSame('baz.jpg', rex_media_manager::getMediaFile());

        $_GET['rex_media_file'] = '..\\foo\\bar\\baz.jpg';
        $this->assertSame('baz.jpg', rex_media_manager::getMediaFile());
    }
}
