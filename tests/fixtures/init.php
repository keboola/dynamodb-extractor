<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Symfony\Component\Console\Output\ConsoleOutput;

$consoleOutput = new ConsoleOutput;

$dynamoDb = new DynamoDbClient([
    'endpoint' => 'http://dynamodb:8000',
    'credentials' => [
        'key' => 'key',
        'secret' => 'secret',
    ],
    'region' => 'eu-central-1',
    'version' => '2012-08-10',
]);

try {
    $dynamoDb->deleteTable([
        'TableName' => 'Movies',
    ]);
    $consoleOutput->writeln('Table deleted.');
} catch (Throwable $e) {
    $consoleOutput->writeln('Table not deleted.');
}

$params = [
    'TableName' => 'Movies',
    'KeySchema' => [
        [
            'AttributeName' => 'year',
            'KeyType' => 'HASH',
        ],
        [
            'AttributeName' => 'title',
            'KeyType' => 'RANGE',
        ],
    ],
    'AttributeDefinitions' => [
        [
            'AttributeName' => 'year',
            'AttributeType' => 'S',
        ],
        [
            'AttributeName' => 'title',
            'AttributeType' => 'S',
        ],
    ],
    'ProvisionedThroughput' => [
        'ReadCapacityUnits' => 10,
        'WriteCapacityUnits' => 10,
    ],
    'GlobalSecondaryIndexes' => [
        [
            'IndexName' => 'Movies_title',
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 10,
                'WriteCapacityUnits' => 10,
            ],
            'KeySchema' => [
                [
                    'AttributeName' => 'title',
                    'KeyType' => 'HASH',
                ],
                [
                    'AttributeName' => 'year',
                    'KeyType' => 'RANGE',
                ],
            ],
            'Projection' => [
                'ProjectionType' => 'ALL',
            ],
        ],
    ],
];

try {
    $result = $dynamoDb->createTable($params);
    echo 'Created table.  Status: ' .
        $result['TableDescription']['TableStatus'] . "\n";

    $marshaler = new Marshaler();

    $movies = json_decode(file_get_contents(__DIR__ . '/moviedata-10.json'), true);

    foreach ($movies as $movie) {
        $movie['year'] = strval($movie['year']);
        $json = json_encode($movie);

        $params = [
            'TableName' => 'Movies',
            'Item' => $marshaler->marshalJson($json),
        ];

        $result = $dynamoDb->putItem($params);
        echo 'Added movie: ' . $movie['year'] . ' ' . $movie['title'] . "\n";
    }
} catch (DynamoDbException $e) {
    echo "Error:\n";
    echo $e->getMessage() . "\n";
}
