<?php

require __DIR__ . '/vendor/autoload.php';

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
    'version' => '2012-08-10'
]);

try {
    $dynamoDb->deleteTable([
        'TableName' => 'Movies',
    ]);
    $consoleOutput->writeln('Table deleted.');
} catch (Exception $e) {
    $consoleOutput->writeln('Table not deleted.');
}

$params = [
    'TableName' => 'Movies',
    'KeySchema' => [
        [
            'AttributeName' => 'year',
            'KeyType' => 'HASH'  //Partition key
        ],
        [
            'AttributeName' => 'title',
            'KeyType' => 'RANGE'  //Sort key
        ]
    ],
    'AttributeDefinitions' => [
        [
            'AttributeName' => 'year',
            'AttributeType' => 'S'
        ],
        [
            'AttributeName' => 'title',
            'AttributeType' => 'S'
        ],

    ],
    'ProvisionedThroughput' => [
        'ReadCapacityUnits' => 10,
        'WriteCapacityUnits' => 10
    ]
];

try {
    $result = $dynamoDb->createTable($params);
    echo 'Created table.  Status: ' .
        $result['TableDescription']['TableStatus'] ."\n";


    // load sample data

    $marshaler = new Marshaler();

    $movies = json_decode(file_get_contents(__DIR__ . '/tests/moviedata-10.json'), true);

    foreach ($movies as $movie) {
        $year = $movie['year'];
        $title = $movie['title'];
        $info = $movie['info'];

        $json = json_encode([
            'year' => strval($year),
            'title' => $title,
            'info' => $info
        ]);

        $params = [
            'TableName' => 'Movies',
            'Item' => $marshaler->marshalJson($json)
        ];

        $result = $dynamoDb->putItem($params);
        echo "Added movie: " . $movie['year'] . " " . $movie['title'] . "\n";
    }
} catch (DynamoDbException $e) {
    echo "Error:\n";
    echo $e->getMessage() . "\n";
}
