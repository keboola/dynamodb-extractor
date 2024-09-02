<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor\ReadingAdapter;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Keboola\DynamoDbExtractor\FileHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractReadingAdapter
{
    protected array $exportOptions;

    protected DynamoDbClient $dynamoDbClient;

    protected LoggerInterface $logger;

    protected string $filename;

    public function __construct(
        array $exportOptions,
        DynamoDbClient $dynamoDbClient,
        LoggerInterface $logger,
        string $filename,
    ) {
        $this->exportOptions = $exportOptions;
        $this->dynamoDbClient = $dynamoDbClient;
        $this->logger = $logger;
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
