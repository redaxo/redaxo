<?php

class rex_test_locator implements IteratorAggregate
{
  const TESTS_FOLDER = 'tests';

  private $testFoldersIterator;

  public function __construct()
  {
    $this->testFoldersIterator = new AppendIterator();

    $this->addTestFolder( rex_path::core( self::TESTS_FOLDER ));
    foreach( rex_addon::getAvailableAddons() as $addon )
    {
      $this->addTestFolder( $addon->getBasePath( self::TESTS_FOLDER ));
      foreach( $addon->getAvailablePlugins() as $plugin )
      {
        $this->addTestFolder( $plugin->getBasePath( self::TESTS_FOLDER ));
      }
    }
  }

  private function addTestFolder($folder)
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
}
