<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Keboola\CsvMap\Exception\BadDataException;
use Keboola\CsvMap\Mapper;
use Keboola\CsvTable\Table;
use Nette\Utils\Strings;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Parser
{
    private string $name;

    private string $filename;

    private array $mapping;

    private OutputInterface $consoleOutput;

    private Filesystem $filesystem;

    public function __construct(string $name, string $filename, array $mapping, OutputInterface $output)
    {
        $this->name = $name;
        $this->filename = $filename;
        $this->mapping = $mapping;
        $this->consoleOutput = $output;

        $this->filesystem = new Filesystem;
    }

    /**
     * Parses json using Mapper and writes output to CSV files
     * @throws \Keboola\DynamoDbExtractor\UserException|\Keboola\Csv\Exception
     */
    public function parseAndWriteCsvFiles(): void
    {
        $this->consoleOutput->writeln('Parsing "' . $this->filename . '"');

        $handle = fopen($this->filename, 'r');
        if (!$handle) {
            return;
        }

        while (!feof($handle)) {
            $line = (string) fgets($handle);
            $data = [];
            if (trim($line) !== '') {
                $data = [json_decode($line)];
            }

            $parser = new Mapper($this->mapping, true, $this->name);
            try {
                $parser->parse($data);
            } catch (BadDataException $e) {
                throw new UserException($e->getMessage());
            }

            $this->write($parser->getCsvFiles());
        }

        $this->consoleOutput->writeln('Done');
    }

    /**
     * Writes CSV files to filesystem
     */
    private function write(array $csvFiles): void
    {
        foreach ($csvFiles as $file) {
            /** @var Table $file */
            $name = Strings::webalize($file->getName());
            $outputCsv = dirname($this->filename). '/' . $name . '.csv';

            $content = (string) file_get_contents($file->getPathname());

            // csv-map doesn't have option to skip header yet,
            // so we skip header if file exists
            if ($this->filesystem->exists($outputCsv)) {
                $contentArr = explode("\n", $content);
                array_shift($contentArr);
                $content = implode("\n", $contentArr);
            }

            FileHelper::appendContentToFile($outputCsv, $content);

            $this->filesystem->remove($file->getPathname());
        }
    }
}
