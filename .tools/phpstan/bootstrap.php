<?php

use staabm\PHPStanDba\QueryReflection\MysqliQueryReflector;
use staabm\PHPStanDba\QueryReflection\QueryReflection;

require_once __DIR__ . '/../../vendor/autoload.php';

if (false !== getenv('GITHUB_ACTION')) {
    $mysqli = new mysqli('127.0.0.1', 'root', 'root', 'redaxo5');
} else {
    // XXX somehow introspect the db settings from redaxo
    $mysqli = new mysqli('mysql80.ab', 'testuser', 'test', 'redaxo5');
}

QueryReflection::setupReflector(
    new MysqliQueryReflector($mysqli)
);
