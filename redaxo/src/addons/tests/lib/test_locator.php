<?php

/**
 * @package redaxo\tests
 *
 * @internal
 */
class rex_test_locator implements IteratorAggregate
{
    const TESTS_FOLDER = 'tests';

    private $testFoldersIterator;

    public function __construct()
    {
        $this->testFoldersIterator = new AppendIterator();
    }

    public function addTestFolder($folder)
    {
        if (is_dir($folder)) {
            rex_autoload::addDirectory($folder);

            $this->testFoldersIterator->append(
                rex_finder::factory($folder)->recursive()->filesOnly()->getIterator()
            );
        }
    }

    public function getIterator()
    {
        return $this->testFoldersIterator;
    }

    public static function defaultLocator()
    {
        $locator = new self();

        $locator->addTestFolder(rex_path::core(self::TESTS_FOLDER));
        foreach (rex_package::getAvailablePackages() as $package) {
            $locator->addTestFolder($package->getPath(self::TESTS_FOLDER));
        }
        return $locator;
    }
}
