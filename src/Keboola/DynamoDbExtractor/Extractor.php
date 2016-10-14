<?php

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Symfony\Component\Config\Definition\Processor;

class Extractor
{
    /** @var array */
    private $parameters;

    /** @var DynamoDbClient */
    private $dynamoDbClient;

    public function __construct(array $config)
    {
        $this->parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']]
        );

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

    public function actionTestConnection(): array
    {
        $this->dynamoDbClient->listTables();

        return [
            'status' => 'ok'
        ];
    }

    public function actionRun(string $outputPath)
    {
    }
}
