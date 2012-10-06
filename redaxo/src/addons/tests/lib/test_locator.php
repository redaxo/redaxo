<?php

use Symfony\Component\Finder\Finder;

class rex_test_locator implements IteratorAggregate
{
  const TESTS_FOLDER = 'tests';

  private $finder;

  public function __construct()
  {
    $this->finder = Finder::create()->files();
  }

  public function addTestFolder($folder)
  {
    if (is_dir($folder)) {
      rex_autoload::addDirectory($folder);

      $this->finder->in($folder);
    }
  }

  public function getIterator()
  {
    return $this->finder->getIterator();
  }

  static public function defaultLocator()
  {
    $locator = new self();

    $locator->addTestFolder(rex_path::core(self::TESTS_FOLDER));
    foreach (rex_package::getAvailablePackages() as $package) {
      $locator->addTestFolder($package->getBasePath(self::TESTS_FOLDER));
    }
    return $locator;
  }
}
