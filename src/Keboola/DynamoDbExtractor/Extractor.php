<?php

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Nette\Utils\Strings;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Output\OutputInterface;
use Keboola\Component\Manifest\ManifestManager;

class Extractor
{
    /** @var array */
    private $parameters;

    /** @var DynamoDbClient */
    private $dynamoDbClient;

    /** @var OutputInterface */
    private $consoleOutput;

    public function __construct(array $config, OutputInterface $output)
    {
        // validate configuration with ConfigDefinition
        $this->parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']]
        );

        $this->consoleOutput = $output;

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

        $manifestManager = new ManifestManager($outputPath);

        foreach ($this->parameters['exports'] as $exportOptions) {
            $export = new Exporter($this->dynamoDbClient, $exportOptions, $outputPath, $this->consoleOutput);

            if ($export->hasEnabledExport()) {
                $filename = $export->export();
                if (file_exists($filename)) {
                    $webalizedExportName = Strings::webalize($exportOptions['name']);
                    $parser = new Parser(
                        $webalizedExportName,
                        $filename,
                        $exportOptions['mapping'],
                        $this->consoleOutput
                    );

                    $parser->parseAndWriteCsvFiles();
                    $export->cleanup();

                    $manifestOptions = [
                        'incremental' => $exportOptions['incremental'],
                    ];

                    if (isset($exportOptions['primaryKey'])) {
                        $manifestOptions['primary_key'] = $exportOptions['primaryKey'];
                    }

                    $manifestManager->writeTableManifestFromArray($outputPath . '/' .$webalizedExportName . '.csv', $manifestOptions);
                } else {
                    $this->consoleOutput->writeln('No documents found for export ' . $exportOptions['name']);
                }
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

        foreach ($exports as $export) {
            if (isset($export['dateFilter'])) {
                $this->validateDateFilter($export['dateFilter'], $export['name']);
            }
        }
    }

    /**
     * Validates export's "dateFilter"
     * @param array $dateFilter
     * @param string $exportName
     * @throws UserException
     */
    private function validateDateFilter(array $dateFilter, string $exportName)
    {
        if (!isset($dateFilter['field'], $dateFilter['format'], $dateFilter['value'])) {
            throw new UserException(
                'Please check if "dateFilter" contains all required parameters (field, format and value) '
                . 'in "'. $exportName . '" export'
            );
        }

        if (strtotime($dateFilter['value']) === false) {
            throw new UserException(
                'Please check "value" field of "dateFiler" in "'. $exportName . '" export'
            );
        }
    }
}
