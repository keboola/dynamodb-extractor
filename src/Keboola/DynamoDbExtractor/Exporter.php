<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Keboola\Component\UserException;
use Keboola\DynamoDbExtractor\Config\ConfigDefinition;
use Keboola\DynamoDbExtractor\ReadingAdapter\QueryReadingAdapter;
use Keboola\DynamoDbExtractor\ReadingAdapter\ScanReadingAdapter;
use Nette\Utils\Strings;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class Exporter
{
    private DynamoDbClient $dynamoDbClient;
    private array $exportOptions;
    private Filesystem $filesystem;
    private string $filename;
    private LoggerInterface $logger;

    public function __construct(
        DynamoDbClient $dynamoDbClient,
        array $exportOptions,
        string $outputPath,
        LoggerInterface $logger,
    ) {
        $this->dynamoDbClient = $dynamoDbClient;
        $this->exportOptions = $exportOptions;
        $this->logger = $logger;

        $this->filesystem = new Filesystem;
        $this->filename = $outputPath . '/out/tables/' . Strings::webalize($this->exportOptions['name']) . '.json';
    }

    /**
     * Exports table from DynamoDb
     * @throws UserException
     */
    public function export(): string
    {
        $params = [
            'TableName' => $this->exportOptions['table'],
        ];

        switch ($this->exportOptions['mode']) {
            case ConfigDefinition::MODE_QUERY:
                $readingAdapter = new QueryReadingAdapter(
                    $this->exportOptions,
                    $this->dynamoDbClient,
                    $this->logger,
                    $this->filename,
                );
                break;
            default:
                $readingAdapter = new ScanReadingAdapter(
                    $this->exportOptions,
                    $this->dynamoDbClient,
                    $this->logger,
                    $this->filename,
                );
        }
        try {
            $readingAdapter->read($params);
        } catch (DynamoDbException $e) {
            if ($e->getStatusCode() !== null && substr((string) $e->getStatusCode(), 0, 1) === '4') {
                throw new UserException((string) $e->getAwsErrorMessage());
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
}
