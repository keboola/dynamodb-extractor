<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Keboola\Component\Config\DatatypeSupport;
use Keboola\Component\Manifest\ManifestManager;
use Keboola\Component\UserException;
use Keboola\DynamoDbExtractor\Config\Config;
use Nette\Utils\Strings;
use Psr\Log\LoggerInterface;

class Extractor
{
    private DynamoDbClient $dynamoDbClient;
    private array $exports;
    private DatatypeSupport $dataTypeSupport;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, Config $config)
    {
        $dbConfig = $config->getDb();
        // create DynamoDbClient instance
        $this->dynamoDbClient = new DynamoDbClient([
            'endpoint' => $dbConfig['endpoint'],
            'credentials' => [
                'key' => $dbConfig['accessKeyId'],
                'secret' => $dbConfig['#secretAccessKey'],
            ],
            'region' => $dbConfig['regionName'],
            'version' => '2012-08-10',
        ]);
        $this->exports = $config->getExports();
        $this->dataTypeSupport = $config->getDataTypeSupport();
        $this->logger = $logger;
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
        $this->validateExports($this->exports);

        $manifestManager = new ManifestManager($outputPath);

        foreach ($this->exports as $exportOptions) {
            $export = new Exporter($this->dynamoDbClient, $exportOptions, $outputPath, $this->logger);
            $primaryKeysSet = [];

            if ($export->hasEnabledExport()) {
                $filename = $export->export();
                if (file_exists($filename)) {
                    $webalizedExportName = Strings::webalize($exportOptions['name']);
                    $parser = new Parser(
                        $webalizedExportName,
                        $filename,
                        $exportOptions['mapping'],
                        $this->logger,
                    );

                    $headers = $parser->parseAndWriteCsvFiles();
                    $export->cleanup();

                    foreach ($headers as $tableName => $tableHeaders) {
                        $options = new ManifestManager\Options\OutTable\ManifestOptions();
                        $options->setIncremental($exportOptions['incremental']);

                        foreach ($tableHeaders as $column) {
                            $column = trim($column, '"');
                            $isPrimaryKey = in_array($column, $exportOptions['primaryKey'] ?? [], true);
                            if ($isPrimaryKey) {
                                $primaryKeysSet[] = $column;
                            }
                            $options->addSchema(new ManifestManager\Options\OutTable\ManifestOptionsSchema(
                                $column,
                                ['base' => ['type' => 'string']],
                                true,
                                $isPrimaryKey,
                            ));
                        }

                        $manifestManager->writeTableManifest(
                            $tableName . '.csv',
                            $options,
                            $this->dataTypeSupport->usingLegacyManifest(),
                        );
                    }
                } else {
                    $this->logger->info('No documents found for export ' . $exportOptions['name']);
                }
            }

            if (isset($exportOptions['primaryKey']) && count($primaryKeysSet) !== count($exportOptions['primaryKey'])) {
                throw new UserException(sprintf(
                    'Primary keys do not match columns. Missing columns: %s',
                    implode(', ', array_diff($exportOptions['primaryKey'], $primaryKeysSet)),
                ));
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
