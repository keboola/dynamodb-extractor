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
     */
    public function export(): string
    {
        $marshaler = new Marshaler();

        $params = [
            'TableName' => $this->exportOptions['table'],
        ];

        try {
            do {
                if (isset($response, $response['LastEvaluatedKey'])) {
                    $params['ExclusiveStartKey'] = $response['LastEvaluatedKey'];
                }
                $response = $this->dynamoDbClient->scan($params);

                foreach ($response['Items'] as $item) {
                    $json = \json_encode($marshaler->unmarshalItem($item));
                    FileHelper::appendContentToFile($this->filename, $json . "\n");
                }
            } while (isset($response['LastEvaluatedKey']));
        } catch (DynamoDbException $e) {
            echo "Unable to scan:\n";
            echo $e->getMessage() . "\n";
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
