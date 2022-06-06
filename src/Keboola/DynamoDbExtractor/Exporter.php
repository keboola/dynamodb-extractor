<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Nette\Utils\Strings;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Exporter
{
    private DynamoDbClient $dynamoDbClient;

    /** @var mixed[]  */
    private array $exportOptions;

    private string $outputPath;

    private OutputInterface $consoleOutput;

    private Filesystem $filesystem;

    private string $filename;

    /**
     * @param array<string, mixed> $exportOptions
     */
    public function __construct(
        DynamoDbClient $dynamoDbClient,
        array $exportOptions,
        string $outputPath,
        OutputInterface $output
    ) {
        $this->dynamoDbClient = $dynamoDbClient;
        $this->exportOptions = $exportOptions;
        $this->outputPath = $outputPath;
        $this->consoleOutput = $output;

        $this->filesystem = new Filesystem;
        $this->filename = $this->outputPath . '/' . Strings::webalize($this->exportOptions['name']) . '.json';
    }

    /**
     * Exports table from DynamoDb
     * @throws UserException
     */
    public function export(): string
    {
        $marshaler = new Marshaler();

        $params = [
            'TableName' => $this->exportOptions['table'],
        ];

        if (isset($this->exportOptions['index'])) {
            $params['IndexName'] = $this->exportOptions['index'];
        }

        if (isset($this->exportOptions['dateFilter'])) {
            $paramsFromDateFilter = $this->createParamsFromDateFilter($this->exportOptions['dateFilter']);
            $this->consoleOutput->writeln(json_encode($paramsFromDateFilter));
            $params = array_merge($params, $paramsFromDateFilter);
        }

        $scanLimit = new ScanLimit(1000, $this->exportOptions['limit'] ?? null);

        try {
            do {
                if (isset($response, $response['LastEvaluatedKey'])) {
                    $params['ExclusiveStartKey'] = $response['LastEvaluatedKey'];
                }
                $params['Limit'] = $scanLimit->getBatchSize();
                $response = $this->dynamoDbClient->scan($params);
                $scanLimit->decreaseLimit($response['Count']);

                foreach ($response['Items'] as $item) {
                    $json = json_encode($marshaler->unmarshalItem($item));
                    FileHelper::appendContentToFile($this->filename, $json . "\n");
                }
            } while ($scanLimit->shouldContinue() && isset($response['LastEvaluatedKey']));
        } catch (DynamoDbException $e) {
            if ($e->getStatusCode() !== null && strpos($e->getStatusCode(), '4') === 0) {
                throw new UserException($e->getAwsErrorCode());
            } else {
                throw $e;
            }
        }

        return $this->filename;
    }

    /**
     * Returns if export is enabled or not
     */
    public function hasEnabledExport(): bool
    {
        return (bool) ($this->exportOptions['enabled'] ?? false);
    }

    /**
     * Deletes exported json
     */
    public function cleanup(): void
    {
        $this->filesystem->remove($this->filename);
    }

    /**
     * Creates filtering params from date filter
     * @param array<string, mixed> $dateFilter
     * @return array<string, mixed>
     */
    private function createParamsFromDateFilter(array $dateFilter): array
    {
        return [
            'FilterExpression' => '#field >= :value',
            'ExpressionAttributeNames' => [
                '#field' => $dateFilter['field'],
            ],
            'ExpressionAttributeValues' => [
                ':value' => [
                    'S' => date($dateFilter['format'], strtotime($dateFilter['value'])),
                ],
            ],
        ];
    }
}
