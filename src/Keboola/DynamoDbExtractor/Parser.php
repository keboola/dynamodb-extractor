<?php

namespace Keboola\DynamoDbExtractor;

use Keboola\CsvMap\Mapper;
use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Filesystem;

class Parser
{
    private $filename;

    private $mapping;

    private $name;

    private $filesystem;

    public function __construct(string $name, string $filename, array $mapping)
    {
        $this->name = $name;
        if (!file_exists($filename)) {
            throw new \Exception('Specified file not exists: ' . $this->filename);
        }
        $this->filename = $filename;
        $this->mapping = $mapping;

        $this->filesystem = new Filesystem;
    }

    /**
     * Parses json using Mapper and writes output to CSV files
     */
    public function parseAndWriteCsvFiles()
    {
        $this->logToConsoleOutput('Parsing "' . $this->filename . '"');

        $handle = fopen($this->filename, 'r');

        while (!feof($handle)) {
            $line = fgets($handle);
            $data = trim($line) !== '' ? [json_decode($line)] : [];

            $parser = new Mapper($this->mapping, $this->name);
            $parser->parse($data);

            $this->write($parser->getCsvFiles());
        }

        $this->logToConsoleOutput('Done "' . $this->filename . '"');
    }

    /**
     * Writes CSV files to filesystem
     * @param array $csvFiles
     */
    private function write(array $csvFiles)
    {
        foreach ($csvFiles as $file) {
            $name = Strings::webalize($file->getName());
            $outputCsv = dirname($this->filename). '/' . $name . '.csv';

            $content = file_get_contents($file->getPathname());

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

    /**
     * Echoes a message
     * @param string $message
     */
    private function logToConsoleOutput(string $message)
    {
        echo $message . "\n";
    }
}
