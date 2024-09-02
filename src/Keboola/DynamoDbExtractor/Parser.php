<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Keboola\Component\UserException;
use Keboola\CsvMap\Exception\BadDataException;
use Keboola\CsvMap\Mapper;
use Keboola\CsvTable\Table;
use Nette\Utils\Strings;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class Parser
{
    private string $name;

    private string $filename;

    private array $mapping;

    private LoggerInterface $logger;

    private Filesystem $filesystem;

    public function __construct(string $name, string $filename, array $mapping, LoggerInterface $logger)
    {
        $this->name = $name;
        $this->filename = $filename;
        $this->mapping = $mapping;
        $this->logger = $logger;

        $this->filesystem = new Filesystem;
    }

    /**
     * Parses json using Mapper and writes output to CSV files
     * @throws \Keboola\Component\UserException|\Keboola\Csv\Exception
     */
    public function parseAndWriteCsvFiles(): array
    {
        $this->logger->info('Parsing "' . $this->filename . '"');

        $handle = fopen($this->filename, 'rb');
        if (!$handle) {
            return [];
        }

        $headers = [];
        while (!feof($handle)) {
            $line = (string) fgets($handle);
            $data = [];
            if (trim($line) !== '') {
                $data = [json_decode($line, false, 512, JSON_THROW_ON_ERROR)];
            }

            $parser = new Mapper($this->mapping, true, $this->name);
            try {
                //@phpstan-ignore-next-line
                $parser->parse($data);
            } catch (BadDataException $e) {
                throw new UserException($e->getMessage());
            }

            $headers = array_merge($headers, $this->write($parser->getCsvFiles()));
        }

        $this->logger->info('Done');
        return $headers;
    }

    /**
     * Writes CSV files to filesystem
     */
    private function write(array $csvFiles): array
    {
        $headers = [];
        foreach ($csvFiles as $file) {
            /** @var Table $file */
            $name = Strings::webalize($file->getName());
            $outputCsv = dirname($this->filename). '/' . $name . '.csv';

            $content = (string) file_get_contents($file->getPathname());
            $contentArr = explode("\n", $content);
            $header = array_shift($contentArr);
            $content = implode("\n", $contentArr);

            FileHelper::appendContentToFile($outputCsv, $content);

            $this->filesystem->remove($file->getPathname());
            $headers[$name] = explode(',', $header);
        }

        return $headers;
    }
}
