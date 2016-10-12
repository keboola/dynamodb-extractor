<?php

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;

class Extractor
{
    /** @var array */
    private $parameters;

    /** @var DynamoDbClient */
    private $dynamoDbClient;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;

        $this->dynamoDbClient = new DynamoDbClient([
            'endpoint' => $this->parameters['db']['endpoint'],
            'credentials' => [
                'key' => $this->parameters['db']['accessKeyId'],
                'secret' => $this->parameters['db']['#secretAccessKey'],
            ],
            'region' => $this->parameters['db']['regionName'],
            'version' => '2012-08-10'
        ]);
    }

    public function testConnection()
    {
        $this->dynamoDbClient->listTables();
    }
}
