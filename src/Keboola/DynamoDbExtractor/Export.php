<?php

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\Exception\DynamoDbException;
use Nette\Utils\Strings;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Export
{
    /** @var DynamoDbClient */
    private $dynamoDbClient;

    /** @var array */
    private $exportOptions;

    /** @var string */
    private $outputPath;

    /** @var OutputInterface  */
    private $consoleOutput;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $filename;

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
     * @return string
     * @throws UserException
     */
    public function export(): string
    {
        $marshaler = new Marshaler();

        $params = [
            'TableName' => $this->exportOptions['table'],
        ];

        if (isset($this->exportOptions['dateFilter'])) {
            $params = array_merge(
                $params,
                $this->createParamsFromDateFilter($this->exportOptions['dateFilter'])
            );
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
                    $json = \json_encode($marshaler->unmarshalItem($item));
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
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->exportOptions['enabled'] ?? false);
    }

    /**
     * Deletes exported json
     */
    public function cleanup()
    {
        $this->filesystem->remove($this->filename);
    }

    /**
     * Creates filtering params from date filter
     * @param array $dateFilter
     * @return array
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
                    'S' => date($dateFilter['format'], strtotime($dateFilter['value']))
                ]
            ]
        ];
    }
}
