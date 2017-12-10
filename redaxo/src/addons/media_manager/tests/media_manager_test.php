<?php

class rex_media_manager_test extends PHPUnit_Framework_TestCase
{
    public function testGetCacheFilename()
    {
        $manager = new rex_media_manager(new rex_managed_media(__DIR__.'/foo.jpg'));

        $cachePath = rex_path::addonCache('media_manager');
        $manager->setCachePath($cachePath);

        $property = new ReflectionProperty(rex_media_manager::class, 'type');
        $property->setAccessible(true);
        $property->setValue($manager, 'test');

        $_GET['rex_media_file'] = 'bar.gif';

        $this->assertSame($cachePath.'test/bar.gif', $manager->getCacheFilename());
    }
}
