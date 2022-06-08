<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Keboola\DynamoDbExtractor\ReadingAdapter\ScanReadingAdapter;
use Nette\Utils\Strings;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Exporter
{
    private DynamoDbClient $dynamoDbClient;

    private array $exportOptions;

    private OutputInterface $consoleOutput;

    private Filesystem $filesystem;

    private string $filename;

    public function __construct(
        DynamoDbClient $dynamoDbClient,
        array $exportOptions,
        string $outputPath,
        OutputInterface $output
    ) {
        $this->dynamoDbClient = $dynamoDbClient;
        $this->exportOptions = $exportOptions;
        $this->consoleOutput = $output;

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
        $readingAdapter = new ScanReadingAdapter(
            $this->exportOptions,
            $this->dynamoDbClient,
            $this->consoleOutput,
            $this->filename
        );
        $readingAdapter->read($params);

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
