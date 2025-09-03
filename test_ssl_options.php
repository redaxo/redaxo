<?php

// Temporärer Test für SSL Options Creation
require_once __DIR__ . '/vendor/autoload.php';

// Test-Daten
$testConfig = [
    'ssl_key' => '/path/to/key.pem',
    'ssl_cert' => '/path/to/cert.pem',
    'ssl_ca' => '/path/to/ca.pem',
    'ssl_verify_server_cert' => true,
];

$testConfigBoolean = [
    'ssl_key' => '/path/to/key.pem',
    'ssl_cert' => '/path/to/cert.pem',
    'ssl_ca' => true,  // Boolean CA mode
    'ssl_verify_server_cert' => false,
];

echo "Testing SSL options creation...\n";

try {
    $options1 = rex_sql::createSslOptions($testConfig);
    echo "Test 1 (with CA file path): " . count($options1) . " options created\n";
    var_dump($options1);

    $options2 = rex_sql::createSslOptions($testConfigBoolean);
    echo "Test 2 (with CA boolean): " . count($options2) . " options created\n";
    var_dump($options2);

    echo "SSL options creation works correctly!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Clean up
unlink(__FILE__);
