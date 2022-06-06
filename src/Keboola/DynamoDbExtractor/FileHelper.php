<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use Exception;

class FileHelper
{
    /**
     * Appends content to file
     * @throws Exception
     */
    public static function appendContentToFile(string $filename, string $content): void
    {
        if (file_put_contents($filename, $content, FILE_APPEND | LOCK_EX) === false) {
            throw new Exception('Failed write to file: ' . $filename);
        }
    }
}
