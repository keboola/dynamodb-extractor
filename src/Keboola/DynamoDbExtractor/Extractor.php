<?php

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Nette\Utils\Strings;
use Symfony\Component\Config\Definition\Processor;

class Extractor
{
    /** @var array */
    private $parameters;

    /** @var DynamoDbClient */
    private $dynamoDbClient;

    public function __construct(array $config)
    {
        // validate configuration with ConfigDefinition
        $this->parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']]
        );

        // create DynamoDbClient instance
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

    /**
     * Tests connection to database by listing tables
     * @return array
     */
    public function actionTestConnection(): array
    {
        $this->dynamoDbClient->listTables();

        return [
            'status' => 'ok'
        ];
    }

    /**
     * Runs data extraction
     * @param string $outputPath
     */
    public function actionRun(string $outputPath): void
    {
        $this->validateExports($this->parameters['exports']);

        foreach ($this->parameters['exports'] as $exportOptions) {
            $export = new Export($this->dynamoDbClient, $exportOptions, $outputPath);

            if ($export->isEnabled()) {
                $filename = $export->export();
                $parser = new Parser(
                    Strings::webalize($exportOptions['name']),
                    $filename,
                    $exportOptions['mapping']
                );
                $parser->parseAndWriteCsvFiles();
                $export->cleanup();
            }
        }
    }

    /**
     * Validates exports
     * @param array $exports
     * @throws UserException
     */
    private function validateExports(array $exports): void
    {
        // check if there are enabled exports
        if (array_sum(array_column($exports, 'enabled')) === 0) {
            throw new UserException('Please enable at least one export');
        }

        // check for duplicate export names
        if (count($exports) !== count(array_unique(array_column($exports, 'name')))) {
            throw new UserException('Please remove duplicate export names');
        }
    }
}
