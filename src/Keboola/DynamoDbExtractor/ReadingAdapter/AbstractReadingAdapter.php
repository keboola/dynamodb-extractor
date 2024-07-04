<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor\ReadingAdapter;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Keboola\DynamoDbExtractor\FileHelper;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractReadingAdapter
{
    protected array $exportOptions;

    protected DynamoDbClient $dynamoDbClient;

    protected OutputInterface $consoleOutput;

    protected string $filename;

    public function __construct(
        array $exportOptions,
        DynamoDbClient $dynamoDbClient,
        OutputInterface $consoleOutput,
        string $filename,
    ) {
        $this->exportOptions = $exportOptions;
        $this->dynamoDbClient = $dynamoDbClient;
        $this->consoleOutput = $consoleOutput;
        $this->filename = $filename;
    }

    abstract public function read(array $params): void;

    protected function saveResponseItems(Marshaler $marshaler, array $items): void
    {
        /** @var array $item */
        foreach ($items as $item) {
            $json = json_encode($marshaler->unmarshalItem($item));
            FileHelper::appendContentToFile($this->filename, $json . "\n");
        }
    }
}
