<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\Component\Manifest\ManifestManager\Options\OutTableManifestOptions;
use Nette\Utils\Strings;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Output\OutputInterface;

class Extractor
{
    private array $parameters;

    private DynamoDbClient $dynamoDbClient;

    private OutputInterface $consoleOutput;

    public function __construct(array $config, OutputInterface $output)
    {
        // validate configuration with ConfigDefinition
        $this->parameters = (new Processor)->processConfiguration(
            new ConfigDefinition,
            [$config['parameters']],
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
            'version' => '2012-08-10',
        ]);
    }

    /**
     * Tests connection to database by listing tables
     */
    public function actionTestConnection(): array
    {
        $this->dynamoDbClient->listTables();

        return [
            'status' => 'ok',
        ];
    }

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
                        $this->consoleOutput,
                    );

                    $parser->parseAndWriteCsvFiles();
                    $export->cleanup();

                    $manifestOptions = new OutTableManifestOptions();
                    $manifestOptions->setIncremental($exportOptions['incremental']);

                    if (isset($exportOptions['primaryKey'])) {
                        $manifestOptions->setPrimaryKeyColumns($exportOptions['primaryKey']);
                    }

                    $manifestManager->writeTableManifest(
                        $webalizedExportName . '.csv',
                        $manifestOptions,
                    );
                } else {
                    $this->consoleOutput->writeln('No documents found for export ' . $exportOptions['name']);
                }
            }
        }
    }

    /**
     * Validates exports
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
     * @throws UserException
     */
    private function validateDateFilter(array $dateFilter, string $exportName): void
    {
        if (!isset($dateFilter['field'], $dateFilter['format'], $dateFilter['value'])) {
            throw new UserException(
                'Please check if "dateFilter" contains all required parameters (field, format and value) '
                . 'in "'. $exportName . '" export',
            );
        }

        if (strtotime($dateFilter['value']) === false) {
            throw new UserException(
                'Please check "value" field of "dateFiler" in "'. $exportName . '" export',
            );
        }
    }
}
