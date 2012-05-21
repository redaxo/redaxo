<?php

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
    if (is_dir($folder))
    {
      rex_autoload::addDirectory($folder);

      $this->testFoldersIterator->append(
          rex_dir::recursiveIterator($folder, rex_dir_recursive_iterator::LEAVES_ONLY)->ignoreSystemStuff()
      );
    }
  }

  public function getIterator()
  {
    return $this->testFoldersIterator;
  }

  static public function defaultLocator()
  {
    $locator = new rex_test_locator();

    $locator->addTestFolder(rex_path::core(self::TESTS_FOLDER));
    foreach (rex_package::getAvailablePackages() as $package)
    {
      $locator->addTestFolder($package->getBasePath(self::TESTS_FOLDER));
    }
    return $locator;
  }
}
