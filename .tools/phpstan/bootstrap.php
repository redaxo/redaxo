<?php

use staabm\PHPStanDba\QueryReflection\MysqliQueryReflector;
use staabm\PHPStanDba\QueryReflection\QueryReflection;

require_once __DIR__ . '/../../vendor/autoload.php';

QueryReflection::setupReflector(
    new MysqliQueryReflector(new mysqli('127.0.0.1', 'root', 'root', 'redaxo5'))
);
