<?php

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\Exception\DynamoDbException;
use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Filesystem;

class Export
{
    /** @var array */
    private $exportOptions;

    /** @var string */
    private $outputPath;

    /** @var DynamoDbClient */
    private $dynamoDbClient;

    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $filename;

    public function __construct(DynamoDbClient $dynamoDbClient, array $exportOptions, string $outputPath)
    {
        $this->dynamoDbClient = $dynamoDbClient;
        $this->outputPath = $outputPath;
        $this->exportOptions = $exportOptions;

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
}
