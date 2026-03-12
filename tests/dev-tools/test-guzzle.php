<?php

require __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client;

echo "Testing Guzzle HTTP client...\n";

$client = new Client([
    'timeout' => 5,
    'verify' => false,
]);

try {
    $response = $client->get('https://httpbin.org/get');
    echo "Status: " . $response->getStatusCode() . "\n";
} catch (\Exception $e) {
    echo "Request error: " . $e->getMessage() . "\n";
}

echo "Guzzle test finished.\n";
