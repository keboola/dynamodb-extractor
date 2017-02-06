<?php

namespace Keboola\DynamoDbExtractor;

class FileHelper
{
    /**
     * Appends content to file
     * @param string $filename
     * @param string $content
     * @throws \Exception
     */
    public static function appendContentToFile(string $filename, string $content)
    {
        if (file_put_contents($filename, $content, FILE_APPEND | LOCK_EX) === false) {
            throw new \Exception('Failed write to file: ' . $filename);
        }
    }
}
